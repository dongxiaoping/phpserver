<?php
// +----------------------------------------------------------------------
// | Copyright (php), BestTV.
// +----------------------------------------------------------------------
// | Author: karl.dong
// +----------------------------------------------------------------------
// | Date：2018/8/28
// +----------------------------------------------------------------------
// | Description: 
// +----------------------------------------------------------------------

namespace app\race\service;

use app\race\model\RoomOP;
use app\race\model\UserOP;

class UserServer
{
    public function __construct()
    {
        $this->UserOP = new  UserOP();
        $this->RoomOp = new  RoomOP();
    }

    public function get_user_info_by_id($id)
    {
        $item = $this->UserOP->get($id);
        if ($item === null) {
            return getInterFaceArray(0, "not_exist", "");
        }
        $info = getInterFaceArray(1, "success", $item);
        $info["config"] =  config('roomGameConfig');
        return $info;
    }

    public function create_visit_account()
    {
        $USER_TYPE = json_decode(USER_TYPE, true);
        $item = [
            'score' => 0,
            'diamond' => 1000,
            'type' => $USER_TYPE['NORMAL_USER'],
            'nick' => '用户' . time(),
            'icon' => 'http://120.26.52.88/default_user_icon.png',
            'creatTime' => date("Y-m-d H:i:s"),
            'modTime' => date("Y-m-d H:i:s")
        ];
        $id = $this->UserOP->insert($item);
        if ($id) {
            $info = $this->get_user_info_by_id($id);
            return $info;
        }
        return getInterFaceArray(0, "fail", '');
    }

    public function cost_diamond_in_room($roomId, $userId)
    {
        $item = $this->RoomOp->get($roomId);
        if (!$item) {
            return getInterFaceArray(0, "room_not_exist", "");
        }
        $ROOM_PAY = json_decode(ROOM_PAY, true);
        $roomPay = $item["roomPay"];
        if ($roomPay == $ROOM_PAY["CREATOR"]) {
            return getInterFaceArray(0, "not_aa", "");
        }
        if ($userId == $item["creatUserId"]) {
            return getInterFaceArray(0, "is_creator", "");
        }
        $cost_value = $item["roomFee"];
        $isCashOk = $this->UserOP->mod_cash_by_user_id($userId, $cost_value, 0);
        if ($isCashOk) {
            return getInterFaceArray(1, "success", "");
        }
        return getInterFaceArray(0, "cash_error", "");
    }
}