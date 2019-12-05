<?php


namespace app\race\service\socket;

use Workerman\Lib\Timer;

class Room
{
    public $connect_manage = null;
    public $socket_server = null;

    public $room_id = null;
    public $member_list = array(); //connect 对象id集合   $this->member_list[$userId] = array('user_id' => $userId, 'connection_id' => $connection->id);
    public $race_list = array();
    private $state = null; //房间状态
    private $running_race_num = 0;
    private $race_count;
    private $landlord_select_timer;
    public $creatTime; //房间创建的时间戳

    public function __construct($room_id, $race_count, $connect_manage, $socket_server)
    {
        $this->connect_manage = $connect_manage;
        $this->socket_server = $socket_server;
        $this->race_count = $race_count;
        $this->room_id = $room_id;
        $this->creatTime = time();
        $this->init_race($race_count);
    }

    public function get_race_state($race_num)
    {
        return $this->race_list[$race_num]['state'];
    }

    //1、把数据库里面的比赛地主Id设置完毕 2、向玩家发出地主被选中通知 3、将游戏环节改为开始摇色子
    public function landlord_selected($raceNum, $landlordId)
    {
        $landlordLastCount = config('roomGameConfig.landlordLastCount');
        $this->socket_server->change_race_landlord($this->room_id, $this->running_race_num, $landlordId, $landlordLastCount); //数据库修改

        $message = array('type' => 'landlordSelected', 'info' => array('roomId' => $this->room_id,
            'raceNum' => $raceNum, 'landlordId' => $landlordId, 'landlordLastCount' => $landlordLastCount));
        $this->broadcast_to_all_member($message); //通知用户

        Timer::add(2, function ($raceNum) {
            $RACE_PLAY_STATE = json_decode(RACE_PLAY_STATE, true);
            $this->set_race_state($raceNum, $RACE_PLAY_STATE['ROLL_DICE']);
        }, array($raceNum), false);
    }


    public function set_race_state($race_num, $state)
    {
        $this->race_list[$race_num]['state'] = $state;
        $this->socket_server->change_race_state($this->room_id, $race_num, $state);
    }

    public function init_race($race_count)
    {
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        $this->state = $ROOM_STATE['OPEN'];
        $RACE_PLAY_STATE = json_decode(RACE_PLAY_STATE, true);
        for ($i = 0; $i < $race_count; $i++) {
            $item = array('state' => $RACE_PLAY_STATE['NOT_BEGIN']);
            $this->race_list[$i] = $item;
        }
        var_dump('房间初始化完毕');
    }

    public function add_member($connection, $userId)
    {
        if ($this->is_user_in_room($userId)) {
            var_dump('成员在房间中，不能重复加入');
            return false;
        }
        $this->member_list[$userId] = array('user_id' => $userId, 'connection_id' => $connection->id);
        $member_info = $this->socket_server->get_member_info_in_the_room($userId, $this->room_id);
        $message = array('type' => 'newMemberInRoom', 'info' => $member_info);
        $this->broadcast_to_all_member($message);

        $message = array('type' => 'roomGameConfigSet', 'info' => config('roomGameConfig'));
        $this->broadcast_to_member($message, $connection->id, $userId);
        var_dump('房间加入新成员');
        return true;
    }

    public function remove_member($userId)
    {
        if (isset($this->member_list[$userId])) {
            unset($this->member_list[$userId]);
            $message = array('type' => 'memberOffLine', 'info' => array('user_id' => $userId));//用户离线
            $this->broadcast_to_all_member($message);
        }
    }

    //判断在socket的房间中是否存在
    public function is_user_in_room($userId)
    {
        if (isset($this->member_list[$userId])) {
            if ($this->get_member_ob_by_connection_id($this->member_list[$userId]['connection_id']) === null) {
                $this->remove_member($userId);
                return false;
            }
            return true;
        }
        return false;
    }

