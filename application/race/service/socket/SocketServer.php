<?php


namespace app\race\service\socket;


use app\race\model\PlayerOP;
use app\race\model\RoomOP;
use app\race\service\BetRecordServer;
use app\race\service\RaceServer;
use app\race\service\RoomServer;

class SocketServer
{
    public function __construct()
    {
        $this->RaceServer = new RaceServer();
        $this->RoomOp = new  RoomOP();
        $this->PlayerOP = new PlayerOP();
        $this->BetRecordServer = new BetRecordServer();
        $this->RoomServer = new RoomServer();
    }

    public function change_race_state($room_id, $race_num, $state)
    {
        $this->RaceServer->change_race_state($room_id, $race_num, $state);
    }

    public function change_room_state($room_id, $state)
    {
        $this->RoomOp->change_room_state($room_id, $state);
    }

    public function change_race_landlord($room_id, $running_race_num, $landlordId, $landlordLastCount)
    {
        $this->RaceServer->change_race_landlord($room_id, $running_race_num, $landlordId, $landlordLastCount);
    }

    public function get_member_info_in_the_room($user_id, $room_id)
    {
        return $this->PlayerOP->get_member_info_in_the_room($user_id, $room_id);
    }

    public function to_bet($userId, $roomId, $raceNum, $betLocation, $betVal)
    {
        return $this->BetRecordServer->to_bet($userId, $roomId, $raceNum, $betLocation, $betVal);
    }

    //一个房间单场比赛的结果
    public function get_race_result($room_id, $race_num){
        return $this->RaceServer->get_race_result($room_id, $race_num);
    }

    //一个房间所有比赛完毕后总的结果
    public function get_room_result($room_id){
        return $this->RoomServer->get_room_result($room_id);
    }
}