<?php
// +----------------------------------------------------------------------
// | Copyright (php), BestTV.
// +----------------------------------------------------------------------
// | Author: karl.dong
// +----------------------------------------------------------------------
// | Date：2019/11/15
// +----------------------------------------------------------------------
// | Description: 
// +----------------------------------------------------------------------
namespace app\race\service\base;
class RoomBase
{

    public function getRoomCostValue($playCount, $costLimit)
    {
        $cost = 10;
        if ($playCount <= 20) { //次数
            $cost = $cost + 2;
        } else if ($playCount <= 30) {
            $cost = $cost + 3;
        } else { //40
            $cost = $cost + 4;
        }



        if ($costLimit <= 40) { //下注值
            $cost = $cost * 2;
        } else if ($playCount <= 80) {
            $cost = $cost * 2;
        } else if ($playCount <= 100) {
            $cost = $cost * 3;
        } else {//200
            $cost = $cost * 3;
        }
        return $cost;
    }
}