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

class User
{
    public function __construct() {
        $this->UserServer = new UserServer();
    }

    //http://localhost/phpserver/public/index.php/race/user/get_user_info_by_id?id=1
    public function  get_user_info_by_id(){
        header("Access-Control-Allow-Origin: *");
        if(isset($_GET["id"])){
            $id = $_GET["id"];
            $result_array = $this->UserServer->get_user_info_by_id($id);
            echo arrayToJson($result_array);
        }else{
            echo getJsonStringByParam(0,"param_error","");
        }
    }

    public function get_test(){
        $result_array = $this->UserServer->test();
        echo arrayToJson($result_array);
    }

    //http://localhost/phpserver/public/index.php/race/user/test
    //http://127.0.0.1/phpserver/public/index.php/race/user/test
    public function test(){
        echo 'dxp';
    }


}