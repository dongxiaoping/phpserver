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

    public function getRoomCostValue($playCount, $costLimit, $roomPay)
    {
        $ROOM_PAY = json_decode(ROOM_PAY, true);
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
        if ($roomPay === $ROOM_PAY["CREATOR"]) {
            return 3 * $cost;
        }
        return $cost;
    }

    public function to_race_merge($list, $otherList)
    {
        for ($j = 0; $j < count($otherList); $j++) {
            $is_exist = false;
            for ($i = 0; $i < count($list); $i++) {
                if ($list[$i]['userId'] === $otherList[$j]['userId']) {
                    $is_exist = true;
                    $list[$i]['score'] += $otherList[$j]['score'];
                    break;
                }
            }
            if (!$is_exist) {
                array_push($list, $otherList[$j]);
            }
        }
        return $list;
    }

    public function check_vals_create_room($content)
    {
        $ROOM_PAY = json_decode(ROOM_PAY, true);
        if ($content['creatUserId'] === "") {
            return false;
        }
        if ($content['playCount'] <= 0) {
            return false;
        }
        if ($content['memberLimit'] <= 1) {
            return false;
        }
        if ($content['roomPay'] != $ROOM_PAY["AA"] && $content['roomPay'] != $ROOM_PAY["CREATOR"]) {
            return false;
        }
        if ($content['costLimit'] <= 0) {
            return false;
        }
        return true;
    }

}