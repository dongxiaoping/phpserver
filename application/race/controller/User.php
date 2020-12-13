<?php
// +----------------------------------------------------------------------
// | Copyright (php), BestTV.
// +----------------------------------------------------------------------
// | Author: karl.dong
// +----------------------------------------------------------------------
// | Date：2019/11/12
// +----------------------------------------------------------------------
// | Description: 
// +----------------------------------------------------------------------

namespace app\race\controller;

use app\race\service\UserServer;
use think\Log;

class User
{
    public function __construct()
    {
        $this->UserServer = new UserServer();
    }

    //http://localhost/phpserver/public/index.php/race/user/get_user_info_by_id?id=1
    public function get_user_info_by_id()
    {
        header("Access-Control-Allow-Origin: *");
        if (isset($_GET["id"])) {
            $id = $_GET["id"];
            $result_array = $this->UserServer->get_user_info_by_id($id);
            echo arrayToJson($result_array);
        } else {
            echo getJsonStringByParam(0, "请求参数错误！", "");
        }
    }

    public function login_in()
    {
        header("Access-Control-Allow-Origin: *");
        if (isset($_GET["phone"]) && isset($_GET["password"])) {
            $password = $_GET["password"];
            $phone = $_GET["phone"];
            $result_array = $this->UserServer->get_user_info_by_login_in($phone, $password);
            echo arrayToJson($result_array);
        } else {
            echo getJsonStringByParam(0, "参数错误！", "");
        }
    }

    public function create_visit_account()
    {
        header("Access-Control-Allow-Origin: *");
        $result_array = $this->UserServer->create_visit_account();
        echo arrayToJson($result_array);
    }

    //http://localhost/phpserver/public/index.php/race/user/create_account
    public function create_account()
    {
        header("Access-Control-Allow-Origin: *");
        $baseData = trim($_POST['file']);
        $nick = trim($_POST['nick']);
        $phone = trim($_POST['phone']);
        $password = trim($_POST['password']);
        $iconName = $this->UserServer->loadUserIcon($baseData);
        if($iconName == null){
            echo getJsonStringByParam(0, "上传异常", "");
        }else{
            $result_array = $this->UserServer->create_account($phone, $password, $nick, $iconName);
            echo arrayToJson($result_array);
        }
    }

    //游戏开始后调用扣除钻
    public function cost_diamond_in_room()
    {
        header('Access-Control-Allow-Origin: *');
        if ($_GET["userId"] && $_GET["roomId"]) {
            $userId = $_GET["userId"];
            $roomId = $_GET["roomId"];
            $result_array = $this->UserServer->cost_diamond_in_room($roomId, $userId);
            echo arrayToJson($result_array);
        } else {
            echo getJsonStringByParam(0, "param_error", "");
        }
    }

    public function get_user_diamond()
    {
        header('Access-Control-Allow-Origin: *');
        if ($_GET["userId"]) {
            $result_array = $this->UserServer->get_user_diamond($_GET["userId"]);
            echo arrayToJson($result_array);
        } else {
            echo getJsonStringByParam(0, "param_error", "");
        }
    }


    //冲钻
    public function recharge_diamond()
    {
        header('Access-Control-Allow-Origin: *');
        if ($_GET["userId"] && $_GET["diamondCount"]) {
            $result_array = $this->UserServer->recharge_diamond($_GET["userId"], $_GET["diamondCount"]);
            echo arrayToJson($result_array);
        } else {
            echo getJsonStringByParam(0, "param_error", "");
        }
    }

    public function mod_account_info()
    {

    }

    //http://120.26.52.88/phpserver/index.php/race/user/test
    //http://localhost/phpserver/public/index.php/race/user/test
    public function test()
    {
        var_dump('welco2me');
        Log::record('debug级别日志','debug');
        Log::info('error级别日志', 'error');
        Log::record('测试日志信息，这是警告级别', 'error');
        Log::write('测试日志信息，这是警告级别，并且实时写入', 'notice');
    }
}