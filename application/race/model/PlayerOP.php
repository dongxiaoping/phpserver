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

class PlayerOP {
    public function __construct() {

    }

    public function get_member_info_in_the_room($user_id, $room_id)
    {
        $table = new Player();
        $info = $table->where("userId",$user_id)->where("roomId",$room_id)->find();
        return $info;
    }

    public function get_member_count_in_the_room($room_id){
        $table = new Player();
        $count = $table->where("roomId",$room_id)->count();
        return $count;
    }

    public function get_member_count_without_kickout($room_id){ //不包含踢出的
        $table = new Player();
        $count = $table->where("roomId",$room_id)->where('state!=3')->count();
        return $count;
    }

    public function get_members_by_room_id($id){
        $table = new Player();
        $list = $table->where('roomId',$id)->select();
        return $list;
    }

    public function cancel_member_from_room($user_id, $room_id){
        $table = new Player();
        return $table->where("roomId", $room_id)->where("userId", $user_id)->delete();
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