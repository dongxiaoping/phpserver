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
}
