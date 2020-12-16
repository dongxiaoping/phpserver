<?php


namespace app\race\service\socket\action;

use app\race\service\socket\BackData;
use app\race\service\socket\SocketData;
use app\race\service\socket\SocketServer;
use think\Log;

class StartGameAction
{
    private $socketServer;
    private $socketData;

    public function __construct(SocketServer $socketServer, SocketData $socketData)
    {
        $this->socketData = $socketData;
        $this->socketServer = $socketServer;
    }

    public function startGame($roomId): bool
    {
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        $room = $this->socketData->get_room_by_id($roomId);
        if ($room == null) {
            return false;
        }
        if ($room->getState() != $ROOM_STATE['OPEN']) {
            return false;
        }
        if ($room->getRunningRaceNum() != 0) {
            return false;
        }
        Log::record('开始游戏前，返回游戏玩家信息检查通知，之后启动比赛流程');//
        $room->broadcastToAllMember(BackData::getCheckRoomMemberBack($this->socketServer->get_members_can_play($roomId)));
        $this->socketServer->change_room_state($roomId, $ROOM_STATE['PLAYING']);
        $room->setState($ROOM_STATE['PLAYING']);
        $room->startRace();
        return true;
    }
}