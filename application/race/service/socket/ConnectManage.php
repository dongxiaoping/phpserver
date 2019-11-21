<?php


namespace app\race\service\socket;


class ConnectManage
{
    public $list = array(); //包含connect_id、ob、room_id

    public function add_connect($connection){
        if (isset($connection->id)) {
            $this->list[$connection->id] = array('ob'=>$connection,'room_id'=>null);
        }
    }

    public function remove_connect($connection){
        if (isset($connection->id)) {
            unset($this->list[$connection->id]);
        }
    }

    public function enter_room($connection,$room_id){
        if (isset($this->list[$connection->id])) {
            $this->list[$connection->id]['room_id'] = $room_id;
        }
    }

    public function out_room($connect_id){
        if (isset($this->list[$connect_id])) {
            $this->list[$connect_id]['room_id'] = null;
        }
    }

    public function get_room_id($connect_id){
        if (isset($this->list[$connect_id]) && $this->list[$connect_id]['room_id'] !==null) {
            return $this->list[$connect_id]['room_id'];
        }
        return false;
    }
}