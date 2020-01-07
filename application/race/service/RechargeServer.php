<?php


namespace app\race\service;


use app\race\model\RechargeRecordOP;

class RechargeServer
{
    public function __construct()
    {
        $this->RechargeRecordOP = new  RechargeRecordOP();
    }

    public function add_recharge_record($userId, $cost, $platform_type)
    {
      //  $RECHARGE_PLATFORM = json_decode(RECHARGE_PLATFORM, true);
        $item = [
            'platform' => $platform_type,
            'cost' => $cost,
            'userId' => $userId,
            'creatTime' => date("Y-m-d H:i:s"),
            'modTime' => date("Y-m-d H:i:s")
        ];
        $info = $this->RechargeRecordOP->insert($item);
    }
}