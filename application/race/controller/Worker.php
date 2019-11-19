<?php
// +----------------------------------------------------------------------
// | Copyright (php), BestTV.
// +----------------------------------------------------------------------
// | Author: karl.dong
// +----------------------------------------------------------------------
// | Date：2019/11/15
// +----------------------------------------------------------------------
// | Description: 
// +----------------------------------------------------------------------

namespace app\race\controller;

use think\worker\Server;
use Workerman\Lib\Timer;

class Worker extends Server
{
    protected $socket = 'websocket://127.0.0.1:2346';
    public $connectList = array(); //连接对象列表,如; array(''id'=>ob)
    public $memberInRoomList = array();
    public $roomGameList = array();
    public $rollDiceTime = 5; //摇色子持续时间 s
    public $dealTime = 8; //发牌持续时间 s
    public $betTime = 12; //下注持续时间 s
    public $showDownTime = 8; //比大小持续时间 s
    public $showResultTime = 5; //显示结果持续时间 s
    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    public function onMessage($connection, $data)
    {
        //$connection->send('我收到你的信息了:'.$connection->id);
        //$this->connectList[$connection->id]->send('我收到你的信息了:');
        $data = json_decode($data, true);
        var_dump($data);
        $type = $data['type'];
        $connectId = $connection->id;
        $info = $data['info'];
        switch ($type) {
            case 'enterRoom': //进入房间
                $this->enterRoom($info, $connectId);
                break;
            case 'startRoomGame':
                $this->startRoomGame($info);
                break;
            case 'landlordSelected':
                $this->landlordSelected($info);
                break;
            default:

        }
    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {
        if (isset($connection->id)) {
            $this->connectList[$connection->id] = $connection;
        }
    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
        if (isset($connection->id)) {
            unset($this->connectList[$connection->id]);
        }
    }

    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg)
    {
        echo "error $code $msg\n";
    }

    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker)
    {
        //$this->testTime($worker);
    }

    public function testTime($worker)
    {
        // 定时，每10秒一次
        Timer::add(10, function () use ($worker) {
            // 遍历当前进程所有的客户端连接，发送当前服务器的时间
            foreach ($worker->connections as $connection) {
                $connection->send(time());
            }
        });
    }

    public function landlordSelected($info)
    {
        $roomId = $info['roomId'];
        $raceNum = $info['raceNum'];
        $RACE_PLAY_STATE = json_decode(RACE_PLAY_STATE, true);
        if ($this->roomGameList[$roomId]['racesStatusList'][$raceNum] === $RACE_PLAY_STATE['CHOICE_LANDLORD']) {
            $this->roomGameList[$roomId]['racesStatusList'][$raceNum] = $RACE_PLAY_STATE['ROLL_DICE'];
        } else {
            var_dump('地主已经被抢');
        }
    }

    public function enterRoom($info, $connectId)
    {
        $roomId = $info['roomId'];
        if (isset($this->memberInRoomList[$roomId])) {
            if (!isset($this->memberInRoomList[$roomId][$connectId])) {
                $this->memberInRoomList[$roomId][] = $connectId;
            }
        } else {
            $this->memberInRoomList[$roomId] = [$connectId];
        }
        //  var_dump($this->memberInRoomList);
    }

    //向指定房间的所有成员发消息
    public function broadcastToRoom($roomId, $message)
    {
        if (!isset($this->memberInRoomList[$roomId])) {
            return;
        }
        foreach ($this->memberInRoomList[$roomId] as $item) {
            if (isset($this->connectList[$item])) {
                $this->connectList[$item]->send(json_encode($message));
            }
        }
    }

    public function startRoomGame($info)
    {
        $roomId = $info['roomId'];
        $result = $this->initRoomGame($info);
        if (!$result) {
            return;
        }
        $this->startRace($roomId, 0);
    }

    public function startRace($roomId, $raceNum)
    {
        if ($raceNum >= $this->roomGameList[$roomId]['raceCount']) {
            $ROOM_STATE = json_decode(ROOM_STATE, true);
            $this->roomGameList[$roomId]['roomStatus'] = $ROOM_STATE['ALL_RACE_FINISHED'];
            $message = array('type' => 'allRaceFinished', 'info' => array('roomId' => $roomId));
            $this->broadcastToRoom($roomId, $message);
            $this->removeRoom($roomId);
            var_dump('所有比赛结束');
            return;
        }
        $RACE_PLAY_STATE = json_decode(RACE_PLAY_STATE, true);
        $message = array('type' => 'choiceLandlord', 'info' => array('raceNum' => $raceNum, 'roomId' => $roomId));
        $this->roomGameList[$roomId]['racesStatusList'][$raceNum] = $RACE_PLAY_STATE['CHOICE_LANDLORD'];
        $this->broadcastToRoom($roomId, $message); //广播选地主
        $this->roomGameList[$roomId]['timer'] = Timer::add(2, function ($roomId, $raceNum) { //循环监听选地主状态是否改变 有bug 不能无限循环
            $RACE_PLAY_STATE = json_decode(RACE_PLAY_STATE, true);
            if ($this->roomGameList[$roomId]['racesStatusList'][$raceNum] !== $RACE_PLAY_STATE['CHOICE_LANDLORD']) {
                Timer::del($this->roomGameList[$roomId]['timer']);
                $this->roomGameList[$roomId]['racesStatusList'][$raceNum] = $RACE_PLAY_STATE['ROLL_DICE'];
                $message = array('type' => 'rollDice', 'info' => array('raceNum' => $raceNum, 'roomId' => $roomId));
                $this->broadcastToRoom($roomId, $message); //广播摇色子

                Timer::add($this->rollDiceTime, function ($roomId, $raceNum) {
                    $RACE_PLAY_STATE = json_decode(RACE_PLAY_STATE, true);
                    $this->roomGameList[$roomId]['racesStatusList'][$raceNum] = $RACE_PLAY_STATE['DEAL']; //发牌
                    $message = array('type' => 'deal', 'info' => array('raceNum' => $raceNum, 'roomId' => $roomId));
                    $this->broadcastToRoom($roomId, $message);

                    Timer::add($this->dealTime, function ($roomId, $raceNum) {
                        $RACE_PLAY_STATE = json_decode(RACE_PLAY_STATE, true);
                        $this->roomGameList[$roomId]['racesStatusList'][$raceNum] = $RACE_PLAY_STATE['BET']; //下注
                        $message = array('type' => 'bet', 'info' => array('raceNum' => $raceNum, 'roomId' => $roomId));
                        $this->broadcastToRoom($roomId, $message);

                        Timer::add($this->betTime, function ($roomId, $raceNum) {
                            $RACE_PLAY_STATE = json_decode(RACE_PLAY_STATE, true);
                            $this->roomGameList[$roomId]['racesStatusList'][$raceNum] = $RACE_PLAY_STATE['SHOW_DOWN']; //比大小
                            $message = array('type' => 'showDown', 'info' => array('raceNum' => $raceNum, 'roomId' => $roomId));
                            $this->broadcastToRoom($roomId, $message);

                            Timer::add($this->showDownTime, function ($roomId, $raceNum) {
                                $RACE_PLAY_STATE = json_decode(RACE_PLAY_STATE, true);
                                $this->roomGameList[$roomId]['racesStatusList'][$raceNum] = $RACE_PLAY_STATE['SHOW_RESULT']; //显示结果
                                $message = array('type' => 'showResult', 'info' => array('raceNum' => $raceNum, 'roomId' => $roomId));
                                $this->broadcastToRoom($roomId, $message);

                                Timer::add($this->showResultTime, function ($roomId, $raceNum) {
                                    $RACE_PLAY_STATE = json_decode(RACE_PLAY_STATE, true);
                                    $this->roomGameList[$roomId]['racesStatusList'][$raceNum] = $RACE_PLAY_STATE['FINISHED']; //结束
                                    $message = array('type' => 'finished', 'info' => array('raceNum' => $raceNum, 'roomId' => $roomId));
                                    $this->broadcastToRoom($roomId, $message);

                                    Timer::add(2, function ($roomId, $raceNum) {
                                        $nextRaceNum = $raceNum + 1;
                                        $this->startRace($roomId, $nextRaceNum);
                                    }, array($roomId, $raceNum), false);

                                }, array($roomId, $raceNum), false);

                            }, array($roomId, $raceNum), false);

                        }, array($roomId, $raceNum), false);

                    }, array($roomId, $raceNum), false);

                }, array($roomId, $raceNum), false);

            } else {
                var_dump('还在选地主');
            }
        }, array($roomId, $raceNum), true);

    }

    public function removeRoom($roomId)
    {
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        if (isset($this->roomGameList[$roomId]) && $this->roomGameList[$roomId]['roomStatus'] === $ROOM_STATE['ALL_RACE_FINISHED']) {
            unset($this->roomGameList[$roomId]);
        }
        if (isset($this->memberInRoomList[$roomId])) {
            unset($this->memberInRoomList[$roomId]);
        }
    }

    public function initRoomGame($info)
    {
        $RACE_PLAY_STATE = json_decode(RACE_PLAY_STATE, true);
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        $roomId = $info['roomId'];
        $raceCount = $info['raceCount'];
        if (isset($this->roomGameList[$roomId])) {
            return false;
        }
        $roomItem = array('timer' => '', 'roomStatus' => $ROOM_STATE['PLAYING'], 'raceCount' => $raceCount, 'oningRaceNum' => 0, 'racesStatusList' => []);
        for ($i = 0; $i < $raceCount; $i++) {
            $roomItem['racesStatusList'][] = $RACE_PLAY_STATE['NOT_BEGIN'];
        }
        $this->roomGameList[$roomId] = $roomItem;
        // var_dump($this->roomGameList);
        return true;
    }

}