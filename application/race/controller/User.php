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
            echo getJsonStringByParam(0, "param_error", "");
        }
    }

    public function create_visit_account()
    {
        header("Access-Control-Allow-Origin: *");
        $result_array = $this->UserServer->create_visit_account();
        echo arrayToJson($result_array);
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

    public function mod_account_info()
    {

    }

    //http://120.26.52.88/phpserver/index.php/race/user/test
    public function test()
    {
        var_dump('welcome');
        Log::record('测试日志信息');
        Log::record('测试日志信息，这是警告级别','error');
        Log::write('测试日志信息，这是警告级别，并且实时写入','notice');
    }
}