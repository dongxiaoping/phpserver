<?php


namespace app\race\model;


use app\race\model\table\RechargeRecord;
use think\Log;

class RechargeRecordOP
{
    public function insert($info)
    {
        $table = new RechargeRecord();
        $result = $table->insertGetId($info);
        Log::info('插入充值操作:'.$result);
        return $result;
    }
}