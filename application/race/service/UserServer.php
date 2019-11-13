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

    public function get_user_info_by_id($id){
        $item = $this->UserOP->get($id);
        if($item===null){
            return getInterFaceArray(0,"not_exist","");
        }
        return  getInterFaceArray(1,"success",$item);
    }

    public function test(){
        return  getInterFaceArray(0,"用户不存在",$this->UserOP->get('1'));
    }
}