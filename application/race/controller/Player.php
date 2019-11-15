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


use app\race\service\PlayerServer;

class Player
{
    public function __construct() {
        $this->PlayerServer = new PlayerServer();
    }

    public function get_members_by_room_id(){
        header("Access-Control-Allow-Origin: *");
        if(isset($_GET["id"])){
            $id = $_GET["id"];
            $result_array = $this->PlayerServer->get_members_by_room_id($id);
            echo arrayToJson($result_array);
        }else{
            echo getJsonStringByParam(0,"param_error","");
        }
    }
}