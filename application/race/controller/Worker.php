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
                if (isset($data['info']['roomId']) && isset($data['info']['raceCount']) && isset($data['info']['userId'])) {
                    $roomId = $data['info']['roomId'];
                    $raceCount = $data['info']['raceCount'];
                    $userId = $data['info']['userId'];
                    $this->createAndEnterRoom($connection, $roomId, $raceCount, $userId);
                } else {
                    var_dump('参数错误');
                }
                break;
            case 'enterRoom': //进入房间
                if (isset($data['info']['roomId']) && isset($data['info']['userId'])) {
                    $roomId = $data['info']['roomId'];
                    $userId = $data['info']['userId'];
                    $this->enterRoom($roomId, $connection, $userId);
                } else {
                    var_dump('参数错误');
                }
                break;
            case 'startRoomGame':
                if (isset($data['info']['roomId'])) {
                    $roomId = $data['info']['roomId'];
                    $this->startRoomGame($connection, $roomId);
                } else {
                    var_dump('参数错误');
                }
                break;
            case 'landlordSelected': //玩家选择当地主通知
                if (isset($data['info']['roomId']) && isset($data['info']['raceNum']) && isset($data['info']['landlordId'])) {
                    $roomId = $data['info']['roomId'];
                    $raceNum = $data['info']['raceNum'];
                    $landlordId = $data['info']['landlordId'];
                    $this->landlordSelected($roomId, $raceNum, $landlordId);
                } else {
                    var_dump('参数错误');
                }
                break;
            case 'raceBet': //下注
                if (isset($data['info']['userId']) && isset($data['info']['roomId']) && isset($data['info']['raceNum'])
                    && isset($data['info']['betLocation']) && isset($data['info']['betVal'])) {
                    $userId = $data['info']['userId'];
                    $roomId = $data['info']['roomId'];
                    $raceNum = $data['info']['raceNum'];
                    $betLocation = $data['info']['betLocation'];
                    $betVal = $data['info']['betVal'];
                    $this->raceBet($userId, $roomId, $raceNum, $betLocation, $betVal);
                } else {
                    var_dump('参数错误');
                }
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

    public function createAndEnterRoom($connection, $roomId, $raceCount, $userId)
    {
        if (isset($this->roomList[$roomId])) {
            var_dump('房间已存在');
            return false;
        }
        $newRoom = new Room($roomId, $raceCount, $this->connectManage, $this->socketServer);
        $this->roomList[$roomId] = $newRoom;
        $this->enterRoom($roomId, $connection, $userId);
    }

    public function enterRoom($roomId, $connection, $userId)
    {
        if (isset($this->roomList[$roomId])) {
            $this->roomList[$roomId]->add_member($connection, $userId);
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

    //抢地主
    public function landlordSelected($roomId, $raceNum, $landlordId)
    {
        $RACE_PLAY_STATE = json_decode(RACE_PLAY_STATE, true);
        if (!isset($this->roomList[$roomId])) {
            var_dump('房间不存在,无法抢地主');
            return;
        }
        $the_race_state = $this->roomList[$roomId]->get_race_state($raceNum);
        if ($the_race_state === $RACE_PLAY_STATE['CHOICE_LANDLORD']) {//该用户抢到地主了
            $this->roomList[$roomId]->landlord_selected($raceNum, $landlordId);
        } else {
            var_dump('地主已经被抢');
        }
    }

}