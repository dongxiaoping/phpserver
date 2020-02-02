<?php


namespace app\race\service\socket;

use think\Log;
use Workerman\Lib\Timer;

class Room
{
    public $connect_manage = null;
    public $socket_server = null;

    public $room_id = null;
    public $member_list = array(); //connect 对象id集合   $this->member_list[$userId] = array('user_id' => $userId, 'connection_id' => $connection->id);
    public $score_list = array(); //已发布场次的玩家得分集合
    public $race_list = array();
    private $state = null; //房间状态
    private $running_race_num = 0;
    private $race_count;
    private $is_valid = true; //房间是否有效标志位 true表示有效 false表示无效

    private $dealActionTimer;//摇色子、发牌定时器
    private $betTimer;//下注定时器
    private $showDownTimer;//比大小定时器

    private $create_user_id = null; //房间创建者ID

    public $creatTime; //房间创建的时间戳

    public function __construct($room_id, $create_user_id, $race_count, $connect_manage, $socket_server)
    {
        $this->connect_manage = $connect_manage;
        $this->socket_server = $socket_server;
        $this->race_count = $race_count;
        $this->room_id = $room_id;
        $this->create_user_id = $create_user_id;
        $this->creatTime = time();
        $this->init_race($race_count);
    }

    public function get_race_state($race_num)
    {
        return $this->race_list[$race_num]['state'];
    }

    public function get_race_landlord_id($race_num)
    {
        return $this->race_list[$race_num]['landlord_id'];
    }

    public function set_race_landlord($race_num, $landlord_id)
    {
        try {
            $count = config('roomGameConfig.landlordLastCount');
            for ($i = 0; $i < $count; $i++) {
                if (isset($this->race_list[$race_num + $i])) {
                    $this->race_list[$race_num + $i]['landlord_id'] = $landlord_id;
                }
            }
        } catch (Exception $e) {
            //Log::write($e->getMessage(), 'error');
        }
    }

    public function is_room_valid()
    {
        $now_time = time();
        if ($now_time - $this->creatTime >= config('roomGameConfig.roomTimeoutTime')) {
            $this->is_valid = false;
            $ROOM_STATE = json_decode(ROOM_STATE, true);
            if ($this->state != $ROOM_STATE['CLOSE']) {
                $this->state = $ROOM_STATE['CLOSE'];
                $this->socket_server->change_room_state($this->room_id, $ROOM_STATE['CLOSE']);
            }
        }
        return $this->is_valid;
    }

