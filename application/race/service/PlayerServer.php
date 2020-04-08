<?php
// +----------------------------------------------------------------------
// | Copyright (php), BestTV.
// +----------------------------------------------------------------------
// | Author: karl.dong
// +----------------------------------------------------------------------
// | Date：2019/11/14
// +----------------------------------------------------------------------
// | Description: 
// +----------------------------------------------------------------------

namespace app\race\service;

use app\race\model\PlayerOP;

class PlayerServer
{
    public function __construct()
    {
        $this->PlayerOP = new  PlayerOP();
    }

    public function get_members_by_room_id($id){
        $list = $this->PlayerOP->get_members_by_room_id($id);
        return getInterFaceArray(1, "success", $list);
    }

    public function cancel_member_from_room($user_id, $room_id){
        $this->PlayerOP->cancel_member_from_room($user_id, $room_id);
    }

    //清除房间的所有玩家，只有在房间未开始的时候才能执行
    public function clear_all_member_in_room($room_id){
        $this->PlayerOP->cancel_member_from_room($user_id, $room_id);
    }
}
