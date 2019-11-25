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

use app\race\model\table\BetRecord;
use think\Db;

class BetRecordOP extends BaseOP
{
    public function __construct()
    {
        $this->bet_record = new BetRecord();
        parent::__construct($this->bet_record);
    }

    public function get_the_record($userId, $roomId, $raceNum)
    {
        $map['userId'] = $userId;
        $map['roomId'] = $roomId;
        $map['raceNum'] = $raceNum;
        $info = $this->table->where($map)->find();
        return $info;
    }

    public function update_bet_val($id, $betLocation, $new_val)
    {
        Db::query("update bet_record set ".$betLocation."=" . $new_val . " where id=" . $id);
        return true;
    }
}