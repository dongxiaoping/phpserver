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

use app\race\model\table\Room;
use think\Db;
use think\Log;

class RoomOP
{
    public function __construct()
    {

    }

    public function change_room_state($room_id, $state)
    {
        $table = new Room();
        $table->where('id', $room_id)
            ->update([
                'roomState'  => $state,
                'modTime' => date("Y-m-d H:i:s"),
            ]);
    }

    public function change_on_race($room_id, $on_race_num)
    {
        Db::query("update room set oningRaceNum=" . $on_race_num . " where id=" . $room_id);
    }

    public function get_not_begin_room_list_by_user_id($id)
    {
        $table = new Room();
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        $list = $table->where("creatUserId", $id)->where("roomState", $ROOM_STATE['OPEN'])->select();
        return $list;
    }

    public function get_on_room_list_by_user_id($id)
    {
        $table = new Room();
        $list = $table->where("creatUserId", $id)->where("roomState", "<=", 2)
            ->order('roomState asc,creatTime desc')->select();
        $real_list = [];
        $now_time = time();
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        for ($j = 0; $j < count((array)$list); $j++) {
            $mod_time = strtotime($list[$j]["modTime"]);
            if (($now_time - $mod_time > 3600) && ($list[$j]["roomState"] == $ROOM_STATE["PLAYING"])) {
                $room_id = $list[$j]["id"];
                Log::write("当前时间戳:".$now_time.",最后修改时间戳：".$mod_time.",房间运行超时，强制关闭，房间号：" . $room_id, 'error');
                Log::write("当前时间：".date("Y-m-d H:i:s").",最后修改时间:".$list[$j]["modTime"], 'error');
                $this->change_room_state($room_id, $ROOM_STATE["CLOSE"]);
            } else {
                $real_list[] = $list[$j];
            }
        }
        return $real_list;
    }

    /////////////////
    /* $info ["category_name"=>$name,......] 除主键之外的表字段信息集合
 * */
    public function insert($info)
    {
        $table = new Room();
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
        $table = new Room();
        $isOk = $table->insertAll($list);
        if ($isOk) {
            return true;
        } else {
            return false;
        }
    }


    public function del($id)
    {
        $table = new Room();
        return $table->where("id", $id)->delete();
    }

    /* $info ["category_name"=>$name,......] 除主键之外的表字段信息集合
     * */
    public function mod($id, $info)
    {
        $table = new Room();
        $table->save($info, ["id" => $id]);
    }

    public function get($id)
    {
        $table = new Room();
        return $table->where("id", $id)->find(); //查询一个数据
    }

    public function getListByOneColumn($tag, $val)
    {
        $table = new Room();
        $list = $table->where($tag, $val)->select();
        return $list;
    }

    public function getAll()
    {
        $table = new Room();
        $list = $table->select();
        return $list;
    }
}