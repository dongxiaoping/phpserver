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
        $content = file_get_contents("php://input");
        $content = (string)$content;
        $content = json_decode($content,true);
        if($content["creatUserId"] && $content["playCount"]&& $content["memberLimit"]&& $content["roomPay"]
            && $content["costLimit"]){
            $result_array = $this->RoomServer->create_room($content);
            echo arrayToJson($result_array);
        }else{
            echo getJsonStringByParam(0,"param_error","");
        }

    }

}