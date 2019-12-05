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

    public function create_visit_account(){
        $USER_TYPE = json_decode(USER_TYPE, true);
        $item = [
            'score' => 0,
            'diamond' => 10000,
            'type' => $USER_TYPE['NORMAL_USER'],
            'nick' =>'用户'.time(),
            'creatTime' => date("Y-m-d H:i:s"),
            'modTime' =>date("Y-m-d H:i:s")
        ];
        $id = $this->UserOP->insert($item);
        if ($id) {
            $info = $this->get_user_info_by_id($id);
            return $info;
        }
        return getInterFaceArray(0, "fail", '');
    }
}