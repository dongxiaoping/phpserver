<?php
// +----------------------------------------------------------------------
// | Copyright (php), BestTV.
// +----------------------------------------------------------------------
// | Author: karl.dong
// +----------------------------------------------------------------------
// | Date：2019/11/14
// +----------------------------------------------------------------------
// | Description: 
// +----------------------------------------------------------------------

namespace app\race\service;

use app\race\model\BetRecordOP;
use app\race\model\RaceOP;

class BetRecordServer
{
    public function __construct()
    {
        $this->BetRecordOP = new  BetRecordOP();
        $this->RaceOP = new RaceOP();
    }

    public function get_bet_record_by_room_id($id)
    {
        $list = $this->BetRecordOP->getListByOneColumn('roomId', $id);
        return getInterFaceArray(1, "success", $list);
    }

    public function to_bet($userId, $roomId, $raceNum, $betLocation, $betVal)
    {
        $the_record = $this->BetRecordOP->get_the_record($userId, $roomId, $raceNum);
        if ($the_record) { //存在记录
            $new_bet_val = $the_record[$betLocation] + $betVal;
            $id = $the_record['id'];
            $this->BetRecordOP->update_bet_val($id, $betLocation, $new_bet_val);
            return getInterFaceArray(1, "success", '');
        }

        //记录不存在
        $item = [
            'roomId' => $roomId,
            'raceNum' => $raceNum,
            'userId' => $userId,
            $betLocation=>$betVal,
            'creatTime' => date("Y-m-d H:i:s"),
            'modTime' => date("Y-m-d H:i:s")
        ];
        $info = $this->BetRecordOP->insert($item);
        if ($info) {
            return getInterFaceArray(1, "success", '');
        }
        return getInterFaceArray(0, "fail", '');
    }

    public function getWinResult($race_item, $betLocation)
    {
        $BET_LOCATION = json_decode(BET_LOCATION, true);
        $COMPARE_DX_RE = json_decode(COMPARE_DX_RE, true);
        $isWin = $COMPARE_DX_RE['EQ'];
        if ($betLocation === $BET_LOCATION['SKY']) {
            $isWin = $race_item['skyResult'];
        } else if ($betLocation === $BET_LOCATION['LAND']) {
            $isWin = $race_item['landResult'];
        }
        if ($betLocation === $BET_LOCATION['MIDDLE']) {
            $isWin = $race_item['middleResult'];
        }
        if ($betLocation === $BET_LOCATION['BRIDG']) {
            $isWin = $race_item['bridgResult'];
        }
        if ($betLocation === $BET_LOCATION['SKY_CORNER']) {
            $isWin = $race_item['skyCornerResult'];
        }
        if ($betLocation === $BET_LOCATION['LAND_CORNER']) {
            $isWin = $race_item['landCornerResult'];
        }
        return $isWin;
    }

}