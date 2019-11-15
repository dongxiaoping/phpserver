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

namespace app\race\controller;


use app\race\service\RoomPlayerServer;

class RoomPlayer
{
    public function __construct() {
        $this->RoomPlayerServer = new RoomPlayerServer();
    }
}