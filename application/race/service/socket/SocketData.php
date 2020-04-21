<?php
/**
 * Created by PhpStorm.
 * User: dongxiaoping-nb
 * Date: 2020/4/18
 * Time: 16:26
 */

namespace app\race\service\socket;


class SocketData
{
    private $connect_people_list = array(); //连接对象集合
    private $room_list = array();

    function __construct()
    {

    }

    public function get_room_list(){
        return $this->room_list;
    }

    public function get_people_list(){
        return $this->connect_people_list;
    }

    public function add_room(Room $room){
        $id = $room->getRoomId();
        if(isset($this->room_list[$id])){
            return false;
        }
        $this->room_list[$id] = $room;
        return true;
    }

    public function remove_room_by_id($id){
        if(isset($this->room_list[$id])){
            unset($this->room_list[$id]);
            return true;
        }
        return false;
    }

    public function get_room_by_id($id):?Room{
        if(isset($this->room_list[$id])){
            return $this->room_list[$id];
        }
        return null;
    }

    public function add_connect_people(ConnectPeople $connect_people){
        $id = $connect_people->get_connection_id();
        if(isset($this->connect_people_list[$id])){
            return false;
        }
        $this->connect_people_list[$id] = $connect_people;
        return true;
    }

    public function remove_connect_people_by_connect_id($connect_id){
        if(isset($this->connect_people_list[$connect_id])){
            unset($this->connect_people_list[$connect_id]);
            return true;
        }
        return false;
    }

    public function get_connect_people_by_connect_id($connect_id):?ConnectPeople{
        if(isset($this->connect_people_list[$connect_id])){
            return $this->connect_people_list[$connect_id];
        }
        return null;
    }

    public function get_connect_people_by_user_id($user_id):?ConnectPeople{
        foreach ($this->connect_people_list as $connect_people ) {
            $the_user_id = $connect_people->get_user_id();
            if($user_id == $the_user_id){
                return $connect_people;
            }
        }
        return null;
    }

    public function get_connect_people_list_by_room_id($room_id){
        $people_list = array();
        foreach ($this->connect_people_list as $connect_people ) {
            $the_room_id = $connect_people->get_room_id();
            if($room_id == $the_room_id){
                $people_list[] = $connect_people;
            }
        }
        return $people_list;
    }
}