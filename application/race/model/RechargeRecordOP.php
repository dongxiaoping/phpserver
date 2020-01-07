<?php


namespace app\race\model;


use app\race\model\table\RechargeRecord;

class RechargeRecordOP
{
    public function insert($info)
    {
        $table = new RechargeRecord();
        $table->data($info);
        $isOk = $table->save();
        if ($isOk) {
            return $table->id;
        } else {
            return false;
        }
    }
}