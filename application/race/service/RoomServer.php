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

use app\race\model\BetRecordOP;
use app\race\model\PlayerOP;
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
        $this->PlayerOP = new PlayerOP();
        $this->BetRecordOP = new BetRecordOP();
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
        $info["playMode"] = $BE_LANDLORD_WAY['RAP'];
        $item = $this->UserOP->get($user_id);
        if ($item === null) {
            return getInterFaceArray(0, "user_not_exist", "");
        }
        $play_count = $info["playCount"];
        $cost_limit = $info["costLimit"];
        $cost_value = $this->getRoomCostValue($play_count, $cost_limit);
        $diamond = $item["diamond"] - $cost_value;
        $info["roomFee"] = $cost_value;
        if ($diamond < 0) {
            return getInterFaceArray(0, "diamond_not_enough", "");
        }
        $room_id = $this->RoomOp->insert($info);
        if ($room_id) {
            $isRaceOk = $this->RaceServer->createRacesByRoom($room_id, $info['playCount']);
            if (!$isRaceOk) {
                return getInterFaceArray(0, "race_error", "");
            }
            $isCashOk = $this->UserOP->mod_cash_by_user_id($user_id, $cost_value, 0);
            if (!$isCashOk) {
                return getInterFaceArray(0, "cash_error", "");
            }
            $room_info = $this->RoomOp->get($room_id);
            return getInterFaceArray(1, "success", $room_info);
        }
        return getInterFaceArray(0, "faill", "");
    }

    public function login_in_room($user_id, $room_id)
    {
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        $ROOM_PLAY_MEMBER_STATE = json_decode(ROOM_PLAY_MEMBER_STATE, true);

        ////// 1、判断用户以及房间是否存在 2、判断房间是否已关闭
        $user_info = $this->UserOP->get($user_id);
        if ($user_info === null) {
            return getInterFaceArray(0, "user_not_exist", "");
        }
        $room_info = $this->RoomOp->get($room_id);
        if ($room_info === null) {
            return getInterFaceArray(0, "room_not_exist", "");
        }
        if ($room_info["roomState"] !== $ROOM_STATE["OPEN"]) {
            return getInterFaceArray(0, "room_not_open_state", "");
        }

        //////////登录过房间情况的判断
        $member_info = $this->PlayerOP->get_member_info_in_the_room($user_id, $room_id);
        if ($member_info) {
            if ($member_info["state"] === $ROOM_PLAY_MEMBER_STATE["KICK_OUT"]) {
                return getInterFaceArray(0, "has_kickout", "");
            }
            $room_race_info = $this->get_room_race_info($room_id);
            return getInterFaceArray(1, "success", $room_race_info);
        }


        /////////房间已满的判断
        $ROOM_PLAY_MEMBER_TYPE = json_decode(ROOM_PLAY_MEMBER_TYPE, true);
        $member_in_count = $this->PlayerOP->get_member_count_in_the_room($room_id);
        if ($member_in_count >= $room_info["memberLimit"]) {
            return getInterFaceArray(0, "member_count_limit", "");
        }


        //创建进入房间
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
        $isOk = $this->PlayerOP->insert($item);
        if ($isOk) {
            $room_race_info = $this->get_room_race_info($room_id);
            return getInterFaceArray(1, "success", $room_race_info);
        }
        return getInterFaceArray(0, "fail", '');
    }

    //获取房间比赛相关的所有信息
    public function get_room_race_info($room_id)
    {
        $room_info = $this->get_room_info_by_id($room_id)['data'];
        $race_info = $this->RaceServer->getRacesByRoomId($room_id)["data"];
        $member_info = $this->PlayerOP->get_members_by_room_id($room_id);
        $bet_record_info = $this->BetRecordOP->getListByOneColumn('roomId', $room_id);
        return array('room' => $room_info, 'races' => $race_info, 'members' => $member_info, 'betRecords' => $bet_record_info);
    }

    public function get_room_result($room_id)
    {
        $room_info = $this->RoomOp->get($room_id);
        $playCount = $room_info['playCount'];
        $list = $this->RaceServer->get_race_result($room_id, 0);
        for ($i = 1; $i < $playCount; $i++) {
            $otherList = $this->RaceServer->get_race_result($room_id, $i);
            $list = $this->to_race_merge($list, $otherList);
        }
        return $list;
    }

    private function to_race_merge($list, $otherList)
    {
        for ($j = 0; $j < count($otherList); $j++) {
            $is_exist = false;
            for ($i = 0; $i < count($list); $i++) {
                if($list[$i]['userId'] === $otherList[$j]['userId']){
                    $is_exist = true;
                    $list[$i]['score'] += $otherList[$j]['score'];
                    break;
                }
            }
            if(!$is_exist){
                array_push($list, $otherList[$j]);
            }
        }
        return $list;
    }

}