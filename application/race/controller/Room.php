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

namespace app\race\controller;

use app\race\service\RoomServer;

class Room
{
    public function __construct()
    {
        $this->RoomServer = new RoomServer();
    }

    public function get_room_info_by_id()
    {
        header("Access-Control-Allow-Origin: *");
        if (isset($_GET["id"])) {
            $id = $_GET["id"];
            $result_array = $this->RoomServer->get_room_info_by_id($id);
            echo arrayToJson($result_array);
        } else {
            echo getJsonStringByParam(0, "param_error", "");
        }
    }

    // http://127.0.0.1/phpserver/public/index.php/race/room/login_in_room
    public function login_in_room()
    {
        header("Access-Control-Allow-Origin: *");
        if (isset($_GET["userId"]) && isset($_GET["roomId"])) {
            $userId = $_GET["userId"];
            $roomId = $_GET["roomId"];
            $result_array = $this->RoomServer->login_in_room($userId, $roomId);
            echo arrayToJson($result_array);
        } else {
            echo getJsonStringByParam(0, "param_error", "");
        }
    }

    // http://127.0.0.1/phpserver/public/index.php/race/room/create_room
    public function create_room()
    {
        header('Access-Control-Allow-Origin: *');
        if ($_GET["creatUserId"] && $_GET["playCount"] && $_GET["memberLimit"] && $_GET["roomPay"]
            && $_GET["costLimit"]) {
            $content = [];
            $content['creatUserId'] = $_GET["creatUserId"];
            $content['playCount'] = $_GET["playCount"];
            $content['memberLimit'] = $_GET["memberLimit"];
            $content['roomPay'] = $_GET["roomPay"];
            $content['costLimit'] = $_GET["costLimit"];
            $result_array = $this->RoomServer->create_room($content);
            echo arrayToJson($result_array);
        } else {
            echo getJsonStringByParam(0, "param_error", "");
        }
    }


    //http://localhost/phpserver/public/index.php/race/room/test
    public function test()
    {
        header("Access-Control-Allow-Origin: *");
        $room_id = 217;
        $info = $this->RoomServer->get_room_result($room_id);
        var_dump($info);
    }

}