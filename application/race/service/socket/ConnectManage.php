<?php


namespace app\race\service\socket;


class ConnectManage
{
    private $connections = array(); //连接对象集合
    private $connection_room_list = array();

    public function add_connect($connection)
    {
        if (isset($connection->id)) {
            $this->connections[$connection->id] = $connection;
        }
    }

    public function add_room_id($connection_id, $room_id)
    {
        $this->connection_room_list[$connection_id] = $room_id;
    }

    public function get_connections()
    {
        return $this->connections;
    }

    public function get_connection_by_id($connection_id)
    {
        if (isset($this->connections[$connection_id])) {
            return $this->connections[$connection_id];
        }
        return null;
    }

    public function get_room_id($connection_id)
    {
        if (isset($this->connection_room_list[$connection_id])) {
            return $this->connection_room_list[$connection_id];
        }
        return null;
    }

    public function remove_connect($connection)
    {
        if (isset($connection->id)) {
            unset($this->connections[$connection->id]);
            $this->remove_room_id($connection->id);
        }
    }

    public function remove_room_id($connection_id)
    {
        if (isset($this->connection_room_list[$connection_id])) {
            unset($this->connection_room_list[$connection_id]);
        }
    }
}