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

    public function get_test(){
        $result_array = $this->UserServer->test();
        echo arrayToJson($result_array);
    }

    //http://localhost/phpserver/public/index.php/race/user/test
    public function test(){
        echo 'dxp';
    }
}