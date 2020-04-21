<?php


namespace app\race\service\socket\action;


use app\race\service\socket\Room;
use app\race\service\socket\SocketData;
use app\race\service\socket\SocketServer;

class LandlordSelectedAction
{
    private $socketServer;
    private $socketData;

    public function __construct(SocketServer $socketServer, SocketData $socketData)
    {
        $this->socketData = $socketData;
        $this->socketServer = $socketServer;
    }

    public function landlordSelected(Room $room, $raceNum, $landlordId)
    {
        if ($room->getRunningRaceNum() != $raceNum) {
            return false;
        }
        $RACE_PLAY_STATE = json_decode(RACE_PLAY_STATE, true);
        $theRaceState = $room->getRaceState($raceNum);
        if ($theRaceState != $RACE_PLAY_STATE['CHOICE_LANDLORD']) {
            return false;
        }
        $room->rapLandlordUserList[] = $landlordId;
        $message = array('type' => 'memberWaitForLandlord', 'info' => array('userId' => $landlordId));//用户选择当庄
        $room->broadcastToAllMember($message);
    }
}