<?php


namespace app\race\service\socket;


use app\race\model\PlayerOP;
use app\race\model\RoomOP;
use app\race\service\BetRecordServer;
use app\race\service\RaceServer;

class SocketServer
{
    public function __construct()
    {
        $this->RaceServer = new RaceServer();
        $this->RoomOp = new  RoomOP();
        $this->PlayerOP = new PlayerOP();
        $this->BetRecordServer = new BetRecordServer();
    }

    public function change_race_state($room_id, $race_num, $state)
    {
        $this->RaceServer->change_race_state($room_id, $race_num, $state);
    }

    public function change_room_state($room_id, $state)
    {
        $this->RoomOp->change_room_state($room_id, $state);
    }

    public function change_race_landlord($room_id, $running_race_num, $landlordId)
    {
        $this->RaceServer->change_race_landlord($room_id, $running_race_num, $landlordId);
    }

    public function get_member_info_in_the_room($user_id, $room_id)
    {
        return $this->PlayerOP->get_member_info_in_the_room($user_id, $room_id);
    }

    public function to_bet($userId, $roomId, $raceNum, $betLocation, $betVal)
    {
        return $this->BetRecordServer->to_bet($userId, $roomId, $raceNum, $betLocation, $betVal);
    }

    public function get_race_result($room_id, $race_num){
        return $this->RaceServer->get_race_result($room_id, $race_num);
    }

}