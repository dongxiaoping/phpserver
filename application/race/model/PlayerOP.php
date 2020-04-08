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
        Log::write("socket房间成员", 'info');
        Log::write($member_in_socket_list, 'info');
        Log::write("数据库中的成员", 'info');
        $member_in_database_list = $this->get_members_without_kickout($room_id);
        Log::write($member_in_database_list, 'info');
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
                Log::write("无效成员", 'info');
                Log::write($data_base_user_id, 'info');
            }
        }
    }

    public function get_rand_landlord_info($room_id)
    {
        $count = $this->get_member_count_without_kickout($room_id);
        if ($count <= 0) {
            return null;
        }
        $member_list = $this->get_members_without_kickout($room_id);
        return $member_list[rand(0, count($member_list) - 1)];
    }

    public function get_member_count_in_the_room($room_id)
    {
        $table = new Player();
        $count = $table->where("roomId", $room_id)->count();
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

    public function get_members_without_kickout($room_id)
    {
        $table = new Player();
        $ROOM_PLAY_MEMBER_STATE = json_decode(ROOM_PLAY_MEMBER_STATE, true);
        $stringItem = 'state!=' . $ROOM_PLAY_MEMBER_STATE['KICK_OUT'];
        $list = $table->where('roomId', $room_id)->where($stringItem)->select();
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
        Db::query("update room_player set state=" . $state . " where userId=" . $user_id . " and roomId=" . $room_id);
    }

    /////////////////
    /* $info ["category_name"=>$name,......] 除主键之外的表字段信息集合
 * */
    public function insert($info)
    {
        $table = new Player();
        $table->data($info);
        $isOk = $table->save();
        if ($isOk) {
            return $table->id;
        } else {
            return false;
        }
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