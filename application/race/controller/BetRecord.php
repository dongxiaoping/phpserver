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


use app\race\service\BetRecordServer;

class BetRecord
{
    public function __construct() {
        $this->BetRecordServer = new BetRecordServer();
    }
}