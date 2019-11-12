<?php
// +----------------------------------------------------------------------
// | Copyright (php), BestTV.
// +----------------------------------------------------------------------
// | Author: karl.dong
// +----------------------------------------------------------------------
// | Date：2018/8/28
// +----------------------------------------------------------------------
// | Description: 
// +----------------------------------------------------------------------

namespace app\race\service;
use app\race\model\UserOP;

class UserServer{
    public function __construct() {
        $this->UserOP = new  UserOP();
    }

    public function test(){
        return  getInterFaceArray(0,"用户不存在",$this->UserOP->get('1'));
    }
}