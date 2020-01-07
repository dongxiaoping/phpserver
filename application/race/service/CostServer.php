<?php


namespace app\race\service;
use app\race\model\CostRecordOP;

class CostServer
{
    public function __construct()
    {
        $this->CostRecordOP = new  CostRecordOP();
    }

    public function add_cost_record($userId, $roomId, $cost)
    {
        $item = [
            'roomId' => $roomId,
            'cost' => $cost,
            'userId' => $userId,
            'creatTime' => date("Y-m-d H:i:s"),
            'modTime' => date("Y-m-d H:i:s")
        ];
        $info = $this->CostRecordOP->insert($item);
    }
}