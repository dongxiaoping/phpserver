<?php


namespace app\race\service\socket\action;


use app\race\service\socket\Room;
use app\race\service\socket\SocketData;
use app\race\service\socket\SocketServer;

class RaceBetAction
{
    private $socketServer;
    private $socketData;

    public function __construct(SocketServer $socketServer, SocketData $socketData)
    {
        $this->socketData = $socketData;
        $this->socketServer = $socketServer;
    }

    public function raceBet(Room $room, $userId, $raceNum, $betLocation, $betVal)
    {
        if ($raceNum != $room->getRunningRaceNum()) {
            return false;
        }
        $race_play_state = json_decode(RACE_PLAY_STATE, true);
        if ($room->getRaceState($raceNum) != $race_play_state['BET']) {
            return false;
        }
        $back = $this->socketServer->to_bet($userId, $room->getRoomId(), $raceNum, $betLocation, $betVal);
        if (!$back['status']) {
            return false;
        }
        $message = array('type' => 'betNotice', 'info' => array('userId' => $userId, 'roomId' => $room->getRoomId(),
            'raceNum' => $raceNum, 'betLocation' => $betLocation, 'betVal' => $betVal));
        $room->broadcastToAllMember($message);
        return true;
    }
}