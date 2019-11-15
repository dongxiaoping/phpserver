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

class RoomServer extends RoomBase
{
    public function __construct()
    {
        $this->RoomOp = new  RoomOP();
        $this->UserOP = new  UserOP();
        $this->RaceServer = new RaceServer();
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
            $isRaceOk = $this->RaceServer->createRacesByRoom($room_id,$info['playCount']);
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

}