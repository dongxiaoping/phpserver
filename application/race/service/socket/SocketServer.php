<?php


namespace app\race\service\socket;


use app\race\model\PlayerOP;
use app\race\model\RoomOP;
use app\race\model\UserOP;
use app\race\service\BetRecordServer;
use app\race\service\RaceServer;
use app\race\service\RoomServer;

class SocketServer
{
    private $RaceServer;
    private $RoomOp;
    private $PlayerOP;
    private $BetRecordServer;
    private $RoomServer;
    private $UserOP;
    public function __construct()
    {
        $this->RaceServer = new RaceServer();
        $this->RoomOp = new  RoomOP();
        $this->PlayerOP = new PlayerOP();
        $this->BetRecordServer = new BetRecordServer();
        $this->RoomServer = new RoomServer();
        $this->UserOP = new  UserOP();
    }

    public function change_race_state($room_id, $race_num, $state)
    {
        $this->RaceServer->change_race_state($room_id, $race_num, $state);
    }

    public function change_on_race($room_id, $on_race_num)
    {
        $this->RoomOp->change_on_race($room_id, $on_race_num);
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
    public function get_race_result($room_id, $race_num)
    {
        return $this->RaceServer->get_race_result($room_id, $race_num);
    }

    //一个房间0-指定场次号的比赛的分汇总
    public function get_room_result($room_id, $race_num)
    {
        $info = $this->RoomServer->get_room_result($room_id, $race_num);
        return $info['data'];
    }

    public function cancel_member_from_room($user_id, $room_id)
    {
        $this->PlayerOP->cancel_member_from_room($user_id, $room_id);
    }

    public function change_member_state_in_room($user_id, $room_id, $state)
    {
        $this->PlayerOP->change_state_in_room($user_id, $room_id, $state);
    }

    public function get_room_info_by_id($id)
    {
        return $this->RoomOp->get($id);
    }

    public function get_rand_landlord_user_id($room_id)
    {
        $info = $this->PlayerOP->get_rand_landlord_info($room_id);
        if ($info == null) {
            return null;
        } else {
            return $info['userId'];
        }
    }


    //核对房间人员，以传入的为准
    public function check_room_member($room_id, $member_list)
    {
        $this->PlayerOP->check_room_member($room_id, $member_list);
    }

    public function get_member_count_without_kickout($room_id){
        return $this->PlayerOP->get_member_count_without_kickout($room_id);
    }

    //用户进入游戏房间
    public function member_in_to_room($room_id, $user_id){
        $user_info = $this->UserOP->get($user_id);
        $ROOM_PLAY_MEMBER_TYPE = json_decode(ROOM_PLAY_MEMBER_TYPE, true);
        $ROOM_PLAY_MEMBER_STATE = json_decode(ROOM_PLAY_MEMBER_STATE, true);
        $item = [
            'userId' => $user_id,
            'roomId' => $room_id,
            'roleType' => $ROOM_PLAY_MEMBER_TYPE["PLAYER"],
            'score' => 0,
            'nick' => $user_info['nick'],
            'icon' => $user_info['icon'],
            'state' => $ROOM_PLAY_MEMBER_STATE["ON_LINE"],
            'creatTime' => date("Y-m-d H:i:s"),
            'modTime' => date("Y-m-d H:i:s")
        ];
        $this->PlayerOP->insert($item);
        return $item;
    }

    public function get_user_info($user_id){
        return $this->UserOP->get($user_id);
    }

    //获取房间比赛相关的所有信息
    public function get_room_race_info($room_id)
    {
        return $this->RoomServer->get_room_race_info($room_id);
    }
}