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
            $content['creatUserId'] = $_GET["creatUserId"]; //创建者ID
            $content['playCount'] = $_GET["playCount"];  //游戏场次
            $content['memberLimit'] = $_GET["memberLimit"];  //成员数量限制
            $content['roomPay'] = $_GET["roomPay"];  //房间费用支付模式
            $content['costLimit'] = $_GET["costLimit"];  //下注上限
            $is_val_all_right = $this->RoomServer->check_vals_create_room($content);
            if(!$is_val_all_right){
                echo getJsonStringByParam(0, "param_val_error", "");
                return;
            }
            $result_array = $this->RoomServer->create_room($content);
            echo arrayToJson($result_array);
        } else {
            echo getJsonStringByParam(0, "param_error", "");
        }
    }

    public function is_room_exist()
    {
        header("Access-Control-Allow-Origin: *");
        if (isset($_GET["roomId"])) {
            $result_array = $this->RoomServer->is_room_exist($_GET["roomId"]);
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