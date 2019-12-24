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
    private $is_valid = true; //房间是否有效标志位 true表示有效 false表示无效

    private $rollDiceTimer;//摇色子定时器
    private $dealTimer;//发牌定时器
    private $betTimer;//下注定时器
    private $showDownTimer;//比大小定时器
    private $showResultTimer;//显示结果定时器
    private $nextRaceTimer;//开始下场比赛定时器

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

    public function is_room_valid()
    {
        return $this->is_valid;
    }

    //1、把数据库里面的比赛地主Id设置完毕 2、向玩家发出地主被选中通知 3、将游戏环节改为开始摇色子
    public function landlord_selected($raceNum, $landlordId)
    {
        $landlordLastCount = config('roomGameConfig.landlordLastCount');
        $this->socket_server->change_race_landlord($this->room_id, $this->running_race_num, $landlordId, $landlordLastCount); //数据库修改
        $message = array('type' => 'landlordSelected', 'info' => array('roomId' => $this->room_id,
            'raceNum' => $raceNum, 'landlordId' => $landlordId, 'landlordLastCount' => $landlordLastCount));
        $this->broadcast_to_all_member($message); //通知用户

        Timer::add(1.5, function ($raceNum) {
            $this->race_run_after_landlord();
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
        var_dump('socket房间创建完毕');
    }

    public function add_member($connection, $userId)
    {
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        if ($this->state != $ROOM_STATE["OPEN"]) {
            var_dump('socket房间已经开始，不能加入');
            return false;
        }

        $member_info = $this->socket_server->get_member_info_in_the_room($userId, $this->room_id);
        if (!$member_info) {
            var_dump('数据库中该用户不在房间，进入socket房间失败');
            return false;
        }
        $this->member_list[$userId] = array('user_id' => $userId, 'connection_id' => $connection->id);
        $message = array('type' => 'newMemberInRoom', 'info' => $member_info);
        $this->broadcast_to_all_member($message);
        var_dump('房间加入新成员');
        return true;
    }

    public function out_member($userId)
    {
        if (!$this->is_user_in_room($userId)) {
            var_dump('成员不在房间中');
            return false;
        }
        unset($this->member_list[$userId]);
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        if ($this->state == $ROOM_STATE["OPEN"]) { //如果比赛没有开始，从数据库中删除成员
            $this->socket_server->cancel_member_from_room($userId, $this->room_id);
        }
        $message = array('type' => 'memberOutRoom', 'info' => array('user_id' => $userId));//用户离开socket房间
        $this->broadcast_to_all_member($message);
        return true;
    }

    //判断玩家是否在socket房间中
    public function is_user_in_room($userId)
    {
        if (isset($this->member_list[$userId])) {
            return true;
        }
        return false;
    }

    //失败返回null
    public function get_member_ob_by_connection_id($connection_id)
    {
        $connections = $this->connect_manage->get_connections();
        if (isset($connections[$connection_id])) {
            return $connections[$connection_id];
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
            unset($this->member_list[$userId]);
            var_dump('该成员不在线');
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
            $this->race_run_after_landlord();
        }
    }

    public function destroy()
    {
        Timer::del($this->rollDiceTimer);
        Timer::del($this->dealTimer);
        Timer::del($this->betTimer);
        Timer::del($this->showDownTimer);
        Timer::del($this->showResultTimer);
        Timer::del($this->nextRaceTimer);
    }

    public function race_run_after_landlord()
    {
        $this->change_roll_dice();
        $this->rollDiceTimer = Timer::add(config('roomGameConfig.rollDiceTime'), function () {
            $this->change_deal();
            $this->dealTimer = Timer::add(config('roomGameConfig.dealTime'), function () {
                $this->change_roll_bet();
                $this->betTimer = Timer::add(config('roomGameConfig.betTime'), function () {
                    $this->change_show_down();
                    $this->showDownTimer = Timer::add(config('roomGameConfig.showDownTime'), function () {
                        $this->change_show_result();
                        $this->showResultTimer = Timer::add(config('roomGameConfig.showResultTime'), function () {
                            $this->change_finished();
                            $this->nextRaceTimer = Timer::add(2, function () {
                                $this->running_race_num = $this->running_race_num + 1;
                                $this->startRace($this->running_race_num);
                            }, array(), false);

                        }, array(), false);

                    }, array(), false);

                }, array(), false);

            }, array(), false);

        }, array(), false);
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
            $this->is_valid = false;
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

    public function cancel_bet_by_location($userId, $roomId, $raceNum, $betLocation)
    {
        $message = array('type' => 'cancelBetSuccessNotice', 'info' => array('userId' => $userId, 'roomId' => $roomId,
            'raceNum' => $raceNum, 'betLocation' => $betLocation)); //删除下注通知成功
        $this->broadcast_to_all_member($message);
    }

}