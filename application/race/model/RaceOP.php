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

class RaceOP extends BaseOP{
    public function __construct() {
        $this->race = new Race();
        parent::__construct($this->race);
    }
}