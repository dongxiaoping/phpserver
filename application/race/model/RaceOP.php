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

use app\race\model\table\Race;
use think\Db;

class RaceOP extends BaseOP
{
    public function __construct()
    {
        $this->race = new Race();
        parent::__construct($this->race);
    }

    public function change_race_state($room_id, $race_num, $state)
    {
        Db::query("update race set playState=" . $state . " where raceNum=" . $race_num . " and roomId=" . $room_id);
    }

    public function change_race_landlord($room_id, $running_race_num, $landlordId)
    {
        Db::query("update race set landlordId=" . $landlordId . " where raceNum=" . $running_race_num . " and roomId=" . $room_id);
    }
}