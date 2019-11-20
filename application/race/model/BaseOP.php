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

class BaseOP
{
    public function __construct($table) {
        $this->table = $table;
    }

    /* $info ["category_name"=>$name,......] 除主键之外的表字段信息集合
     * */
    public function insert($info){
        $this->table->data($info);
        $isOk = $this->table->save();
        if($isOk){
            return $this->table->id;
        }else{
            return false;
        }
    }

    public function insertAll($list){
        $isOk = $this->table->insertAll($list);
        if($isOk){
            return true;
        }else{
            return false;
        }
    }


    public function del($id){
       return $this->table->where("id",$id)->delete();
    }

   /* $info ["category_name"=>$name,......] 除主键之外的表字段信息集合
    * */
    public function mod($id,$info){
        $this->table->save($info,["id"=>$id]);
    }

    public function get($id){
        return $this->table->where("id",$id)->find(); //查询一个数据
    }

    public function getListByOneColumn($tag, $val){
        $list = $this->table->where($tag, $val)->select();
        return $list;
    }

    public function getAll(){
        $list = $this->table->select();
        return $list;
    }

}