<?php
// +----------------------------------------------------------------------
// | Copyright (php), BestTV.
// +----------------------------------------------------------------------
// | Author: karl.dong
// +----------------------------------------------------------------------
// | Date：2018/8/28
// +----------------------------------------------------------------------
// | Description: 
// +----------------------------------------------------------------------

namespace app\race\service;

use app\race\model\RoomOP;
use app\race\model\UserOP;

class UserServer
{
    public function __construct()
    {
        $this->UserOP = new  UserOP();
        $this->RoomOp = new  RoomOP();
        $this->RechargeServer = new RechargeServer();
        $this->CostServer = new CostServer();
    }

    public function get_user_info_by_id($id)
    {
        $item = $this->UserOP->get($id);
        if ($item === null) {
            return getInterFaceArray(0, "not_exist", "");
        }
        $info = getInterFaceArray(1, "success", $item);
        $info["config"] = config('roomGameConfig');
        return $info;
    }

    public function get_user_info_by_login_in($phone, $password)
    {
        $item = $this->UserOP->get_user_info_by_login_in($phone, $password);
        if ($item == null) {
            return getInterFaceArray(0, "手机号或者密码错误！", "");
        }
        $item["gameUrl"] = config('gameAgencyConfig')['gameUrl'];
        $info = getInterFaceArray(1, "登录成功！", $item);
        return $info;
    }

    public function loadUserIcon($baseData)
    {
        try {
            $up_dir = './upload/';//存放在当前目录的upload文件夹下
            if (!file_exists($up_dir)) {
                mkdir($up_dir, 0777);
            }
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $baseData, $result)) {
                $type = $result[2];
                if (in_array($type, array('pjpeg', 'jpeg', 'jpg', 'gif', 'bmp', 'png'))) {
                    $picName = date('YmdHis') . '.' . $type;
                    $path = $up_dir . $picName;
                    if (file_put_contents($path, base64_decode(str_replace($result[1], '', $baseData)))) {
                        $img_path = str_replace('../../..', '', $path);
                        //echo '图片上传成功</br>![](' .$img_path. ')';
                        return $picName;
                    } else {
                        //echo '图片上传失败</br>';
                        return null;
                    }
                } else {
                    //  echo '文件错误';
                    return null;
                }
            } else {
                //echo '数据异常';
                return null;
            }
        } catch (Exception $e) {
            return null;
        }
    }

    public function get_user_diamond($id)
    {
        $item = $this->UserOP->get($id);
        if ($item == null) {
            return getInterFaceArray(0, "not_exist", "");
        }
        $info = getInterFaceArray(1, "success", $item['diamond']);
        return $info;
    }

    public function create_visit_account()
    {
        $USER_TYPE = json_decode(USER_TYPE, true);
        $diamond_val = 0;
        $item = [
            'score' => 0,
            'diamond' => $diamond_val,
            'type' => $USER_TYPE['NORMAL_USER'],
            'nick' => '用户' . time(),
            'icon' => 'http://120.26.52.88/default_user_icon.png',
            'creatTime' => date("Y-m-d H:i:s"),
            'modTime' => date("Y-m-d H:i:s")
        ];
        $id = $this->UserOP->insert($item);
        if ($id) {
            $RECHARGE_PLATFORM = json_decode(RECHARGE_PLATFORM, true);
            $this->RechargeServer->add_recharge_record($id, $diamond_val, $RECHARGE_PLATFORM['WECHAT']);
            $info = $this->get_user_info_by_id($id);
            return $info;
        }
        return getInterFaceArray(0, "fail", '');
    }

    public function create_account($phone, $password, $nick, $iconName)
    {
        $USER_TYPE = json_decode(USER_TYPE, true);
        $user = $this->UserOP->get_user_info_by_phone($phone);
        if($user != null){
            return getInterFaceArray(0, "手机号码已存在！", '');
        }
        $item = [
            'score' => 0,
            'diamond' => 0,
            'type' => $USER_TYPE['NORMAL_USER'],
            'nick' => $nick,
            'icon' => $iconName,
            'phone' => $phone,
            'password' => $password,
            'creatTime' => date("Y-m-d H:i:s"),
            'modTime' => date("Y-m-d H:i:s")
        ];
        $id = $this->UserOP->insert($item);
        if ($id) {
            return getInterFaceArray(1, "账户创建成功！", array('id' => $id, 'gameUrl' => config('gameAgencyConfig')['gameUrl']));
        } else {
            return getInterFaceArray(0, "账户创建失败！", '');
        }
    }

    public function cost_diamond_in_room($roomId, $userId)
    {
        $item = $this->RoomOp->get($roomId);
        if (!$item) {
            return getInterFaceArray(0, "room_not_exist", "");
        }
        $ROOM_PAY = json_decode(ROOM_PAY, true);
        if ($userId != $item["creatUserId"] && $item["roomPay"] == $ROOM_PAY["CREATOR"]) {
            return getInterFaceArray(0, "not_need_diamond", "");
        }
        $cost_value = $item["roomFee"];
        $diamond_count = $this->UserOP->mod_cash_by_user_id($userId, $cost_value, 0);
        if ($diamond_count != null) {
            $this->CostServer->add_cost_record($userId, $roomId, $cost_value);
            return getInterFaceArray(1, "success", $diamond_count);
        }
        return getInterFaceArray(0, "cash_error", "");
    }

    public function recharge_diamond($userId, $diamondCount)
    {
        $val = $this->UserOP->mod_cash_by_user_id($userId, $diamondCount, 1);
        if ($val == null) {
            return getInterFaceArray(0, "fail", "");
        } else {
            return getInterFaceArray(1, "success", $val);
        }
    }
}