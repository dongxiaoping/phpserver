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
        $cost = 0;
        if ($playCount <= 8) { //次数
            $cost = $cost + 4;
        } else if ($playCount <= 16) {
            $cost = $cost + 8;
        } else if ($playCount <= 24) {
            $cost = $cost + 12;
        } else { //32
            $cost = $cost + 16;
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