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

    public function get_the_record($user_id, $race_id, $location)
    {
        $info = $this->table->where("userId", $user_id)->where("raceId", $race_id)->where("betLocation", $location)->find();
        return $info;
    }

    public function update_bet_val($id, $new_val)
    {
        Db::query("update bet_record set costValue=" . $new_val . " where id=" . $id);
        return true;
    }
}