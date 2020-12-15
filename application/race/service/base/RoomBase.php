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
        $createDiamondConfig = config('createDiamondConfig');
        $diamond = 0;
        $rate = 1;
        if ($roomPay == $ROOM_PAY["AA"]) { //代开
            switch ($playCount) {
                case $createDiamondConfig['totalRace']['one']['raceCount']:
                    $diamond = $createDiamondConfig['totalRace']['one']['aaDiamond'];
                    break;
                case $createDiamondConfig['totalRace']['two']['raceCount'];
                    $diamond = $createDiamondConfig['totalRace']['two']['aaDiamond'];
                    break;
                case $createDiamondConfig['totalRace']['three']['raceCount'];
                    $diamond = $createDiamondConfig['totalRace']['three']['aaDiamond'];
                    break;
                case $createDiamondConfig['totalRace']['four']['raceCount'];
                    $diamond = $createDiamondConfig['totalRace']['four']['aaDiamond'];
                    break;
                default:
                    break;
            }
        } else {
            switch ($playCount) {
                case $createDiamondConfig['totalRace']['one']['raceCount']:
                    $diamond = $createDiamondConfig['totalRace']['one']['daiKaiDiamond'];
                    break;
                case $createDiamondConfig['totalRace']['two']['raceCount'];
                    $diamond = $createDiamondConfig['totalRace']['two']['daiKaiDiamond'];
                    break;
                case $createDiamondConfig['totalRace']['three']['raceCount'];
                    $diamond = $createDiamondConfig['totalRace']['three']['daiKaiDiamond'];
                    break;
                case $createDiamondConfig['totalRace']['four']['raceCount'];
                    $diamond = $createDiamondConfig['totalRace']['four']['daiKaiDiamond'];
                    break;
                default:
                    break;
            }
        }

        if ($roomPay == $ROOM_PAY["AA"]) { //代开
            switch ($costLimit) {
                case $createDiamondConfig['betLimit']['one']['limitVal']:
                    $rate = $createDiamondConfig['betLimit']['one']['aaRate'];
                    break;
                case $createDiamondConfig['betLimit']['two']['limitVal']:
                    $rate = $createDiamondConfig['betLimit']['two']['aaRate'];
                    break;
                case $createDiamondConfig['betLimit']['three']['limitVal']:
                    $rate = $createDiamondConfig['betLimit']['three']['aaRate'];
                case $createDiamondConfig['betLimit']['four']['limitVal']:
                    $rate = $createDiamondConfig['betLimit']['four']['aaRate'];
                    break;
                default:
                    break;
            }
        } else {
            switch ($costLimit) {
                case $createDiamondConfig['betLimit']['one']['limitVal']:
                    $rate = $createDiamondConfig['betLimit']['one']['daiKaiRate'];
                    break;
                case $createDiamondConfig['betLimit']['two']['limitVal']:
                    $rate = $createDiamondConfig['betLimit']['two']['daiKaiRate'];
                    break;
                case $createDiamondConfig['betLimit']['three']['limitVal']:
                    $rate = $createDiamondConfig['betLimit']['three']['daiKaiRate'];
                    break;
                case $createDiamondConfig['betLimit']['four']['limitVal']:
                    $rate = $createDiamondConfig['betLimit']['four']['daiKaiRate'];
                    break;
                default:
                    break;
            }
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
        $BE_LANDLORD_WAY = json_decode(BE_LANDLORD_WAY, true);
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
        if ($content['playMode'] != $BE_LANDLORD_WAY["TURN"] && $content['playMode'] != $BE_LANDLORD_WAY["RAP"]) {
            return false;
        }
        if ($content['costLimit'] <= 0) {
            return false;
        }
        return true;
    }

}