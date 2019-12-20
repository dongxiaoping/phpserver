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


use app\race\service\BetRecordServer;

class Betrecord
{
    public function __construct()
    {
        $this->BetRecordServer = new BetRecordServer();
    }

    public function to_bet()
    {
        header('Access-Control-Allow-Origin: *');
        $content = file_get_contents("php://input");
        $content = (string)$content;
        $content = json_decode($content, true);
        if ($content["userId"] && $content["roomId"] && $content["raceNum"] && $content["betLocation"] && $content["betVal"]) {
            $result_array = $this->BetRecordServer->to_bet($content["userId"], $content["roomId"], $content["raceNum"], $content["betLocation"], $content["betVal"]);
            echo arrayToJson($result_array);
        } else {
            echo getJsonStringByParam(0, "param_error", "");
        }
    }

    public function cancel_bet_by_location()
    {
        header('Access-Control-Allow-Origin: *');
        if ($_GET["roomId"] && $_GET["userId"] && $_GET["betLocation"]) { //$_GET["raceNum"] 注意为0的情况
            $roomId = $_GET["roomId"];
            $raceNum = $_GET["raceNum"];
            $userId = $_GET["userId"];
            $betLocation = $_GET["betLocation"];
            $result_array = $this->BetRecordServer->cancel_bet_by_location($roomId, $raceNum, $userId, $betLocation);
            echo arrayToJson($result_array);
        } else {
            echo getJsonStringByParam(0, "param_error", "");
        }
    }

}