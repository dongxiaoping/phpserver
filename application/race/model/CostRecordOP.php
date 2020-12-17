<?php


namespace app\race\model;


use app\race\model\table\CostRecord;
use think\Log;

class CostRecordOP
{
    public function insert($info)
    {
        $table = new CostRecord();
        $result = $table->insertGetId($info);
        Log::record('插入CostRecordOP操作:'.$result);
        return $result;
    }
}