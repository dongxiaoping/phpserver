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

use app\race\service\socket\SocketServer;
use think\worker\Server;
use app\race\service\socket\ConnectManage;
use app\race\service\socket\Room;

class Worker extends Server
{
    protected $socket = 'websocket://127.0.0.1:2346';
    public $connectManage;
    public $socketServer;
    public $roomList = array(); //房间对象集合

    public function __construct()
    {
        $this->connectManage = new ConnectManage();
        $this->socketServer = new SocketServer();
        parent::__construct();
    }

    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    public function onMessage($connection, $data)
    {
        //$connection->send('我收到你的信息了:'.$connection->id);
        $data = json_decode($data, true);
        var_dump($data);
        switch ($data['type']) {
            case 'createAndEnterRoom':
                $roomId = $data['info']['roomId'];
                $raceCount = $data['info']['raceCount'];
                $this->createAndEnterRoom($connection, $roomId, $raceCount);
                break;
            case 'enterRoom': //进入房间
                $roomId = $data['info']['roomId'];
                $this->enterRoom($roomId, $connection);
                break;
            case 'startRoomGame':
                $roomId = $data['info']['roomId'];
                $this->startRoomGame($connection, $roomId);
                break;
            case 'landlordSelected':
                $roomId = $data['info']['roomId'];
                $raceNum = $data['info']['raceNum'];
                $landlordId = $data['info']['landlordId'];
                $this->landlordSelected($roomId, $raceNum, $landlordId);
                break;
            case 'raceBet':
                $this->raceBet($data['info']['userId'], $data['info']['roomId'], $data['info']['raceNum'],
                    $data['info']['betLocation'], $data['info']['betVal']);
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
        $this->connectManage->add_connect($connection);
        var_dump('连接');
    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
        $this->connectManage->remove_connect($connection);
        var_dump('退出连接');
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

    }

    public function raceBet($userId, $roomId, $raceNum, $betLocation, $betVal)
    {
        if (!isset($this->roomList[$roomId])) {
            var_dump('房间不存在，无法下注');
            return false;
        }
        $this->roomList[$roomId]->raceBet($userId, $roomId, $raceNum, $betLocation, $betVal);
    }

    public function createAndEnterRoom($connection, $roomId, $raceCount)
    {
        if (isset($this->roomList[$roomId])) {
            var_dump('房间已存在');
            return false;
        }
        $newRoom = new Room($roomId, $raceCount, $this->connectManage, $this->socketServer);
        $this->roomList[$roomId] = $newRoom;
        $this->enterRoom($roomId, $connection);
    }

    public function enterRoom($roomId, $connection)
    {
        if (isset($this->roomList[$roomId])) {
            $this->roomList[$roomId]->add_member($connection);
            var_dump('人员加入房间');
        } else {
            var_dump('房间不存在，无法加入');
        }
    }

    public function startRoomGame($connection, $roomId)
    {
        if (!isset($this->roomList[$roomId])) {
            var_dump('房间不存在');
            return false;
        }

        $ROOM_STATE = json_decode(ROOM_STATE, true);
        $room_state = $this->roomList[$roomId]->get_room_state();
        $is_user_in_room = $this->roomList[$roomId]->is_user_in_room($connection->id);
        if ($room_state !== $ROOM_STATE['OPEN'] || (!$is_user_in_room)) {
            var_dump('房间游戏不能重复开始,或者用户不在该房间');
            return;
        } else {
            var_dump('游戏开始');
        }

        $this->roomList[$roomId]->start_game();
    }

    public function landlordSelected($roomId, $raceNum, $landlordId)
    {
        $RACE_PLAY_STATE = json_decode(RACE_PLAY_STATE, true);
        if (!isset($this->roomList[$roomId])) {
            var_dump('房间不存在,无法抢地主');
            return;
        }
        $the_race_state = $this->roomList[$roomId]->get_race_state($raceNum);
        if ($the_race_state === $RACE_PLAY_STATE['CHOICE_LANDLORD']) {
            $this->roomList[$roomId]->set_race_state($raceNum, $RACE_PLAY_STATE['ROLL_DICE']);
        } else {
            var_dump('地主已经被抢');
        }
    }

}