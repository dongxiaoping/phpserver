<?php
// +----------------------------------------------------------------------
// | Copyright (php), BestTV.
// +----------------------------------------------------------------------
// | Author: karl.dong
// +----------------------------------------------------------------------
// | Date：2018/8/16
// +----------------------------------------------------------------------
// | Description: 
// +----------------------------------------------------------------------

namespace app\race\model;

use app\race\model\table\Race;
use think\Db;
use think\Log;

class RaceOP
{
    public function __construct()
    {

    }

    public function change_race_state($room_id, $race_num, $state)
    {
        Db::query("update race set playState=" . $state . " where raceNum=" . $race_num . " and roomId=" . $room_id);
    }

    public function change_race_landlord($room_id, $running_race_num, $landlordId)
    {
        Db::query("update race set landlordId=" . $landlordId . " where raceNum=" . $running_race_num . " and roomId=" . $room_id);
    }

    public function get_race_by_num($room_id, $race_num)
    {
        $table = new Race();
        return $table->where("roomId", $room_id)->where("raceNum", $race_num)->find();
    }

    public function get_race_result($room_id, $race_num)
    {
        $COMPARE_DX_RE = json_decode(COMPARE_DX_RE, true);
        $list = Db::query("select a.roomId as roomId,a.userId,a.sky,a.land,a.middle,a.skyCorner,a.landCorner,
        a.bridg,a.raceNum,b.nick,b.icon from bet_record a INNER JOIN user b on a.userId = b.id where a.raceNum=" . $race_num . " and a.roomId = " . $room_id);
        $table = new Race();
        $race_item = $table->where("roomId", $room_id)->where("raceNum", $race_num)->find();
        $skyResult = $race_item['skyResult'];
        $middleResult = $race_item['middleResult'];
        $landResult = $race_item['landResult'];
        $skyCornerResult = $race_item['skyCornerResult'];
        $landCornerResult = $race_item['landCornerResult'];
        $bridgResult = $race_item['bridgResult'];
        $landlord_score = 0;
        $landlord_id = $race_item['landlordId'];
        for ($i = 0; $i < count($list); $i++) {
            $list[$i]['score'] = 0;
            if ($skyResult === $COMPARE_DX_RE['BIG']) {
                $list[$i]['score'] += $list[$i]['sky'];
            } else if ($skyResult === $COMPARE_DX_RE['SMALL']) {
                $list[$i]['score'] -= $list[$i]['sky'];
            }

            if ($middleResult === $COMPARE_DX_RE['BIG']) {
                $list[$i]['score'] += $list[$i]['middle'];
            } else if ($middleResult === $COMPARE_DX_RE['SMALL']) {
                $list[$i]['score'] -= $list[$i]['middle'];
            }

            if ($landResult === $COMPARE_DX_RE['BIG']) {
                $list[$i]['score'] += $list[$i]['land'];
            } else if ($landResult === $COMPARE_DX_RE['SMALL']) {
                $list[$i]['score'] -= $list[$i]['land'];
            }

            if ($skyCornerResult === $COMPARE_DX_RE['BIG']) {
                $list[$i]['score'] += $list[$i]['skyCorner'];
            } else if ($skyCornerResult === $COMPARE_DX_RE['SMALL']) {
                $list[$i]['score'] -= $list[$i]['skyCorner'];
            }

            if ($landCornerResult === $COMPARE_DX_RE['BIG']) {
                $list[$i]['score'] += $list[$i]['landCorner'];
            } else if ($landCornerResult === $COMPARE_DX_RE['SMALL']) {
                $list[$i]['score'] -= $list[$i]['landCorner'];
            }

            if ($bridgResult === $COMPARE_DX_RE['BIG']) {
                $list[$i]['score'] += $list[$i]['bridg'];
            } else if ($bridgResult === $COMPARE_DX_RE['SMALL']) {
                $list[$i]['score'] -= $list[$i]['bridg'];
            }
            $landlord_score -= $list[$i]['score'];
        }
        $landlord_user_info = (new UserOP())->get($landlord_id);
        $landlordItem = array('userId' => $landlord_id, 'raceNum' => $race_item['raceNum']
        , 'nick' => $landlord_user_info['nick'], 'icon' => $landlord_user_info['icon'], 'score' => $landlord_score);
        array_push($list, $landlordItem);
        return $list;
    }

    public function insert($info)
    {
        $table = new Race();
        $result = $table->insertGetId($info);
        Log::record('插入比赛信息:'.$result);
        return $result;
    }

    public function insertAll($list)
    {
        $table = new Race();
        $isOk = $table->insertAll($list);
        if ($isOk) {
            return true;
        } else {
            return false;
        }
    }


    public function del($id)
    {
        $table = new Race();
        return $table->where("id", $id)->delete();
    }

    /* $info ["category_name"=>$name,......] 除主键之外的表字段信息集合
     * */
    public function mod($id, $info)
    {
        $table = new Race();
        $table->save($info, ["id" => $id]);
    }

    public function get($id)
    {
        $table = new Race();
        return $table->where("id", $id)->find(); //查询一个数据
    }

    public function getListByOneColumn($tag, $val)
    {
        $table = new Race();
        $list = $table->where($tag, $val)->select();
        return $list;
    }

    public function getAll()
    {
        $table = new Race();
        $list = $table->select();
        return $list;
    }
}