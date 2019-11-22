<?php


namespace app\race\service\socket;


class ConnectManage
{
    public $list = array();

    public function add_connect($connection){
        if (isset($connection->id)) {
            $this->list[$connection->id] = $connection;
        }
    }

    public function remove_connect($connection){
        if (isset($connection->id)) {
            unset($this->list[$connection->id]);
        }
    }
}