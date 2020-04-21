<?php


namespace app\race\service\socket\action;

use app\race\service\socket\SocketData;
use app\race\service\socket\SocketServer;

class StartGameAction
{
    private $socketServer;
    private $socketData;

    public function __construct(SocketServer $socketServer, SocketData $socketData)
    {
        $this->socketData = $socketData;
        $this->socketServer = $socketServer;
    }

    public function startGame($roomId):bool
    {
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        $room = $this->socketData->get_room_by_id($roomId);
        if($room == null){
            return false;
        }
        if ($room->getState() != $ROOM_STATE['OPEN']) {
            return false;
        }
        if ($room->getRunningRaceNum() != 0) {
            return false;
        }
        $this->socketServer->change_room_state($roomId, $ROOM_STATE['PLAYING']);
        $room->setState($ROOM_STATE['PLAYING']);
        $room->startRace();
        return true;
    }
}