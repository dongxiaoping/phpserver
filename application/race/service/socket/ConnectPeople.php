<?php
/**
 * Created by PhpStorm.
 * User: dongxiaoping-nb
 * Date: 2020/4/18
 * Time: 16:03
 * 连接对象
 */

namespace app\race\service\socket;


class ConnectPeople
{
    private $user_id = null; //用户ID
    private $room_id = null; //房间ID
    private $connection = null; //连接对象

    function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function set_user_id($user_id){
        $this->user_id = $user_id;
    }

    public function get_user_id(){
        return $this->user_id;
    }

    public function set_room_id($room_id){
        $this->room_id = $room_id;
    }

    public function get_room_id(){
        return $this->room_id;
    }

    public function set_connection($connection){
        $this->connection = $connection;
    }

    public function get_connection(){
        return $this->connection;
    }

    public function get_connection_id(){
        if($this->connection == null){
            return null;
        }
        return $this->connection->id;
    }
}