    //1、把数据库里面的比赛地主Id设置完毕 2、向玩家发出地主被选中通知 3、将游戏环节改为开始摇色子
    public function landlord_selected($raceNum, $landlordId)
    {
        try {
            if ($this->running_race_num != $raceNum) {
                //Log::write('workman/worker:抢地主失败，比赛场次异常', 'error');
                return;
            }
            $RACE_PLAY_STATE = json_decode(RACE_PLAY_STATE, true);
            $the_race_state = $this->get_race_state($this->running_race_num);
            if ($the_race_state != $RACE_PLAY_STATE['CHOICE_LANDLORD']) {
                //Log::write('workman/worker:抢失败', 'info');
                return;
            }
            $this->set_race_landlord($this->running_race_num, $landlordId);
            $landlordLastCount = config('roomGameConfig.landlordLastCount');
            $this->socket_server->change_race_landlord($this->room_id, $this->running_race_num, $landlordId, $landlordLastCount); //数据库修改
            $this->creatTime = time();
            $this->startRace();
        } catch (Exception $e) {
            //Log::write($e->getMessage(), 'error');
        }
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
            $item = array('state' => $RACE_PLAY_STATE['NOT_BEGIN'], 'landlord_id' => null);
            $this->race_list[$i] = $item;
        }
        //Log::write('workman/room:场次信息初始化完毕', 'info');
    }

    public function add_member($connection, $userId)
    {
        try {
            $ROOM_STATE = json_decode(ROOM_STATE, true);
            if ($this->state === $ROOM_STATE["CLOSE"]) {
                //Log::write('workman/room:socket房间比赛已结束，不能加入玩家:' . $userId, 'info');
                return false;
            }

            $member_info = $this->socket_server->get_member_info_in_the_room($userId, $this->room_id);
            if (!$member_info) {
                //Log::write('workman/room:数据库中该用户不在房间，进入socket房间失败:' . $userId, 'error');
                return false;
            }
            $ROOM_PLAY_MEMBER_STATE = json_decode(ROOM_PLAY_MEMBER_STATE, true);
            if ($member_info['state'] == $ROOM_PLAY_MEMBER_STATE['KICK_OUT']) {
                //Log::write('workman/room:该用户被踢出，不能进入:' . $userId, 'error');
                return false;
            }
            $this->socket_server->change_member_state_in_room($userId, $this->room_id, $ROOM_PLAY_MEMBER_STATE['ON_LINE']);
            $this->member_list[$userId] = array('user_id' => $userId, 'connection_id' => $connection->id);
            $this->connect_manage->add_room_id($connection->id, $this->room_id);
            $message = array('type' => 'memberInSocketRoom', 'info' => $member_info);
            $this->broadcast_to_all_member($message);
            //Log::write('workman/room:socket房间加入成员成功:' . $userId . ',房间ID' . $this->room_id, 'info');
            return true;
        } catch (Exception $e) {
            //Log::write($e->getMessage(), 'error');
        }
    }

    public function out_member($userId)
    {
        try {
            if (!$this->is_user_in_room($userId)) {
                //Log::write('workman/room:成员不在房间中，退出房间失败，用户ID：' . $userId . ',房间ID：' . $this->room_id, 'error');
                return false;
            }
            unset($this->member_list[$userId]);
            $ROOM_STATE = json_decode(ROOM_STATE, true);
            if ($this->state == $ROOM_STATE["OPEN"] && $this->create_user_id != $userId) { //如果比赛没有开始，并且当前用户不是房主，从数据库中删除成员
                $this->socket_server->cancel_member_from_room($userId, $this->room_id);
            } else {
                $ROOM_PLAY_MEMBER_STATE = json_decode(ROOM_PLAY_MEMBER_STATE, true);
                $this->socket_server->change_member_state_in_room($userId, $this->room_id, $ROOM_PLAY_MEMBER_STATE['OFF_LINE']);
            }
            $message = array('type' => 'memberOutSocketRoom', 'info' => array('userId' => $userId));//用户离开socket房间
            $this->broadcast_to_all_member($message);
            return true;
        } catch (Exception $e) {
            //Log::write($e->getMessage(), 'error');
        }
    }

    public function get_member_by_connection_id($connection_id)
    {
        foreach ($this->member_list as $member_info) {
            if ($member_info['connection_id'] == $connection_id) {
                return $member_info;
            }
        }
        return null;
    }

    //判断玩家是否在socket房间中
    public function is_user_in_room($userId)
    {
        if (isset($this->member_list[$userId])) {
            return true;
        }
        return false;
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
        $member_ob = $this->connect_manage->get_connection_by_id($connection_id);
        if ($member_ob !== null) {
            $member_ob->send(json_encode($message));
        } else {
            unset($this->member_list[$userId]);
            //Log::write('workman/room:成员失去连接' . $userId, 'error');
        }
    }

    public function start_game()
    {
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        if ($this->state !== $ROOM_STATE['OPEN']) {
            //Log::write('workman/room:比赛不能重复开始，房间号：' . $this->room_id, 'error');
            return;
        }
        if ($this->running_race_num !== 0) {
            //Log::write('workman/room:场次异常', 'error');
            return;
        }
        $this->socket_server->change_room_state($this->room_id, $ROOM_STATE['PLAYING']);
        $this->state = $ROOM_STATE['PLAYING'];
        $this->startRace();
    }

    public function change_deal_action()
    { //发牌
        $race_play_state = json_decode(RACE_PLAY_STATE, true);
        $this->set_race_state($this->running_race_num, $race_play_state['DEAL']);
        $the_landlord_id = $this->get_race_landlord_id($this->running_race_num);
        $message = array('type' => 'raceStateDeal', 'info' => array('raceNum' => $this->running_race_num, 'roomId' => $this->room_id, 'landlordId' => $the_landlord_id));
        $this->broadcast_to_all_member($message);
        //Log::write('workman/room:启动发牌流程，房间号：' . $this->room_id . '场次号：' . $this->running_race_num, 'info');
    }

    public function change_roll_bet()
    { //下注
        $race_play_state = json_decode(RACE_PLAY_STATE, true);
        $this->set_race_state($this->running_race_num, $race_play_state['BET']);
        $the_landlord_id = $this->get_race_landlord_id($this->running_race_num);
        $message = array('type' => 'raceStateBet', 'info' => array('raceNum' => $this->running_race_num,
            'roomId' => $this->room_id, 'landlordId' => $the_landlord_id));
        $this->broadcast_to_all_member($message);
        //Log::write('workman/room:启动下注流程，房间号：' . $this->room_id . '场次号：' . $this->running_race_num, 'info');
    }

    public function change_show_down()
    { //比大小
        $race_play_state = json_decode(RACE_PLAY_STATE, true);
        $this->set_race_state($this->running_race_num, $race_play_state['SHOW_DOWN']);
        $race_result = $this->socket_server->get_race_result($this->room_id, $this->running_race_num);
        $this->add_score_list($race_result);
        $the_landlord_id = $this->get_race_landlord_id($this->running_race_num);
        $message = array('type' => 'raceStateShowDown', 'info' => array('raceNum' => $this->running_race_num,
            'roomId' => $this->room_id, 'raceResult' => $race_result, 'roomResult' => $this->score_list, 'landlordId' => $the_landlord_id));
        $this->broadcast_to_all_member($message);
        //Log::write('workman/room:启动比大小流程，房间号：' . $this->room_id . '场次号：' . $this->running_race_num, 'info');
    }

    public function add_score_list($race_score_list)
    {
        for ($i = 0; $i < count($race_score_list); $i++) {
            $user_id = $race_score_list[$i]['userId'];
            $score = $race_score_list[$i]['score'];
            if (isset($this->score_list[$user_id])) {
                $this->score_list[$user_id] += $score;
            } else {
                $this->score_list[$user_id] = $score;
            }
        }
    }

    public function destroy()
    {
        Timer::del($this->dealActionTimer);
        Timer::del($this->betTimer);
        Timer::del($this->showDownTimer);
    }

    public function startRace()
    {
        try {
            if ($this->running_race_num >= $this->race_count) {
                $ROOM_STATE = json_decode(ROOM_STATE, true);
                $this->socket_server->change_room_state($this->room_id, $ROOM_STATE['CLOSE']);
                $this->state = $ROOM_STATE['CLOSE'];
                $info = $this->socket_server->get_room_result($this->room_id, $this->race_count - 1);
                $message = array('type' => 'allRaceFinished', 'info' => array('roomResult' => $info));
                $this->broadcast_to_all_member($message);
                //Log::write('workman/room:所有比赛结束,房间号:' . $this->room_id, 'info');
                $this->is_valid = false;
                return;
            }

            $this->socket_server->change_on_race($this->room_id, $this->running_race_num);
            $race_play_state = json_decode(RACE_PLAY_STATE, true);
            $the_landlord_id = $this->get_race_landlord_id($this->running_race_num);
            if ($the_landlord_id == null) {
                $this->set_race_state($this->running_race_num, $race_play_state['CHOICE_LANDLORD']);
                $message = array('type' => 'raceStateChoiceLandlord', 'info' => array('raceNum' => $this->running_race_num, 'roomId' => $this->room_id));
                $this->broadcast_to_all_member($message); //广播选地主
                //Log::write('workman/room:广播选地主,房间号:' . $this->room_id . ',场次号：' . $this->running_race_num, 'info');
            } else {
                $this->change_deal_action();
                $this->dealActionTimer = Timer::add(config('roomGameConfig.dealAction'), function () {
                    $this->change_roll_bet();
                    $this->betTimer = Timer::add(config('roomGameConfig.betTime'), function () {
                        $this->change_show_down();
                        $this->showDownTimer = Timer::add(config('roomGameConfig.showDownTime'), function () {
                            $this->running_race_num = $this->running_race_num + 1;
                            $this->startRace();
                        }, array(), false);
                    }, array(), false);
                }, array(), false);
            }
        } catch (Exception $e) {
            //Log::write($e->getMessage(), 'error');
        }
    }

    public function raceBet($userId, $roomId, $raceNum, $betLocation, $betVal)
    {
        try {
            //当前场次 以及状态检查
            if ($raceNum != $this->running_race_num) {
                //Log::write('workman/room:下注失败,比赛场次号不匹配:', 'error');
                return;
            }
            $race_play_state = json_decode(RACE_PLAY_STATE, true);
            if ($this->get_race_state($this->running_race_num) != $race_play_state['BET']) {
                //Log::write('workman/room:下注失败,当前非下注时间:', 'error');
                return;
            }
            $back = $this->socket_server->to_bet($userId, $roomId, $raceNum, $betLocation, $betVal);
            if (!$back['status']) {
                //Log::write('workman/room:下注失败,房间号:' . $this->room_id, 'error');
                return;
            }
            //Log::write('workman/room:下注成功,房间号:' . $this->room_id, 'info');
            $message = array('type' => 'betNotice', 'info' => array('userId' => $userId, 'roomId' => $roomId,
                'raceNum' => $raceNum, 'betLocation' => $betLocation, 'betVal' => $betVal));
            $this->broadcast_to_all_member($message);
        } catch (Exception $e) {
            //Log::write($e->getMessage(), 'error');
        }
    }

    public function cancel_bet_by_location($userId, $roomId, $raceNum, $betLocation)
    {
        $message = array('type' => 'cancelBetSuccessNotice', 'info' => array('userId' => $userId, 'roomId' => $roomId,
            'raceNum' => $raceNum, 'betLocation' => $betLocation)); //删除下注通知成功
        $this->broadcast_to_all_member($message);
    }

}