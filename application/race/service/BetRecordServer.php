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

use app\race\model\BetRecordOP;

class BetRecordServer
{
    public function __construct()
    {
        $this->BetRecordOP = new  BetRecordOP();
    }

    public function get_bet_record_by_room_id($id)
    {
        $list = $this->BetRecordOP->getListByOneColumn('roomId',$id);
        return getInterFaceArray(1, "success", $list);
    }
}