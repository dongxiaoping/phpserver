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

use app\race\model\table\User;
use think\Db;

class UserOP
{
    public function __construct()
    {

    }

    //0表示减 1表示加
    public function mod_cash_by_user_id($id, $cash, $type)
    {
        $item = $this->get($id);
        if ($item) {
            $new_cash = $type ? ($item["diamond"] + $cash) : ($item["diamond"] - $cash);
            Db::query("update user set diamond=" . $new_cash . " where id=" . $id);
            return $new_cash;
        } else {
            return null;
        }
    }

    /////////////////
    /* $info ["category_name"=>$name,......] 除主键之外的表字段信息集合
 * */
    public function insert($info)
    {
        $table = new User();
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
        $table = new User();
        $isOk = $table->insertAll($list);
        if ($isOk) {
            return true;
        } else {
            return false;
        }
    }


    public function del($id)
    {
        $table = new User();
        return $table->where("id", $id)->delete();
    }

    /* $info ["category_name"=>$name,......] 除主键之外的表字段信息集合
     * */
    public function mod($id, $info)
    {
        $table = new User();
        $table->save($info, ["id" => $id]);
    }

    public function get($id)
    {
        $table = new User();
        return $table:: get($id);//查询一个数据
    }

    public function getListByOneColumn($tag, $val)
    {
        $table = new User();
        $list = $table->where($tag, $val)->select();
        return $list;
    }

    public function getAll()
    {
        $table = new User();
        $list = $table->select();
        return $list;
    }
}