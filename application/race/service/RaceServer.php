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

use app\race\model\RaceOP;
use app\race\service\base\RaceBase;

class RaceServer extends RaceBase
{
    public function __construct()
    {
        $this->RaceOP = new  RaceOP();
    }

    /* 给指定的房间创建指定数量的比赛场次信息
     * @$room_id   房间ID
     * @$race_count 比赛场次值
     * */
    public function createRacesByRoom($room_id, $race_count)
    {
        $list = [];
        $RACE_PLAY_STATE = json_decode(RACE_PLAY_STATE, true);
        $majong_list = $this->get_mahjonList_by_race_count($race_count);
        for ($i = 0; $i < $race_count; $i++) {
            $majongs = array_slice($majong_list, $i * 8, 8);
            $points = ['one' => rand(1, 6), 'two' => rand(1, 6)];
            $majongResult = [
                'landlord' => ['one' => $majongs[0], 'two' => $majongs[1]],
                'sky' => ['one' => $majongs[2], 'two' => $majongs[3]],
                'middle' => ['one' => $majongs[4], 'two' => $majongs[5]],
                'land' => ['one' => $majongs[6], 'two' => $majongs[7]]
            ];
            $locationResultDetail = $this->getLocationResultDetail($majongResult);
            $landlordScore = $majongResult['landlord'];
            $skyScore = $majongResult['sky'];
            $middleScore = $majongResult['middle'];
            $groundScore = $majongResult['land'];
            $item = [
                'roomId' => $room_id,
                'raceNum' => $i,
                'playState' => $RACE_PLAY_STATE["NOT_BEGIN"],
                'landlordScore' => json_encode($landlordScore),
                'skyScore' => json_encode($skyScore),
                'middleScore' => json_encode($middleScore),
                'landScore' => json_encode($groundScore),
                'landlordId' => null,
                'points' => json_encode($points),
                'skyResult' => $locationResultDetail['sky'],
                'middleResult' => $locationResultDetail['middle'],
                'landResult' => $locationResultDetail['land'],
                'skyCornerResult' => $locationResultDetail['sky_corner'],
                'landCornerResult' => $locationResultDetail['land_corner'],
                'bridgResult' => $locationResultDetail['bridg'],
                'creatTime' => date("Y-m-d H:i:s"),
                'modTime' => date("Y-m-d H:i:s")
            ];
            $list[] = $item;
        }
        $isOk = $this->RaceOP->insertAll($list);
        return $isOk;
    }

    public function getRacesByRoomId($id)
    {
        $list = $this->RaceOP->getListByOneColumn('roomId', $id);
        return getInterFaceArray(1, "success", $list);
    }

    public function change_race_state($room_id, $race_num, $state)
    {
        $this->RaceOP->change_race_state($room_id, $race_num, $state);
    }

    public function change_race_landlord($room_id, $running_race_num, $landlordId, $landlordLastCount)
    {
        for ($i = 0; $i < $landlordLastCount; $i++) {
            $this->RaceOP->change_race_landlord($room_id, $running_race_num + $i, $landlordId);
        }

    }

    public function get_race_result($room_id, $race_num)
    {
        return $this->RaceOP->get_race_result($room_id, $race_num);
    }
}