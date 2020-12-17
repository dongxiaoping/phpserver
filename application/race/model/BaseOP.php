<?php
// +----------------------------------------------------------------------
// | Copyright (php), BestTV.
// +----------------------------------------------------------------------
// | Author: karl.dong
// +----------------------------------------------------------------------
// | Date：2018/6/28
// +----------------------------------------------------------------------
// | Description: 
// +----------------------------------------------------------------------
namespace app\race\model;

use think\Log;

class BaseOP
{
    private $tableOb;

    public function __construct($table) //有问题 暂时不能继承使用，传参传不过来
    {
        $this->tableOb = $table;
    }

    public function insert($info)
    {
        $table = new $this->tableOb();
        $result = $table->insertGetId($info);
        Log::info('插入操作:'.$result);
        return $result;
    }

    public function insertAll($list)
    {
        $table = new $this->tableOb();
        $isOk = $table->insertAll($list);
        if ($isOk) {
            return true;
        } else {
            return false;
        }
    }


    public function del($id)
    {
        $table = new $this->tableOb();
        return $table->where("id", $id)->delete();
    }

    /* $info ["category_name"=>$name,......] 除主键之外的表字段信息集合
     * */
    public function mod($id, $info)
    {
        $table = new $this->tableOb();
        $table->save($info, ["id" => $id]);
    }

    public function get($id)
    {
        $table = new $this->tableOb();
        return $table->where("id", $id)->find(); //查询一个数据
    }

    public function getListByOneColumn($tag, $val)
    {
        $table = new $this->tableOb();
        $list = $table->where($tag, $val)->select();
        return $list;
    }

    public function getAll()
    {
        $table = new $this->tableOb();
        $list = $table->select();
        return $list;
    }

}