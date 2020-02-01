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
        $diamond = 0;
        $rate = 1;
        if ($roomPay == $ROOM_PAY["AA"]) { //代开
            switch ($playCount) {
                case 15:
                    $diamond = 3;
                    break;
                case 20:
                    $diamond = 3;
                    break;
                case 25:
                    $diamond = 4;
                    break;
                default:
                    break;
            }
        } else {
            switch ($playCount) {
                case 15:
                    $diamond = 20;
                    break;
                case 20:
                    $diamond = 25;
                    break;
                case 25:
                    $diamond = 30;
                    break;
                default:
                    break;
            }
        }

        switch ($costLimit) {
            case 200:
                $rate = 1;
                break;
            case 300:
                $rate = 1.5;
                break;
            case 500:
                $rate = 2;
                break;
            default:
                break;
        }
        return $diamond * $rate;
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