    //失败返回null
    public function get_member_ob_by_connection_id($connection_id)
    {
        if (isset($this->connect_manage->list[$connection_id])) {
            return $this->connect_manage->list[$connection_id];
        }
        return null;
    }

    public function get_room_state()
    {
        return $this->state;
    }

    public function broadcast_to_all_member($message)
    {
        foreach ($this->member_list as $member_info) {
            $this->broadcast_to_member($message, $member_info['connection_id'], $member_info['user_id']);
        }
    }

    public function broadcast_to_member($message, $connection_id, $userId)
    {
        $member_ob = $this->get_member_ob_by_connection_id($connection_id);
        if ($member_ob !== null) {
            $member_ob->send(json_encode($message));
        } else {
            var_dump('该成员不在线');
            $this->remove_member($userId);
        }
    }

    public function start_game()
    {
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        if ($this->state !== $ROOM_STATE['OPEN']) {
            var_dump('不能重复开始');
            return;
        }
        if ($this->running_race_num !== 0) {
            var_dump('正在进行的场次异常');
            return;
        }
        $this->socket_server->change_room_state($this->room_id, $ROOM_STATE['PLAYING']);
        $message = array('type' => 'gameBegin', 'info' => array('roomId' => $this->room_id));
        $this->broadcast_to_all_member($message);
        $this->state = $ROOM_STATE['PLAYING'];
        $this->startRace(0);
    }

    public function change_roll_dice()
    { //摇色子
        $race_play_state = json_decode(RACE_PLAY_STATE, true);
        $this->set_race_state($this->running_race_num, $race_play_state['ROLL_DICE']);
        $message = array('type' => 'raceStateRollDice', 'info' => array('raceNum' => $this->running_race_num, 'roomId' => $this->room_id));
        $this->broadcast_to_all_member($message);
        var_dump('启动摇色子流程');
        var_dump('房间号：' . $this->room_id . ',场次号：' . $this->running_race_num);
    }

    public function change_deal()
    { ////发牌
        $race_play_state = json_decode(RACE_PLAY_STATE, true);
        $this->set_race_state($this->running_race_num, $race_play_state['DEAL']);
        $message = array('type' => 'raceStateDeal', 'info' => array('raceNum' => $this->running_race_num, 'roomId' => $this->room_id));
        $this->broadcast_to_all_member($message);
        var_dump('启动发牌流程');
        var_dump('房间号：' . $this->room_id . ',场次号：' . $this->running_race_num);
    }

    public function change_roll_bet()
    { //下注
        $race_play_state = json_decode(RACE_PLAY_STATE, true);
        $this->set_race_state($this->running_race_num, $race_play_state['BET']);
        $message = array('type' => 'raceStateBet', 'info' => array('raceNum' => $this->running_race_num, 'roomId' => $this->room_id));
        $this->broadcast_to_all_member($message);
        var_dump('启动下注流程');
        var_dump('房间号：' . $this->room_id . ',场次号：' . $this->running_race_num);
    }

    public function change_show_down()
    { //比大小
        $race_play_state = json_decode(RACE_PLAY_STATE, true);
        $this->set_race_state($this->running_race_num, $race_play_state['SHOW_DOWN']);
        $message = array('type' => 'raceStateShowDown', 'info' => array('raceNum' => $this->running_race_num, 'roomId' => $this->room_id));
        $this->broadcast_to_all_member($message);
        var_dump('启动比大小流程');
        var_dump('房间号：' . $this->room_id . ',场次号：' . $this->running_race_num);
    }

    public function change_show_result()
    { //显示结果
        $race_play_state = json_decode(RACE_PLAY_STATE, true);
        $this->set_race_state($this->running_race_num, $race_play_state['SHOW_RESULT']);
        $result_list = $this->socket_server->get_race_result($this->room_id, $this->running_race_num);
        $message = array('type' => 'raceStateShowResult', 'info' => array('raceNum' => $this->running_race_num, 'roomId' => $this->room_id, 'resultList' => $result_list));
        $this->broadcast_to_all_member($message);
        var_dump('启动显示结果流程');
        var_dump('房间号：' . $this->room_id . ',场次号：' . $this->running_race_num);
    }

