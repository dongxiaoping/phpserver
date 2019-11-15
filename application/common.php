<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
// 应用公共文件
function test()
{
    echo "common test";
}


function getJsonStringByParam($status, $message, $data)
{
    $return_array = getInterFaceArray($status, $message, $data);
    return arrayToJson($return_array);
}

function getInterFaceArray($status, $message, $data)
{
    $value = array("status" => 0, "message" => "not init", "data" => null);
    $value["status"] = $status;
    $value["message"] = $message;
    $value["data"] = $data;
    return $value;
}

function arrayToJson($info)
{
    return json_encode($info, JSON_UNESCAPED_UNICODE);
}

function getRandCode()
{
    $charts = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz0123456789";
    $max = strlen($charts) - 1;
    $noncestr = "";
    for ($i = 0; $i < 16; $i++) {
        $noncestr .= $charts[mt_rand(0, $max)];
    }
    return $noncestr;
}


/* 判断是get请求还是post请求，是post请求返回true，都在false
 * */
function getIsPostRequest()
{
    return isset($_SERVER['REQUEST_METHOD']) && !strcasecmp($_SERVER['REQUEST_METHOD'], 'POST');
}

/* 获取日期字符串
 * */
function getDateString()
{
    $year = date("Y");
    $moth = date("m");
    $day = date("d");
    $hour = date("H");
    $minute = date("i");
    $second = date("s");
    return $year . $moth . $day . $hour . $minute . $second;
}

function lssLog($flag, $message)
{
    try {
        $log_error_reporting = "error|warn|info|debug";
        $log_file = __DIR__ . "/logs/log";

        $is_exist = strpos($log_error_reporting, $flag);
        if (FALSE === $is_exist) {
            return;
        }
        if (is_array($message)) {
            $message = implode(",", $message);
        }
        $date = $flag . " " . @date("Y-m-d H:i:s") . " : " . $message . "\n";
        $year = @date("Y");
        $moth = @date("m");
        $day = @date("d");
        $log_file = $log_file . "." . $year . "." . $moth . "." . $day;
        if (($fp = fopen($log_file, "a+")) === FALSE) {
            echo("create log file " . $log_file . "fail!");
            exit();
        }
        $is_right = fwrite($fp, $date);
        if ($is_right == FALSE) {
            echo "write log fail!";
        }
        fclose($fp);
    } catch (Exception $err) {
        echo $err->getMessage();
    }
}

/**
 * XML转数组
 * @param string $xmlstring XML字符串
 *
 * @return array XML数组
 */
function xmlToArray($xmlstring)
{
    $object = simplexml_load_string($xmlstring, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
    return @json_decode(@json_encode($object), 1);
}

function generate_password($length = 18)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $password;
}
