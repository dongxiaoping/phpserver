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
use think\Log;

class RoomServer extends RoomBase
{
    public function __construct()
    {
        $this->RoomOp = new  RoomOP();
        $this->UserServer = new  UserServer();
        $this->UserOP = new  UserOP();
        $this->RaceServer = new RaceServer();
        $this->PlayerOP = new PlayerOP();
        $this->BetRecordOP = new BetRecordOP();
        $this->CostServer = new CostServer();
    }

    public function get_room_info_by_id($id)
    {
        $item = $this->RoomOp->get($id);
        if ($item === null) {
            return getInterFaceArray(0, "not_exist", "");
        }
        return getInterFaceArray(1, "success", $item);
    }

    public function get_not_begin_room_list_by_user_id($id)
    {
        $list = $this->RoomOp->get_not_begin_room_list_by_user_id($id);
        for ($i = 0; $i < count($list); $i++) {
            $member_count = $this->PlayerOP ->get_member_count_without_kickout($list[$i]["id"]);
            $list[$i]["memberCount"] = $member_count;
        }
        return getInterFaceArray(1, "success", $list);
    }

    public function get_on_room_list_by_user_id($id)
    {
        $list = $this->RoomOp->get_on_room_list_by_user_id($id);
        for ($i = 0; $i < count($list); $i++) {
            $member_count = $this->PlayerOP ->get_member_count_without_kickout($list[$i]["id"]);
            $list[$i]["memberCount"] = $member_count;
        }
        return getInterFaceArray(1, "success", $list);
    }


    public function create_room($info)
    {
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        $user_id = $info["creatUserId"];
        $info["creatTime"] = date("Y-m-d H:i:s");
        $info["modTime"] = date("Y-m-d H:i:s");
        $info["roomState"] = $ROOM_STATE['OPEN'];
        // TODO 临时全部改为轮庄
        Log::record('临时方案，创建全部是轮庄');
        $BE_LANDLORD_WAY = json_decode(BE_LANDLORD_WAY, true);
        $info["playMode"] = $BE_LANDLORD_WAY["TURN"];
        ///
        $info["oningRaceNum"] = 0;
        $item = $this->UserOP->get($user_id);
        if ($item === null) {
            return getInterFaceArray(0, "user_not_exist", "");
        }
        $play_count = $info["playCount"];
        $cost_limit = $info["costLimit"];
        $cost_value = $this->getRoomCostValue($play_count, $cost_limit, $info["roomPay"]);
        $diamond = $item["diamond"] - $cost_value;
        $info["roomFee"] = $cost_value;
        if ($diamond < 0) {
            $back_content = [];
            $back_content['has'] = $item["diamond"];
            $back_content['need'] = $cost_value;
            return getInterFaceArray(0, "diamond_not_enough", $back_content);
        }
        Log::record('将创建的房间信息插入到数据库中');
        Log::record($info);
        $room_id = $this->RoomOp->insert($info);
        Log::record('插入的结果：'.$room_id);
        if ($room_id) {
            $isRaceOk = $this->RaceServer->createRacesByRoom($room_id, $info['playCount']);
            if (!$isRaceOk) {
                return getInterFaceArray(0, "race_error", "");
            }
            //房主扣费
            $isCashOk = $this->UserOP->mod_cash_by_user_id($user_id, $cost_value, 0);
            if (!$isCashOk) {
                return getInterFaceArray(0, "cash_error", "");
            }
            $this->CostServer->add_cost_record($user_id, $room_id, $cost_value);
            $room_info = $this->RoomOp->get($room_id);
            return getInterFaceArray(1, "success", $room_info);
        }
        return getInterFaceArray(0, "faill", "");
    }

    public function is_room_exist($room_id)
    {
        $room_info = $this->RoomOp->get($room_id);
        if ($room_info) {
            return getInterFaceArray(1, "exist", '');
        }
        return getInterFaceArray(0, "not_exist", '');
    }

