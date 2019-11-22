<?php


namespace app\race\service\socket;


use app\race\model\RoomOP;
use app\race\service\RaceServer;

class SocketServer
{
    public function __construct()
    {
        $this->RaceServer = new RaceServer();
        $this->RoomOp = new  RoomOP();
    }

    public function change_race_state($room_id, $race_num, $state)
    {
        $this->RaceServer->change_race_state($room_id, $race_num, $state);
    }

    public function change_room_state($room_id , $state)
    {
        $this->RoomOp->change_room_state($room_id, $state);
    }
}