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

class UserOP extends BaseOP
{
    public function __construct()
    {
        $this->user = new User();
        parent::__construct($this->user);
    }

    //0表示减 1表示加
    public function mod_cash_by_user_id($id, $cash, $type)
    {
        $item = $this->get($id);
        if ($item) {
            $new_cash = $type?($item["diamond"] + $cash):($item["diamond"] - $cash);
            Db::query("update user set diamond=" . $new_cash . " where id=" . $id);
            return true;
        } else {
            return false;
        }
    }
}