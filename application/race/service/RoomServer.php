<?php
// +----------------------------------------------------------------------
// | Copyright (php), BestTV.
// +----------------------------------------------------------------------
// | Author: karl.dong
// +----------------------------------------------------------------------
// | Date：2019/11/13
// +----------------------------------------------------------------------
// | Description: 
// +----------------------------------------------------------------------

namespace app\race\service;
use app\race\model\RoomOP;
use app\race\model\UserOP;

class RoomServer{
    public function __construct() {
        $this->RoomOp = new  RoomOP();
        $this->UserOP = new  UserOP();
    }

    public function get_room_info_by_id($id){
        $item = $this->RoomOp->get($id);
        if($item===null){
            return getInterFaceArray(0,"not_exist","");
        }
        return  getInterFaceArray(1,"success",$item);
    }

    public function create_room($info){
        $ROOM_STATE = json_decode(ROOM_STATE,true);
        $user_id = $info["creatUserId"];
        $info["creatTime"] = date("Y-m-d H:i:s");
        $info["modTime"] = date("Y-m-d H:i:s");
        $info["roomState"] = $ROOM_STATE['OPEN'];
        $info["oningRaceNum"] = 0;
        $item = $this->UserOP->get($user_id);
        if($item===null){
            return getInterFaceArray(0,"user_not_exist","");
        }
        $return_info = $this->RoomOp->insert($info);
        if($return_info){
            return  getInterFaceArray(1,"success",$return_info);
        }
        return  getInterFaceArray(0,"faill","");
    }

}