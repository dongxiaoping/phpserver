<?php


namespace app\race\service\socket;


class ConnectManage
{
    public $connections = array(); //连接对象集合

    public function add_connect($connection){
        if (isset($connection->id)) {
            $this->connections[$connection->id] = $connection;
        }
    }

    public function remove_connect($connection){
        if (isset($connection->id)) {
            unset($this->connections[$connection->id]);
        }
    }
}