    public function login_in_room($user_id, $room_id)
    {
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        $ROOM_PLAY_MEMBER_STATE = json_decode(ROOM_PLAY_MEMBER_STATE, true);
        $ROOM_PLAY_MEMBER_TYPE = json_decode(ROOM_PLAY_MEMBER_TYPE, true);
        // 1、判断用户以及房间是否存在 2、判断房间是否已关闭
        $user_info = $this->UserOP->get($user_id);
        if (!$user_info) {
            Log::record('用户不存在,用户：'.$user_id);
            return getInterFaceArray(0, "user_not_exist", "");
        }
        $room_info = $this->RoomOp->get($room_id);
        if ($room_info === null) {
            Log::record('房间不存在,用户：'.$room_id);
            return getInterFaceArray(0, "room_not_exist", "");
        }
        $member_info = $this->PlayerOP->get_member_info_in_the_room($user_id, $room_id);
        $ROOM_PAY = json_decode(ROOM_PAY, true);
        $back_content = [];
        $back_content['has'] = $user_info["diamond"];
        $back_content['need'] = $room_info["roomFee"];

        //是否有足够的钻进房间
        if ($user_id == $room_info["creatUserId"] && $user_info["diamond"] < $room_info["roomFee"]) {
            return getInterFaceArray(0, "diamond_not_enough", $back_content); //账户钻不够
        }
        if ($user_id != $room_info["creatUserId"] && $user_info["diamond"] < $room_info["roomFee"] &&
            $room_info["roomPay"] == $ROOM_PAY["AA"]) {
            return getInterFaceArray(0, "diamond_not_enough", $back_content);
        }

        if ($member_info) {
            Log::record('用户进入过房间');
            if ($member_info["state"] === $ROOM_PLAY_MEMBER_STATE["KICK_OUT"] || $member_info["roleType"] === $ROOM_PLAY_MEMBER_TYPE["LIMIT"]) {
                Log::record('用户被踢出过,不能进入房间');
                return getInterFaceArray(0, "has_kickout", "");
            }
            $room_race_info = $this->get_room_race_info($room_id);
            Log::record('用户正常进入房间');
            return getInterFaceArray(1, "success", $room_race_info);
        }

        if ($room_info["roomState"] == $ROOM_STATE["CLOSE"]) {
            Log::record('用户初次进入房间，并且房间以关闭');
            $room_race_info = $this->get_room_race_info($room_id);
            return getInterFaceArray(1, "success", $room_race_info);
        }

        Log::record('用户在房间没有关闭的情况下，进入了房间');
        //根据房间的人数、房间的状态来决定当前用户的角色类型
        $valid_member = $this->PlayerOP->get_member_count_without_limit_member($room_id);
        Log::record('房间当前有效人数：'.$valid_member);
        Log::record('房间限制人数：'.$room_info["memberLimit"]);
        $role_type = $this->the_user_role_type($room_info["memberLimit"], $valid_member);//玩家类型
        //创建进入房间
        $new_member = $this->create_player_member($user_id, $room_id, $role_type,$user_info);
        Log::record('执行进入房间数据库操作');
        $isOk = $this->PlayerOP->insert($new_member);
        if ($isOk) {
            Log::record('进入房间成功');
            if ($room_info["roomState"] == $ROOM_STATE["PLAYING"]) { //扣费
                Log::record('进行扣除费用操作');
                $this->UserServer->cost_diamond_in_room($room_info['id'], $user_id);
            }
            $room_race_info = $this->get_room_race_info($room_id);
            return getInterFaceArray(1, "success", $room_race_info);
        }
        Log::record('进入房间失败异常','error');
        return getInterFaceArray(0, "in_room_fail", '');
    }

    public function create_player_member($user_id, $room_id, $role_type,$user_info){
        $ROOM_PLAY_MEMBER_STATE = json_decode(ROOM_PLAY_MEMBER_STATE, true);
        Log::record('设置的角色类型值'.$role_type);
        return  [
            'userId' => $user_id,
            'roomId' => $room_id,
            'roleType' => $role_type,
            'score' => 0,
            'nick' => $user_info['nick'],
            'icon' => $user_info['icon'],
            'state' => $ROOM_PLAY_MEMBER_STATE["ON_LINE"],
            'creatTime' => date("Y-m-d H:i:s"),
            'modTime' => date("Y-m-d H:i:s")
        ];
    }

    //用户第一次进入一个未关闭的房间时调用，根据房间的状态以及人数(限制进入以外的玩家人数)决定用户的角色
    public function  the_user_role_type($room_limit_count, $member_count_without_limit_member){
        $ROOM_PLAY_MEMBER_TYPE = json_decode(ROOM_PLAY_MEMBER_TYPE, true);
        if($member_count_without_limit_member < $room_limit_count){
            Log::record('当前用户被判定为普通玩家'.$ROOM_PLAY_MEMBER_TYPE["PLAYER"]);
            return $ROOM_PLAY_MEMBER_TYPE["PLAYER"];
        }else{
            Log::record('当前用户被判定为观战者'.$ROOM_PLAY_MEMBER_TYPE["PLAYER"]);
            return $ROOM_PLAY_MEMBER_TYPE["WATCHER"];
        }
    }

    //获取房间比赛相关的所有信息
    public function get_room_race_info($room_id)
    {
        $room_info = $this->get_room_info_by_id($room_id)['data'];
        $race_info = $this->RaceServer->getRacesByRoomId($room_id)["data"];
        $member_info = $this->PlayerOP->get_members_can_play($room_id);
        $bet_record_info = $this->BetRecordOP->getListByOneColumn('roomId', $room_id);
        return array('room' => $room_info, 'races' => $race_info, 'members' => $member_info, 'betRecords' => $bet_record_info);
    }

    public function get_room_result($room_id, $race_num)
    {
        $list = $this->RaceServer->get_race_result($room_id, 0);
        for ($i = 1; $i <= $race_num; $i++) {
            $otherList = $this->RaceServer->get_race_result($room_id, $i);
            $list = $this->to_race_merge($list, $otherList);
        }
        return getInterFaceArray(1, "success", $list);
    }
}