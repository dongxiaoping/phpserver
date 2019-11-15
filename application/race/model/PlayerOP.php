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

class PlayerOP extends BaseOP{
    public function __construct() {
        $this->room_player = new Player();
        parent::__construct($this->room_player);
    }

    public function get_member_info_in_the_room($user_id, $room_id)
    {
        $info = $this->table->where("userId",$user_id)->where("roomId",$room_id)->find();
        return $info;
    }

    public function get_member_count_in_the_room($room_id){
        $count = $this->table->where("roomId",$room_id)->count();
        return $count;
    }

    public function get_members_by_room_id($id){
        $list = $this->table->where('roomId',$id)->select();
        return $list;
    }

}