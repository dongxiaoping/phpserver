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

class BetRecord
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
        if ($content["userId"] && $content["raceId"] && $content["betVal"] && $content["betLocation"]) {
            $result_array = $this->BetRecordServer->to_bet($content);
            echo arrayToJson($result_array);
        } else {
            echo getJsonStringByParam(0, "param_error", "");
        }
    }
}