    public function change_finished()
    { //结束
        $race_play_state = json_decode(RACE_PLAY_STATE, true);
        $this->set_race_state($this->running_race_num, $race_play_state['FINISHED']);
        $message = array('type' => 'raceStateFinished', 'info' => array('raceNum' => $this->running_race_num, 'roomId' => $this->room_id));
        $this->broadcast_to_all_member($message);
        var_dump('本场比赛结束');
        var_dump('房间号：' . $this->room_id . ',场次号：' . $this->running_race_num);
    }

    public function broadcast_to_select_landlord($raceNum)
    {
        $race_play_state = json_decode(RACE_PLAY_STATE, true);
        $count = config('roomGameConfig.landlordLastCount');
        if ($raceNum % $count === 0) {
            $this->set_race_state($raceNum, $race_play_state['CHOICE_LANDLORD']);
            $message = array('type' => 'raceStateChoiceLandlord', 'info' => array('raceNum' => $raceNum, 'roomId' => $this->room_id));
            $this->broadcast_to_all_member($message); //广播选地主
        } else {
            $this->set_race_state($raceNum, $race_play_state['ROLL_DICE']);
        }
        $this->landlord_select_timer = Timer::add(2, function () {
            $this->race_run_after_landlord();
        }, array(), true);
    }

    public function destroy()
    {
        Timer::del($this->landlord_select_timer);
    }

    public function race_run_after_landlord()
    {
        $race_play_state = json_decode(RACE_PLAY_STATE, true);
        if ($this->race_list[$this->running_race_num]['state'] !== $race_play_state['CHOICE_LANDLORD']) {
            Timer::del($this->landlord_select_timer);
            ///////////////////////
            $this->change_roll_dice();

            Timer::add(config('roomGameConfig.rollDiceTime'), function () {
                $this->change_deal();

                Timer::add(config('roomGameConfig.dealTime'), function () {
                    $this->change_roll_bet();

                    Timer::add(config('roomGameConfig.betTime'), function () {

                        $this->change_show_down();
                        Timer::add(config('roomGameConfig.showDownTime'), function () {
                            $this->change_show_result();
                            Timer::add(config('roomGameConfig.showResultTime'), function () {
                                $this->change_finished();
                                Timer::add(2, function () {
                                    $this->running_race_num = $this->running_race_num + 1;
                                    $this->startRace($this->running_race_num);
                                }, array(), false);

                            }, array(), false);

                        }, array(), false);

                    }, array(), false);

                }, array(), false);

            }, array(), false);
            /////////////////////////
        } else {
            var_dump('等待选地主');
        }
    }

    public function startRace($raceNum)
    {
        if ($raceNum >= $this->race_count) {
            $ROOM_STATE = json_decode(ROOM_STATE, true);
            $this->socket_server->change_room_state($this->room_id, $ROOM_STATE['ALL_RACE_FINISHED']);
            $this->state = $ROOM_STATE['ALL_RACE_FINISHED'];
            $info = $this->socket_server->get_room_result($this->room_id);
            $message = array('type' => 'allRaceFinished', 'info' => array('roomResult' => $info));
            $this->broadcast_to_all_member($message);
            var_dump('所有比赛结束');
            return;
        }
        $this->broadcast_to_select_landlord($raceNum);
    }

    public function raceBet($userId, $roomId, $raceNum, $betLocation, $betVal)
    {
        $back = $this->socket_server->to_bet($userId, $roomId, $raceNum, $betLocation, $betVal);
        if (!$back['status']) {
            var_dump('下注失败');
            return;
        }
        var_dump('下注成功');
        $message = array('type' => 'betNotice', 'info' => array('userId' => $userId, 'roomId' => $roomId,
            'raceNum' => $raceNum, 'betLocation' => $betLocation, 'betVal' => $betVal));
        $this->broadcast_to_all_member($message);
    }

}