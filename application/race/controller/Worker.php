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

use app\race\service\socket\action\LandlordSelectedAction;
use app\race\service\socket\action\RaceBetAction;
use app\race\service\socket\action\OutRoomAction;
use app\race\service\socket\BackData;
use app\race\service\socket\SocketActionTag;
use app\race\service\socket\SocketInParamCheck;
use app\race\service\socket\SocketServer;
use app\race\service\socket\action\StartGameAction;
use think\Log;
use think\worker\Server;
use app\race\service\socket\SocketData;
use app\race\service\socket\ConnectPeople;
use app\race\service\socket\action\EnterRoomAction;

class Worker extends Server
{
    protected $socket = 'websocket://0.0.0.0:2346';
    public $socketData;
    public $socketServer;

    public function __construct()
    {
        $this->socketData = new SocketData();
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
        try {
            Log::record('-----------------------------------------------------------------------');
            Log::record($data);
            $data = json_decode($data, true);
            $isParamRight = SocketInParamCheck::isRight($data);
            if (!$isParamRight) {//
                Log::record("参数错误", 'error');
                $backDataBase = new BackData($data['type']);
                $backDataBase->setflag(0);
                $backDataBase->setMessage("参数错误");
                $connection->send(json_encode($backDataBase->getBackData()));
                return;
            }
            $room = $this->socketData->get_room_by_id($data['info']['roomId']);
            if ($room == null && $data['type'] != SocketActionTag::$ENTER_ROOM_REQ) {
                Log::record("房间不存在", 'error');
                $backDataBase = new BackData($data['type']);
                $backDataBase->setflag(0);
                $backDataBase->setMessage("房间不存在");
                $connection->send(json_encode($backDataBase->getBackData()));
                return;
            }
            switch ($data['type']) {
                case SocketActionTag::$ENTER_ROOM_REQ: //进入房间
                    Log::record('有用户进入房间，用户id:'.$data['info']['userId'].'房间id:'.$data['info']['roomId']);
                    $this->enterRoom($data['info']['roomId'], $connection, $data['info']['userId']);
                    break;
                case SocketActionTag::$START_GAME_REQ: //开始游戏请求
                    $startGameAction = new StartGameAction($this->socketServer, $this->socketData);
                    $startGameAction->startGame($data['info']['roomId']);
                    break;
                case SocketActionTag::$LANDLORD_SELECTED_REQ: //玩家选择当地主通知
                    $landlordSelectedAction = new LandlordSelectedAction($this->socketServer, $this->socketData);
                    $landlordSelectedAction->landlordSelected($room, $data['info']['raceNum'], $data['info']['landlordId']);
                    break;
                case SocketActionTag::$RACE_BET_REQ: //下注 下注值为负数表示取消下注
                    $raceBetAction = new RaceBetAction($this->socketServer, $this->socketData);
                    $raceBetAction->raceBet($room, $data['info']['userId'], $data['info']['raceNum'],
                        $data['info']['betLocation'], $data['info']['betVal']);
                    break;
                case SocketActionTag::$CHAT_CARTON_MESSAGE_REQ: //消息动画
                    $message = array('type' => SocketActionTag::$CHAT_CARTON_MESSAGE_REQ, 'info' => $data['info']['info']);
                    $room->broadcastToAllMember($message);
                    break;
                case SocketActionTag::$AUDIO_PLAY_NOTICE: //语音
                    $message = array('type' => SocketActionTag::$AUDIO_PLAY_NOTICE,
                        'info' => array('userId'=>$data['info']['userId'],'voiceName'=> $data['info']['voiceName'],
                         'duration'=> $data['info']['duration']));
                    $room->broadcastToAllMember($message);
                    break;
                case SocketActionTag::$KICK_OUT_MEMBER_REQ: //踢出玩家，只能在游戏未开始调用
                    $outRoomAction = new OutRoomAction($this->socketServer, $this->socketData);
                    $outRoomAction->kickOutRoom($data['info']['roomId'], $data['info']['kickUserId']);
                    break;
                default:
                case SocketActionTag::$SURE_BE_LANDLORD_IN_TURN: //用户同意轮庄当地主
                    Log::record('接到用户愿意轮庄当地主通知');
                    $landlordSelectedAction = new LandlordSelectedAction($this->socketServer, $this->socketData);
                    $landlordSelectedAction->turnLandlordSelected($room, $data['info']['raceNum'], $data['info']['userId']);
                    break;
                default:
            }
        } catch (Exception $e) {
            Log::record($e->getMessage(), 'error');
        }
    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {
        $this->socketData->add_connect_people(new ConnectPeople($connection));
    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
        $outRoomAction = new OutRoomAction($this->socketServer, $this->socketData);
        $outRoomAction->socketBreakOuRoom($connection->id);
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

    public function enterRoom($roomId, $connection, $userId)
    {
        $enterRoomAction = new EnterRoomAction($roomId, $connection, $userId, $this->socketServer, $this->socketData);
        $is_right_enter = $enterRoomAction->is_room_right_to_enter();
        if ($is_right_enter) {
            $enterRoomAction->enter_room();
            $enterRoomAction->get_enter_room_back()->setFlag(1);
        } else {
            $enterRoomAction->get_enter_room_back()->setFlag(0);
        }
        $enter_back = $enterRoomAction->get_enter_room_back();
        $connection->send(json_encode($enter_back->getBackData()));
    }
}