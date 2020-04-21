<?php


namespace app\race\service\socket\action;

use app\race\service\socket\BackData;
use app\race\service\socket\SocketData;
use app\race\service\socket\SocketServer;
use app\race\service\socket\WordDes;
use think\Log;

class OutRoomAction
{
    public static $OUT_SOCKET_BREAK = 1; //socket断开退出
    public static $OUT_USER_EXIT = 2; //表示用户手动退出
    public static $OUT_KICK_OUT = 3; //表示被踢出
    private $socketServer;
    private $socketData;
    private $outRoomBack;

    public function __construct(SocketServer $socketServer, SocketData $socketData)
    {
        $this->socketData = $socketData;
        $this->socketServer = $socketServer;
        $this->outRoomBack = new BackData("outRoom");
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
        $room->broadcastToAllMember($this->outRoomBack->getBackData());
        $this->socketServer->get_connect_people_by_user_id($userId)->set_room_id(null);
        return true;
    }

    //断开连接退出房间
    public function socketBreakOuRoom($connectId)
    {
        $people = $this->socketData->get_connect_people_by_connect_id($connectId);
        if ($people == null) {
            return;
        }
        $roomId = $people->get_room_id();
        $userId = $people->get_user_id();
        if ($roomId == null) {
            $this->socketData->remove_connect_people_by_connect_id($connectId);
            return;
        }
        $room = $this->socketData->get_room_by_id($roomId);
        if ($room == null || $userId == null) {
            $this->socketData->remove_connect_people_by_connect_id($connectId);
            return;
        }
        $this->outRoomBack->setIsSuccess(1);
        $this->outRoomBack->setUserId($userId);
        $people->set_room_id(null);
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        $ROOM_PLAY_MEMBER_STATE = json_decode(ROOM_PLAY_MEMBER_STATE, true);
        if ($room->getState() == $ROOM_STATE["OPEN"]){
            $this->socketServer->cancel_member_from_room($userId, $roomId);
        }else{
            $this->socketServer->change_member_state_in_room($userId,$roomId, $ROOM_PLAY_MEMBER_STATE['OFF_LINE']);
        }
        $room->broadcastToAllMember($this->outRoomBack->getBackData());
    }

}