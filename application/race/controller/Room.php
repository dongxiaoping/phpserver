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
use think\Log;

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

    //根据创建者id获取当前还未开始的比赛房间信息
    public function get_not_begin_room_list_by_user_id(){
        header("Access-Control-Allow-Origin: *");
        if (isset($_GET["userId"])) {
            $user_id = $_GET["userId"];
            $result_array = $this->RoomServer->get_not_begin_room_list_by_user_id($user_id);
            echo arrayToJson($result_array);
        } else {
            echo getJsonStringByParam(0, "param_error", "");
        }
    }

    //根据创建者id获取已创建，未结束的比赛房间信息
    public function get_on_room_list_by_user_id(){
        header("Access-Control-Allow-Origin: *");
        if (isset($_GET["userId"])) {
            $user_id = $_GET["userId"];
            $result_array = $this->RoomServer->get_on_room_list_by_user_id($user_id);
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
            Log::record('用户登录房间，用户：'.$userId.'房间：'.$roomId);
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
            Log::record('创建房间');
            $content = [];
            $content['creatUserId'] = $_GET["creatUserId"]; //创建者ID
            $content['playCount'] = $_GET["playCount"];  //游戏场次
            $content['memberLimit'] = $_GET["memberLimit"];  //成员数量限制
            $content['roomPay'] = $_GET["roomPay"];  //房间费用支付模式
            $content['costLimit'] = $_GET["costLimit"];  //下注上限
            $content['playMode'] = $_GET["playMode"]; //抢庄模式
            $is_val_all_right = $this->RoomServer->check_vals_create_room($content);
            if (!$is_val_all_right) {
                Log::record('创建房间参数无效错误','error');
                echo getJsonStringByParam(0, "param_val_error", "");
                return;
            }
            $result_array = $this->RoomServer->create_room($content);
            echo arrayToJson($result_array);
        } else {
            Log::record('创建房间参数错误','error');
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

    public function get_room_result()
    {
        header("Access-Control-Allow-Origin: *");
        if (isset($_GET["roomId"]) && isset($_GET["raceNum"])) {
            $result_array = $this->RoomServer->get_room_result($_GET["roomId"], $_GET["raceNum"]);
            echo arrayToJson($result_array);
        } else {
            echo getJsonStringByParam(0, "param_error", "");
        }
    }

    //http://localhost/phpserver/public/index.php/race/room/get_config
    public function get_config()
    {
        header("Access-Control-Allow-Origin: *");
        $info = [];
        $info["roomGame"] = config('roomGameConfig');
        $info["createDiamond"] = config('createDiamondConfig');
        echo getJsonStringByParam(1, "success", $info);
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