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

    public function  get_room_info_by_id(){
        header("Access-Control-Allow-Origin: *");
        if(isset($_GET["id"])){
            $id = $_GET["id"];
            $result_array = $this->RoomServer->get_room_info_by_id($id);
            echo arrayToJson($result_array);
        }else{
            echo getJsonStringByParam(0,"param_error","");
        }
    }

    // http://localhost/phpserver/public/index.php/race/room/create_room
    public function  create_room(){
//        header('Access-Control-Allow-Origin: *');
//        $content = file_get_contents("php://input");
//        $content = (string)$content;
//        $content = json_decode($content,true);
//        if($content["creatUserId"] && $content["playCount"]&& $content["memberLimit"]&& $content["playMode"]&& $content["costLimit"]){
//            $result_array = $this->AddressServer->add_address($content);
//            echo arrayToJson($result_array);
//        }else{
//            echo getJsonStringByParam(0,"error","");
//        }
        $content = [
            "creatUserId" => 1,
            "memberLimit"=>10,
            "playCount"=>4,
            "costLimit"=>100
        ];
        $result_array = $this->RoomServer->create_room($content);
        echo arrayToJson($result_array);
    }

}