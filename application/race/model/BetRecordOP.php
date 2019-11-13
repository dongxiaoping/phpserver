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

class BetRecordOP extends BaseOP{
    public function __construct() {
        $this->bet_record = new BetRecord();
        parent::__construct($this->bet_record);
    }
}