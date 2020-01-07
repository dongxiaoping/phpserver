<?php


namespace app\race\model;


use app\race\model\table\CostRecord;

class CostRecordOP
{
    public function insert($info)
    {
        $table = new CostRecord();
        $table->data($info);
        $isOk = $table->save();
        if ($isOk) {
            return $table->id;
        } else {
            return false;
        }
    }
}