<?php
// +----------------------------------------------------------------------
// | Copyright (php), BestTV.
// +----------------------------------------------------------------------
// | Author: karl.dong
// +----------------------------------------------------------------------
// | Date：2019/11/14
// +----------------------------------------------------------------------
// | Description: 
// +----------------------------------------------------------------------

namespace app\race\controller;


use app\race\service\RaceServer;

class Race
{
    public function __construct() {
        $this->RaceServer = new RaceServer();
    }

    //http://localhost/phpserver/public/index.php/race/race/test
    public function test()
    {
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Headers', 'Origin, Content-Type, cache-control,postman-token,Cookie, Accept');
        $room_id = 1;
        $race_num = 2;
        $info = $this->RaceServer->get_race_result($room_id, $race_num);
        var_dump($info);
    }
}