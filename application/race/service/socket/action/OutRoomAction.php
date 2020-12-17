<?php


namespace app\race\service\socket\action;

use app\race\service\socket\BackData;
use app\race\service\socket\SocketActionTag;
use app\race\service\socket\SocketData;
use app\race\service\socket\SocketServer;
use app\race\service\socket\WordDes;
use think\Log;

class OutRoomAction
{
    public static $OUT_USER_EXIT = 1; //表示用户手动退出
    public static $OUT_KICK_OUT = 2; //表示被踢出
    public static $ANOTHER_IN_OUT = 3; //重复登录，被退出
    private $socketServer;
    private $socketData;
    private $outRoomBack;

    public function __construct(SocketServer $socketServer, SocketData $socketData)
    {
        $this->socketData = $socketData;
        $this->socketServer = $socketServer;
        $this->outRoomBack = new BackData(SocketActionTag::$MEMBER_OUT_ROOM_NOTICE);
    }

    //踢出房间
    public function kickOutRoom($roomId, $userId)
    {
        $room = $this->socketData->get_room_by_id($roomId);
        $people = $this->socketData->get_connect_people_by_user_id($userId);
        if ($people == null) {
            $this->outRoomBack->setMessage(WordDes::$USER_NOT_CONNECT);
            $this->outRoomBack->setFlag(0);
            return false;
        }
        if ($people->get_room_id() != $roomId) {
            $this->outRoomBack->setMessage(WordDes::$USER_NOT_IN_ROOM);
            $this->outRoomBack->setFlag(0);
            return false;
        }
        if ($room == null) {
            $this->outRoomBack->setMessage(WordDes::$ROOM_NOT_EXIST);
            $this->outRoomBack->setFlag(0);
            return false;
        }
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        if ($room->getState() != $ROOM_STATE['OPEN']) {
            $this->outRoomBack->setMessage(WordDes::$ROOM_PLAYING);
            $this->outRoomBack->setFlag(0);
            return false;
        }
        $ROOM_PLAY_MEMBER_STATE = json_decode(ROOM_PLAY_MEMBER_STATE, true);
        $this->socketServer->change_member_state_in_room($userId, $roomId, $ROOM_PLAY_MEMBER_STATE['KICK_OUT']);
        $this->outRoomBack->setMessage(WordDes::$USER_OUT_SUCCESS);
        $this->outRoomBack->setFlag(1);
        $this->outRoomBack->setData(array('outType'=>self::$OUT_KICK_OUT, "userId"=>$userId));
        Log::record('该用户被踢出房间，通知所有玩家，用户：'.$userId);
        $room->broadcastToAllMember($this->outRoomBack->getBackData());
        $people->set_room_id(null);
        if(count($this->socketData->get_connect_people_list_by_room_id($roomId))<=0){
            $room->destroy();
            $this->socketData->remove_room_by_id($roomId);
            Log::record('socket房间无成员，房间销毁：'.$roomId);
        }
        return true;
    }

    //断开连接退出房间
    public function socketBreakOuRoom($connectId)
    {
        $this->outRoomBack->setFlag(1);
        $people = $this->socketData->get_connect_people_by_connect_id($connectId);
        if ($people == null) {
            Log::record('socket中无当前成员');
            return;
        }
        Log::record('退出前的成员信息数量');
        Log::record(count($this->socketData->get_people_list()));
        $roomId = $people->get_room_id();
        $userId = $people->get_user_id();
        if($roomId !=null && $userId!=null){
            $room = $this->socketData->get_room_by_id($roomId);
            if($room !=null){
                $people->set_room_id(null);
                $ROOM_STATE = json_decode(ROOM_STATE, true);
                $ROOM_PLAY_MEMBER_STATE = json_decode(ROOM_PLAY_MEMBER_STATE, true);
                if ($room->getState() == $ROOM_STATE["OPEN"]){
                    Log::record('当前房间处于打开状态，从当前房间数据库中，删除该用户，用户:'.$userId.'房间：'.$roomId);
                    $this->socketServer->cancel_member_from_room($userId, $roomId);
                }else{
                    Log::record('将用户改为离线');
                    $this->socketServer->change_member_state_in_room($userId,$roomId, $ROOM_PLAY_MEMBER_STATE['OFF_LINE']);
                }
                $this->outRoomBack->setMessage(WordDes::$USER_OUT_SUCCESS);
                $this->outRoomBack->setData(array('outType'=>self::$OUT_USER_EXIT, "userId"=>$userId));
                Log::record('用户掉线离开房间，通知所有玩家，用户：'.$userId);
                $room->broadcastToAllMember($this->outRoomBack->getBackData());
                if(count($this->socketData->get_connect_people_list_by_room_id($roomId))<=0){
                    $room->destroy();
                    $this->socketData->remove_room_by_id($roomId);
                    Log::record('socket房间无成员，房间销毁：'.$roomId, 'info');
                }
            }
        }
        $this->socketData->remove_connect_people_by_connect_id($connectId);
    }

}