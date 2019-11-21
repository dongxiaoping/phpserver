<?php


namespace app\race\service\socket;

use Workerman\Lib\Timer;

class Room
{
    public $room_id = null;
    public $member_list = array();
    public $race_list = array();
    private $state = null; //房间状态
    private $running_race_num = 0;
    private $race_count;
    private $landlord_select_timer;

    public $rollDiceTime = 5; //摇色子持续时间 s
    public $dealTime = 8; //发牌持续时间 s
    public $betTime = 12; //下注持续时间 s
    public $showDownTime = 8; //比大小持续时间 s
    public $showResultTime = 5; //显示结果持续时间 s

    public function __construct($room_id, $race_count)
    {
        $this->race_count = $race_count;
        $this->room_id = $room_id;
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        $this->state = $ROOM_STATE['OPEN'];
        $this->init_race($race_count);
    }

    public function get_race_state($race_num)
    {
        return $this->race_list[$race_num]['state'];
    }

    public function set_race_state($race_num, $state)
    {
        $this->race_list[$race_num]['state'] = $state;
    }

    public function init_race($race_count)
    {
        $RACE_PLAY_STATE = json_decode(RACE_PLAY_STATE, true);
        for ($i = 0; $i < $race_count; $i++) {
            $item = array('state' => $RACE_PLAY_STATE['NOT_BEGIN']);
            $this->race_list[$i] = $item;
        }
        var_dump('房间初始化完毕');
    }

    public function add_member($connection)
    {
        if (!isset($this->member_list[$connection->id])) {
            $this->member_list[$connection->id] = $connection;
            var_dump('房间加入新成员');
        }
    }

    public function remove_member($connection)
    {
        if (isset($this->member_list[$connection->id])) {
            unset($this->member_list[$connection->id]);
            var_dump('成员离线');
        }
    }

    public function get_room_state()
    {
        return $this->state;
    }

    public function broadcast_to_all_member($message)
    {
        foreach ($this->member_list as $item) {
            $item->send(json_encode($message));
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
        //$message = array('type' => 'choiceLandlord', 'info' => '游戏开始了');
        //$this->broadcast_to_all_member($message);
        $this->state = $ROOM_STATE['PLAYING'];
        $this->startRace(0);
    }

    public function change_roll_dice()
    { //摇色子
        $race_play_state = json_decode(RACE_PLAY_STATE, true);
        $this->race_list[$this->running_race_num]['state'] = $race_play_state['ROLL_DICE'];
        $message = array('type' => 'rollDice', 'info' => array('raceNum' => $this->running_race_num, 'roomId' => $this->room_id));
        $this->broadcast_to_all_member($message);
    }

    public function change_deal()
    { ////发牌
        $race_play_state = json_decode(RACE_PLAY_STATE, true);
        $this->race_list[$this->running_race_num]['state'] = $race_play_state['DEAL'];
        $message = array('type' => 'deal', 'info' => array('raceNum' => $this->running_race_num, 'roomId' => $this->room_id));
        $this->broadcast_to_all_member($message);
    }

    public function change_roll_bet()
    { //下注
        $race_play_state = json_decode(RACE_PLAY_STATE, true);
        $this->race_list[$this->running_race_num]['state'] = $race_play_state['BET'];
        $message = array('type' => 'bet', 'info' => array('raceNum' => $this->running_race_num, 'roomId' => $this->room_id));
        $this->broadcast_to_all_member($message);
    }

    public function change_show_down()
    { //比大小
        $race_play_state = json_decode(RACE_PLAY_STATE, true);
        $this->race_list[$this->running_race_num]['state'] = $race_play_state['SHOW_DOWN'];
        $message = array('type' => 'showDown', 'info' => array('raceNum' => $this->running_race_num, 'roomId' => $this->room_id));
        $this->broadcast_to_all_member($message);
    }

    public function change_show_result()
    { //显示结果
        $race_play_state = json_decode(RACE_PLAY_STATE, true);
        $this->race_list[$this->running_race_num]['state'] = $race_play_state['SHOW_RESULT'];
        $message = array('type' => 'showResult', 'info' => array('raceNum' => $this->running_race_num, 'roomId' => $this->room_id));
        $this->broadcast_to_all_member($message);
    }

    public function change_finished()
    { //结束
        $race_play_state = json_decode(RACE_PLAY_STATE, true);
        $this->race_list[$this->running_race_num]['state'] = $race_play_state['FINISHED'];
        $message = array('type' => 'finished', 'info' => array('raceNum' => $this->running_race_num, 'roomId' => $this->room_id));
        $this->broadcast_to_all_member($message);
    }


    public function startRace($raceNum)
    {
        if ($raceNum >= $this->race_count) {
            $ROOM_STATE = json_decode(ROOM_STATE, true);
            $this->state = $ROOM_STATE['ALL_RACE_FINISHED'];
            $message = array('type' => 'allRaceFinished', 'info' => array('roomId' => $this->room_id));
            $this->broadcast_to_all_member($message);
            var_dump('所有比赛结束');
            return;
        }

        $RACE_PLAY_STATE = json_decode(RACE_PLAY_STATE, true);
        $this->race_list[$raceNum]['state'] = $RACE_PLAY_STATE['CHOICE_LANDLORD'];
        $message = array('type' => 'choiceLandlord', 'info' => array('raceNum' => $raceNum, 'roomId' => $this->room_id));
        $this->broadcast_to_all_member($message); //广播选地主

        $this->landlord_select_timer = Timer::add(2, function () {
            $race_play_state = json_decode(RACE_PLAY_STATE, true);
            if ($this->race_list[$this->running_race_num]['state'] !== $race_play_state['CHOICE_LANDLORD']) {
                Timer::del($this->landlord_select_timer);
                ///////////////////////
                $this->change_roll_dice();

                Timer::add($this->rollDiceTime, function () {
                    $this->change_deal();

                    Timer::add($this->dealTime, function () {
                        $this->change_roll_bet();

                        Timer::add($this->betTime, function () {

                            $this->change_show_down();
                            Timer::add($this->showDownTime, function () {
                                $this->change_show_result();
                                Timer::add($this->showResultTime, function () {
                                    $this->change_finished();
                                    Timer::add(2, function () {
                                        $nextRaceNum = $this->running_race_num + 1;
                                        $this->startRace($nextRaceNum);
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
        });
    }

}