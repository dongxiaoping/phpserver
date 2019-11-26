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

    /////////////////
    /* $info ["category_name"=>$name,......] 除主键之外的表字段信息集合
 * */
    public function insert($info)
    {
        $table = new Race();
        $table->data($info);
        $isOk = $table->save();
        if ($isOk) {
            return $table->id;
        } else {
            return false;
        }
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