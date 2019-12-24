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
use Workerman\Lib\Timer;

class Worker extends Server
{
    protected $socket = 'websocket://0.0.0.0:2346';
    public $connectManage;
    public $socketServer;
    public $roomList = array(); //房间对象集合
    private $circle_room_check_timer = null;
    private $invalid_room_check_time = 10;//无效房间检查周期 s  90
    private $room_time_out_time = 300;//房间存在超时时间 s  默认1小时 3600

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
        var_dump($data);
        switch ($data['type']) {
            case 'createAndEnterRoom':
                if ($this->circle_room_check_timer === null) {
                    $this->roomCheck();
                }
                if (isset($data['info']['roomId']) && isset($data['info']['userId'])) {
                    $roomId = $data['info']['roomId'];
                    $userId = $data['info']['userId'];
                    $this->createAndEnterRoom($connection, $roomId, $userId);
                } else {
                    var_dump('参数错误');
                }
                break;
            case 'enterRoom': //进入房间
                if (isset($data['info']['roomId']) && isset($data['info']['userId'])) {
                    $roomId = $data['info']['roomId'];
                    $userId = $data['info']['userId'];
                    $this->enterRoom($roomId, $connection, $userId); //只表示进入socket房间，进入的前提是通过接口进入数据库房间成功
                } else {
                    var_dump('参数错误');
                }
                break;
            case 'outRoom': //退出房间
                if (isset($data['info']['roomId']) && isset($data['info']['userId'])) {
                    $roomId = $data['info']['roomId'];
                    $userId = $data['info']['userId'];
                    $this->outRoom($roomId, $userId);
                } else {
                    var_dump('参数错误');
                }
                break;
            case 'startRoomGame':
                if (isset($data['info']['roomId']) && isset($data['info']['userId'])) {
                    $roomId = $data['info']['roomId'];
                    $userId = $data['info']['userId'];
                    $this->startRoomGame($connection, $roomId, $userId);
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
            case 'cancelRaceBet': //取消指定房间、指定用户、指定场次、指定位置的下注
                if (isset($data['info']['userId']) && isset($data['info']['roomId']) && isset($data['info']['raceNum'])
                    && isset($data['info']['betLocation'])) {
                    $userId = $data['info']['userId'];
                    $roomId = $data['info']['roomId'];
                    $raceNum = $data['info']['raceNum'];
                    $betLocation = $data['info']['betLocation'];
                    $this->cancelRaceBet($userId, $roomId, $raceNum, $betLocation);
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

    public function cancelRaceBet($userId, $roomId, $raceNum, $betLocation)
    {
        if (!isset($this->roomList[$roomId])) {
            var_dump('房间不存在，无法取消下注');
            return false;
        }
        $this->roomList[$roomId]->cancel_bet_by_location($userId, $roomId, $raceNum, $betLocation);
    }

    public function createAndEnterRoom($connection, $roomId, $userId)
    {
        if (isset($this->roomList[$roomId])) {
            var_dump('socket房间已存在,不能重新创建');
            return false;
        }
        $room_info = $this->socketServer->get_room_info_by_id($roomId);
        if (!$room_info) {
            var_dump('该房间在数据库中未创建');
            return false;
        }
        if ($room_info["creatUserId"] != $userId) {
            var_dump('当前用户不是房主，不能创建socket房间');
            return false;
        }
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        if ($room_info["roomState"] !== $ROOM_STATE["OPEN"]) {
            var_dump('该房间已存在数据库中，并且已开始游戏，不能创建');
            return false;
        }
        $newRoom = new Room($roomId, $room_info["playCount"], $this->connectManage, $this->socketServer);
        $this->roomList[$roomId] = $newRoom;
        $is_success = $this->enterRoom($roomId, $connection, $userId);
        if ($is_success) {
            $message = array('type' => 'createRoomResultNotice', 'info' => array('state' => 1));
            var_dump('通知房主创建房间成功');
            $connection->send(json_encode($message));
        }
    }

    public function enterRoom($roomId, $connection, $userId)
    {
        if (isset($this->roomList[$roomId])) {
            $is_right = $this->roomList[$roomId]->add_member($connection, $userId);
            if ($is_right) {
                var_dump('人员加入房间成功');
                return true;
            } else {
                var_dump('人员加入房间失败');
                return false;
            }
        } else {
            var_dump('房间不存在，无法加入');
            return false;
        }
    }

    public function outRoom($roomId, $userId)
    {
        if (isset($this->roomList[$roomId])) {
            $this->roomList[$roomId]->out_member($userId);
            var_dump('人员退出房间');
            return true;
        } else {
            var_dump('房间不存在，无法退出房间');
            return false;
        }
    }

    public function startRoomGame($connection, $roomId, $userId)
    {
        if (!isset($this->roomList[$roomId])) {
            var_dump('websocket房间不存在,不能启动游戏');
            return false;
        }

        $ROOM_STATE = json_decode(ROOM_STATE, true);
        $room_state = $this->roomList[$roomId]->get_room_state();
        $is_user_in_room = $this->roomList[$roomId]->is_user_in_room($userId);
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

    public function roomCheck()
    {
        $this->circle_room_check_timer = Timer::add($this->invalid_room_check_time, function () {
            var_dump('定时房间检查,房间数量：' . count($this->roomList));
            foreach ($this->roomList as $roomItem) {
                $now_time = time();
                if (!($roomItem->is_room_valid()) || count($roomItem->member_list) <= 0) {
                    var_dump('发现无效socket房间，销毁');
                    $this->roomList[$roomItem->room_id]->destroy();
                    unset($this->roomList[$roomItem->room_id]);
                }
                var_dump('当前时间：' . $now_time . '__房间创建时间：' . $roomItem->creatTime);
                if ($now_time - $roomItem->creatTime >= $this->room_time_out_time) {
                    var_dump('销毁房间，房间超时');
                    $this->roomList[$roomItem->room_id]->destroy();
                    unset($this->roomList[$roomItem->room_id]);
                }
            }
        }, array(), true);
    }

}