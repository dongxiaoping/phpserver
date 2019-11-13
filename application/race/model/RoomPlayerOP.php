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
use app\race\model\table\RoomPlayer;
use think\Db;

class RoomPlayerOP extends BaseOP{
    public function __construct() {
        $this->room_player = new RoomPlayer();
        parent::__construct($this->room_player);
    }
}