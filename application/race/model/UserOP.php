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

    public function cash_by_user_id($id, $cash)
    {
        $item = $this->get($id);
        if ($item) {
            $new_cash = $item["diamond"] - $cash;
            Db::query("update user set diamond=" . $new_cash . " where id=" . $id);
            return true;
        } else {
            return false;
        }
    }
}