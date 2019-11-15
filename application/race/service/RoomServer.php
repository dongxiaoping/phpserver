<?php
// +----------------------------------------------------------------------
// | Copyright (php), BestTV.
// +----------------------------------------------------------------------
// | Author: karl.dong
// +----------------------------------------------------------------------
// | Date：2019/11/13
// +----------------------------------------------------------------------
// | Description: 
// +----------------------------------------------------------------------

namespace app\race\service;

use app\race\model\RoomOP;
use app\race\model\UserOP;
use app\race\service\base\RoomBase;
use app\race\model\RoomPlayerOP;

class RoomServer extends RoomBase
{
    public function __construct()
    {
        $this->RoomOp = new  RoomOP();
        $this->UserOP = new  UserOP();
        $this->RaceServer = new RaceServer();
        $this->RoomPlayerOP = new RoomPlayerOP();
    }

    public function get_room_info_by_id($id)
    {
        $item = $this->RoomOp->get($id);
        if ($item === null) {
            return getInterFaceArray(0, "not_exist", "");
        }
        return getInterFaceArray(1, "success", $item);
    }

    public function create_room($info)
    {
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        $BE_LANDLORD_WAY = json_decode(BE_LANDLORD_WAY, true);
        $user_id = $info["creatUserId"];
        $info["creatTime"] = date("Y-m-d H:i:s");
        $info["modTime"] = date("Y-m-d H:i:s");
        $info["roomState"] = $ROOM_STATE['OPEN'];
        $info["oningRaceNum"] = 0;
        $info["playMode"] = $BE_LANDLORD_WAY['TURN'];
        $item = $this->UserOP->get($user_id);
        if ($item === null) {
            return getInterFaceArray(0, "user_not_exist", "");
        }
        $play_count = $info["playCount"];
        $cost_limit = $info["costLimit"];
        $cost_value = $this->getRoomCostValue($play_count, $cost_limit);
        $diamond = $item["diamond"] - $cost_value;
        if ($diamond < 0) {
            return getInterFaceArray(0, "diamond_not_enough", "");
        }
        $room_id = $this->RoomOp->insert($info);
        if ($room_id) {
            $isRaceOk = $this->RaceServer->createRacesByRoom($room_id, $info['playCount']);
            if (!$isRaceOk) {
                return getInterFaceArray(0, "race_error", "");
            }
            $isCashOk = $this->UserOP->cash_by_user_id($user_id, $cost_value);
            if (!$isCashOk) {
                return getInterFaceArray(0, "cash_error", "");
            }
            return getInterFaceArray(1, "success", $room_id);
        }
        return getInterFaceArray(0, "faill", "");
    }

    public function login_in_room($user_id, $room_id)
    {
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        $ROOM_PLAY_MEMBER_STATE = json_decode(ROOM_PLAY_MEMBER_STATE, true);
        $item = $this->UserOP->get($user_id);
        if ($item === null) {
            return getInterFaceArray(0, "user_not_exist", "");
        }
        $room_info = $this->RoomOp->get($room_id);
        if ($room_info === null) {
            return getInterFaceArray(0, "room_not_exist", "");
        }
        $room_state = $room_info["roomState"];
        if ($room_state === $ROOM_STATE["ALL_RACE_FINISHED"] || $room_state === $ROOM_STATE["CLOSE"]) {
            return getInterFaceArray(0, "room_has_close", "");
        }
        $memberLimit = $room_info["memberLimit"];
        $member_info = $this->RoomPlayerOP->get_member_info_in_the_room($user_id, $room_id);
        $member_state = $member_info["state"];
        if($member_state === $ROOM_PLAY_MEMBER_STATE["KICK_OUT"]){
            return getInterFaceArray(0, "has_kickout", "");
        }
        if ($member_info) {
            return getInterFaceArray(1, "success", '');
        }
        //////////
        $member_in_count = $this->RoomPlayerOP->get_member_count_in_the_room($room_id);
        if($member_in_count>=$memberLimit){
            return getInterFaceArray(0, "member_count_limit", "");
        }
        $ROOM_PLAY_MEMBER_TYPE = json_decode(ROOM_PLAY_MEMBER_TYPE, true);
        $item = [
            'userId' => $user_id,
            'roomId' => $room_id,
            'roleType' => $ROOM_PLAY_MEMBER_TYPE["PLAYER"],
            'score' => 0,
            'state' => $ROOM_PLAY_MEMBER_STATE["ON_LINE"],
            'creatTime' => date("Y-m-d H:i:s"),
            'modTime' => date("Y-m-d H:i:s")
        ];
        $isOk = $this->RoomPlayerOP->insert($item);
        if ($isOk) {
            return getInterFaceArray(1, "success", '');
        }
        return getInterFaceArray(0, "fail", '');
    }

}