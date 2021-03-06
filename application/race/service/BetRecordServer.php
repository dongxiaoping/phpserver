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
use think\Log;

class BetRecordServer
{
    public function __construct()
    {
        $this->BetRecordOP = new  BetRecordOP();
        $this->raceServer = new RaceServer();
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
            if($betVal < 0){
                $new_bet_val = 0;
            }else{
                $new_bet_val = $the_record[$betLocation] + $betVal;
            }
            $id = $the_record['id'];
            $updateResult =  $this->BetRecordOP->update_bet_val($id, $betLocation, $new_bet_val);
            if($updateResult){
                return getInterFaceArray(1, "success", '');
            }
            return getInterFaceArray(0, "update_fail", '');
        }

        //记录不存在
        $item = [
            'roomId' => $roomId,
            'raceNum' => $raceNum,
            'userId' => $userId,
            $betLocation => $betVal,
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

    //指定用户删除指定场次、指定位置的下注（前提是下注没有结束）
    public function cancel_bet_by_location($roomId, $raceNum, $userId, $betLocation)
    {
        $race_info = $this->raceServer->get_race_by_num($roomId, $raceNum);
        if (!$race_info) {
            return getInterFaceArray(0, "no_race", "");
        }
        $RACE_PLAY_STATE = json_decode(RACE_PLAY_STATE, true);
        $race_play_state = $race_info["playState"];
        if ($race_play_state != $RACE_PLAY_STATE["BET"]) {
            return getInterFaceArray(0, "not_bet_state", "");
        }
        $the_record = $this->BetRecordOP->get_the_record($userId, $roomId, $raceNum);
        if (!$the_record) {
            return getInterFaceArray(0, "not_bet", "");
        }
        if ($the_record[$betLocation] <= 0) {
            return getInterFaceArray(0, "not_bet", "");
        }
        $id = $the_record['id'];
        $updateResult = $this->BetRecordOP->update_bet_val($id, $betLocation, 0);
        if($updateResult){
            Log::record('del bet success', 'info');
            return getInterFaceArray(1, "success", "");
        }else{
            Log::record('del bet fail', 'error');
            return getInterFaceArray(0, "update_fail", "");
        }
    }

}