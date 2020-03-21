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
use think\Log;
use think\worker\Server;
use app\race\service\socket\ConnectManage;
use app\race\service\socket\Room;
use Workerman\Lib\Timer;

class Worker extends Server
{
    protected $socket = 'websocket://0.0.0.0:2346';
    public $connectManage;
    public $socketServer;
    public $roomList = array(); //房间对象集合
    private $circle_room_check_timer = null;

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
        $data = json_decode($data, true);
        //Log::write('-----------------------------------------------------------------------', 'info');
        //Log::write($data, 'info');
        if ($this->circle_room_check_timer === null) {
            //Log::write('workman/worker:启动房间检查', 'info');
            $this->startRoomCheck();
        }
        switch ($data['type']) {
            case 'enterRoom': //进入房间
                if (isset($data['info']['roomId']) && isset($data['info']['userId'])) {
                    $roomId = $data['info']['roomId'];
                    $userId = $data['info']['userId'];
                    $this->enterRoom($roomId, $connection, $userId); //只表示进入socket房间，进入的前提是通过接口进入数据库房间成功
                } else {
                    //Log::write('workman/worker:参数错误', 'error');
                }
                break;
            case 'outRoom': //退出房间
                if (isset($data['info']['roomId']) && isset($data['info']['userId'])) {
                    $roomId = $data['info']['roomId'];
                    $userId = $data['info']['userId'];
                    $this->outRoom($roomId, $userId, $connection->id);
                } else {
                    //Log::write('workman/worker:参数错误', 'error');
                }
                break;
            case 'startRoomGame':
                if (isset($data['info']['roomId']) && isset($data['info']['userId'])) {
                    $roomId = $data['info']['roomId'];
                    $userId = $data['info']['userId'];
                    $this->startRoomGame($connection, $roomId, $userId);
                } else {
                    //Log::write('workman/worker:参数错误', 'error');
                }
                break;
            case 'landlordSelected': //玩家选择当地主通知
                if (isset($data['info']['roomId']) && isset($data['info']['raceNum']) && isset($data['info']['landlordId'])) {
                    $roomId = $data['info']['roomId'];
                    $raceNum = $data['info']['raceNum'];
                    $landlordId = $data['info']['landlordId'];
                    $this->landlordSelected($roomId, $raceNum, $landlordId);
                } else {
                    //Log::write('workman/worker:参数错误', 'error');
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
                    //Log::write('workman/worker:参数错误', 'error');
                }
                break;
            case 'cancelRaceBet': //取消指定房间、指定用户、指定场次、指定位置的下注
                if (isset($data['info']['userId']) && isset($data['info']['roomId']) && isset($data['info']['raceNum'])
                    && isset($data['info']['betLocation'])) {
                    $userId = $data['info']['userId'];
                    $roomId = $data['info']['roomId'];
                    $raceNum = $data['info']['raceNum'];
                    $betLocation = $data['info']['betLocation'];
                    $this->cancelRaceBet($userId, $roomId, $raceNum, $betLocation);
                } else {
                    //Log::write('workman/worker:参数错误', 'error');
                }
                break;
            case 'chatCartonMessage': //消息动画
                if (isset($data['info']['roomId']) && isset($data['info']['info'])) {
                    $roomId = $data['info']['roomId'];
                    $info = $data['info']['info'];
                    if (isset($this->roomList[$roomId])) {
                        $this->roomList[$roomId]->chat_carton_message($info);
                    }
                } else {
                    //Log::write('workman/worker:参数错误', 'error');
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
        //Log::write('workman/worker:连接成功', 'info');
    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
        //Log::write('workman/worker:用户断开连接', 'info');
        $in_room_id = $this->connectManage->get_room_id($connection->id);
        if ($in_room_id != null && isset($this->roomList[$in_room_id])) {
            if (!$this->roomList[$in_room_id]->is_room_valid()) {
                //Log::write('workman/worker:房间无效，销毁房间,房间ID:' . $in_room_id, 'info');
                $this->roomList[$in_room_id]->destroy();
                unset($this->roomList[$in_room_id]);
                return;
            }
            $member_info = $this->roomList[$in_room_id]->get_member_by_connection_id($connection->id);
            if ($member_info != null) {
                $this->roomList[$in_room_id]->out_member($member_info['user_id']);
                //Log::write('workman/worker:断开连接，用户退出房间', 'info');
            } else {
                //Log::write('workman/worker:房间中未找到用户相关信息', 'error');
            }
        } else {
            //Log::write('workman/worker:用户不在房间或者房间未创建', 'info');
        }
        $this->connectManage->remove_connect($connection);
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
            //Log::write('workman/worker:房间不存在，无法下注', 'error');
            return false;
        }
        $this->roomList[$roomId]->raceBet($userId, $roomId, $raceNum, $betLocation, $betVal);
    }

