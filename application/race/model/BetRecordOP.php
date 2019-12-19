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

use app\race\model\table\BetRecord;
use think\Db;

class BetRecordOP
{
    public function __construct()
    {
        /*        $this->tableOb =BetRecord；
                parent::__construct($this->bet_record);*/
    }

    public function get_the_record($userId, $roomId, $raceNum)
    {
        $table = new BetRecord();
        $map['userId'] = $userId;
        $map['roomId'] = $roomId;
        $map['raceNum'] = $raceNum;
        $info = $table->where($map)->find();
        return $info;
    }

    public function update_bet_val($id, $betLocation, $new_val)
    {
        Db::query("update bet_record set " . $betLocation . "=" . $new_val . " where id=" . $id);
        return true;
    }

    /////////////////
    /* $info ["category_name"=>$name,......] 除主键之外的表字段信息集合
 * */
    public function insert($info)
    {
        $table = new BetRecord();
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
        $table = new BetRecord();
        $isOk = $table->insertAll($list);
        if ($isOk) {
            return true;
        } else {
            return false;
        }
    }


    public function del($id)
    {
        $table = new BetRecord();
        return $table->where("id", $id)->delete();
    }

    /* $info ["category_name"=>$name,......] 除主键之外的表字段信息集合
     * */
    public function mod($id, $info)
    {
        $table = new BetRecord();
        $table->save($info, ["id" => $id]);
    }

    public function get($id)
    {
        $table = new BetRecord();
        return $table->where("id", $id)->find(); //查询一个数据
    }

    public function getListByOneColumn($tag, $val)
    {
        $table = new BetRecord();
        $list = $table->where($tag, $val)->select();
        return $list;
    }

    public function getAll()
    {
        $table = new BetRecord();
        $list = $table->select();
        return $list;
    }
}