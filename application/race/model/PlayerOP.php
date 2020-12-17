<?php
// +----------------------------------------------------------------------
// | Copyright (php), BestTV.
// +----------------------------------------------------------------------
// | Author: karl.dong
// +----------------------------------------------------------------------
// | Date：2018/8/16
// +----------------------------------------------------------------------
// | Description: 
// +----------------------------------------------------------------------

namespace app\race\model;

use app\race\model\table\Player;
use think\Db;
use think\Log;

class PlayerOP
{
    public function __construct()
    {

    }

    public function get_member_info_in_the_room($user_id, $room_id)
    {
        $table = new Player();
        $info = $table->where("userId", $user_id)->where("roomId", $room_id)->find();
        return $info;
    }

    //从数据库中删除不存在的成员
    public function check_room_member($room_id, $member_in_socket_list)
    {
        //Log::write("socket房间成员", 'info');
        //Log::write($member_in_socket_list, 'info');
        //Log::write("数据库中的成员", 'info');
        $member_in_database_list = $this->get_members_without_kickout($room_id);
       // Log::write($member_in_database_list, 'info');
        for ($i = 0; $i < count($member_in_database_list); $i++) {
            $data_base_user_id = $member_in_database_list[$i]["userId"];
            $is_exist = false;
            for ($j = 0; $j < count($member_in_socket_list); $j++) {
                if($data_base_user_id == $member_in_socket_list[$j]){
                    $is_exist = true;
                    break;
                }
            }
            if(!$is_exist){
                $this->cancel_member_from_room($data_base_user_id,$room_id);
               // Log::write("无效成员", 'info');
               // Log::write($data_base_user_id, 'info');
            }
        }
    }

    public function get_rand_landlord_info($room_id)
    {
        $member_list = $this->get_member_can_landlord($room_id);
        if($member_list){
            return $member_list[rand(0, count($member_list) - 1)];
        }else{
            return null;
        }
    }

    public function get_member_count_in_the_room($room_id)
    {
        $table = new Player();
        $count = $table->where("roomId", $room_id)->count();
        return $count;
    }

    public function get_member_count_without_limit_member($room_id)
    {
        $ROOM_PLAY_MEMBER_TYPE = json_decode(ROOM_PLAY_MEMBER_TYPE, true);
        $table = new Player();
        $count = $table->where("roomId", $room_id)->where("roleType",'<' ,$ROOM_PLAY_MEMBER_TYPE['LIMIT'])->count();
        return $count;
    }

    public function get_member_count_without_kickout($room_id)
    { //不包含踢出的
        $table = new Player();
        $ROOM_PLAY_MEMBER_STATE = json_decode(ROOM_PLAY_MEMBER_STATE, true);
        $stringItem = 'state!=' . $ROOM_PLAY_MEMBER_STATE['KICK_OUT'];
        $count = $table->where("roomId", $room_id)->where($stringItem)->count();
        return $count;
    }


    public function get_member_count_online($room_id)
    { //不包含踢出的
        $table = new Player();
        $ROOM_PLAY_MEMBER_STATE = json_decode(ROOM_PLAY_MEMBER_STATE, true);
        $count = $table->where("roomId", $room_id)->where('state', $ROOM_PLAY_MEMBER_STATE['ON_LINE'])->count();
        return $count;
    }

    public function get_members_without_kickout($room_id)
    {
        $table = new Player();
        $ROOM_PLAY_MEMBER_STATE = json_decode(ROOM_PLAY_MEMBER_STATE, true);
        $stringItem = 'state!=' . $ROOM_PLAY_MEMBER_STATE['KICK_OUT'];
        return $table->where('roomId', $room_id)->where($stringItem)->select();
    }

    public function get_members_can_play($room_id)
    {
        $table = new Player();
        $ROOM_PLAY_MEMBER_TYPE = json_decode(ROOM_PLAY_MEMBER_TYPE, true);
        $stringItem = 'roleType =' . $ROOM_PLAY_MEMBER_TYPE['PLAYER'];
        return $table->where('roomId', $room_id)->where($stringItem)->order('creatTime desc')->select();
    }


    public function get_members_online($room_id)
    {
        $table = new Player();
        $ROOM_PLAY_MEMBER_STATE = json_decode(ROOM_PLAY_MEMBER_STATE, true);
        $list = $table->where('roomId', $room_id)->where('state', $ROOM_PLAY_MEMBER_STATE['ON_LINE'])->select();
        return $list;
    }

    //获取能当庄的成员信息
    public function get_member_can_landlord($room_id){
        $table = new Player();
        $ROOM_PLAY_MEMBER_TYPE = json_decode(ROOM_PLAY_MEMBER_TYPE, true);
        $ROOM_PLAY_MEMBER_STATE = json_decode(ROOM_PLAY_MEMBER_STATE, true);
        $stringItem = 'roleType =' . $ROOM_PLAY_MEMBER_TYPE['PLAYER'];
        $list = $table->where('roomId', $room_id)->where($stringItem)
            ->where('state', $ROOM_PLAY_MEMBER_STATE['ON_LINE'])->order('creatTime desc')->select();
        return $list;
    }

    public function get_members_by_room_id($id)
    {
        $table = new Player();
        $list = $table->where('roomId', $id)->select();
        return $list;
    }

    public function cancel_member_from_room($user_id, $room_id)
    {
        $table = new Player();
        return $table->where("roomId", $room_id)->where("userId", $user_id)->delete();
    }

    public function change_state_in_room($user_id, $room_id, $state)
    {
        $table = new Player();
        $table->where('userId', $user_id)->where('roomId', $room_id)->update(['state' => $state]);
    }

    public function insert($info)
    {
        $table = new Player();
        $result = $table->insertGetId($info);
        Log::record('插入玩家成员结果:'.$result);
        return $result;
    }

    public function insertAll($list)
    {
        $table = new Player();
        $isOk = $table->insertAll($list);
        if ($isOk) {
            return true;
        } else {
            return false;
        }
    }


    public function del($id)
    {
        $table = new Player();
        return $table->where("id", $id)->delete();
    }

    /* $info ["category_name"=>$name,......] 除主键之外的表字段信息集合
     * */
    public function mod($id, $info)
    {
        $table = new Player();
        $table->save($info, ["id" => $id]);
    }

    public function get($id)
    {
        $table = new Player();
        return $table->where("id", $id)->find(); //查询一个数据
    }

    public function getListByOneColumn($tag, $val)
    {
        $table = new Player();
        $list = $table->where($tag, $val)->select();
        return $list;
    }

    public function getAll()
    {
        $table = new Player();
        $list = $table->select();
        return $list;
    }

}