    public function cancelRaceBet($userId, $roomId, $raceNum, $betLocation)
    {
        if (!isset($this->roomList[$roomId])) {
            //Log::write('workman/worker:房间不存在，无法取消下注', 'error');
            return false;
        }
        $this->roomList[$roomId]->cancel_bet_by_location($userId, $roomId, $raceNum, $betLocation);
    }

    public function enterRoom($roomId, $connection, $userId)
    {
        $room_info = $this->socketServer->get_room_info_by_id($roomId);
        if (!$room_info) {
            //Log::write('workman/worker:无法进入房间，该房间在数据库中不存在，房间号：' . $roomId, 'error');
            return false;
        }
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        if ($room_info["roomState"] == $ROOM_STATE["CLOSE"]) {
            //Log::write('workman/worker:游戏已结束，无法进入房间，房间号：' . $roomId, 'error');
            return false;
        }


        if (isset($this->roomList[$roomId]) && (!$this->roomList[$roomId]->is_room_valid())) { //socket房间存在 并且无效 删除socket房间
            $this->roomList[$roomId]->destroy();
            unset($this->roomList[$roomId]);
            //Log::write('workman/worker:无效房间，销毁，房间号：' . $roomId, 'info');
        }

        if (!isset($this->roomList[$roomId])) { //socket房间不存在
            $create_user_id = $room_info['creatUserId'];
            $newRoom = new Room($roomId, $create_user_id, $room_info["playCount"], $this->connectManage, $this->socketServer);
            $this->roomList[$roomId] = $newRoom;
            $this->delInvalidRoom(); //创建一个房间的同时，检查删除无效房间
            //Log::write('workman/worker:socket房间创建成功，房间号：' . $roomId, 'info');
        }

        $is_right = $this->roomList[$roomId]->add_member($connection, $userId);
        if ($is_right) {
            //Log::write('workman/worker:人员加入房间成功', 'info');
            return true;
        } else {
            //Log::write('workman/worker:人员加入房间失败', 'error');
            return false;
        }
    }

    public function outRoom($roomId, $userId, $connection_id)
    {
        $this->connectManage->remove_room_id($connection_id);
        if (isset($this->roomList[$roomId])) {
            $this->roomList[$roomId]->out_member($userId);
            if (!$this->roomList[$roomId]->is_room_valid()) {
                //Log::write('workman/worker:房间无效，销毁房间,房间ID:' . $roomId, 'info');
                $this->roomList[$roomId]->destroy();
                unset($this->roomList[$roomId]);
            }
            //Log::write('workman/worker:用户主动退出房间', 'info');
            return true;
        } else {
            //Log::write('workman/worker:用户主动退出房间,但是房间不存在', 'error');
            //Log::write('workman/worker:房间ID:' . $roomId . ',用户ID:' . $userId . ',连接ID:' . $connection_id, 'error');
            return false;
        }
    }

    public function startRoomGame($connection, $roomId, $userId)
    {
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        $room_info = $this->socketServer->get_room_info_by_id($roomId);
        if (!$room_info) { //房间在数据库中不存在
            return false;
        }

        if ($room_info["roomState"] != $ROOM_STATE["OPEN"]) { //房间不需要开始
            return false;
        }

        if (isset($this->roomList[$roomId]) && (!$this->roomList[$roomId]->is_room_valid())) { //socket房间存在 并且无效 删除socket房间
            $this->roomList[$roomId]->destroy();
            unset($this->roomList[$roomId]);
        }

        if (!isset($this->roomList[$roomId])) { //socket房间不存在
            $create_user_id = $room_info['creatUserId'];
            $newRoom = new Room($roomId, $create_user_id, $room_info["playCount"], $this->connectManage, $this->socketServer);
            $this->roomList[$roomId] = $newRoom;
            $this->delInvalidRoom();
        }

        $room_state = $this->roomList[$roomId]->get_room_state();
        if ($room_state != $ROOM_STATE['OPEN']) { //房间游戏不能重复开始
            return false;
        }
        $this->roomList[$roomId]->start_game();
    }

    //抢地主
    public function landlordSelected($roomId, $raceNum, $landlordId)
    {
        if (!isset($this->roomList[$roomId])) {
            //Log::write('workman/worker:房间不存在,无法抢地主', 'error');
            return;
        }
        $this->roomList[$roomId]->landlord_selected($raceNum, $landlordId);
    }

    //删除无效的房间
    public function delInvalidRoom()
    {
        foreach ($this->roomList as $roomItem) {
            if (!$roomItem->is_room_valid()) {
                //Log::write('workman/worker:发现无效socket房间，销毁,房间ID:' . $roomItem->room_id, 'info');
                $this->roomList[$roomItem->room_id]->destroy();
                unset($this->roomList[$roomItem->room_id]);
            }
        }
    }

    public function startRoomCheck()
    {
        $this->circle_room_check_timer = Timer::add(config('roomGameConfig.invalidRoomCheckTime'), function () {
            //Log::write('workman/worker:定时房间检查,房间数量：' . count($this->roomList), 'info');
            $this->delInvalidRoom();
        }, array(), true);
    }

}