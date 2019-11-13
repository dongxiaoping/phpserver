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
use app\race\model\table\Room;
use think\Db;

class RoomOP extends BaseOP{
    public function __construct() {
        $this->room = new Room();
        parent::__construct($this->room);
    }
}