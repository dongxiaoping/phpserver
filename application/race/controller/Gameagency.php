<?php
/**
 * Created by PhpStorm.
 * User: dongxiaoping-nb
 * Date: 2020/4/12
 * Time: 12:07
 */

namespace app\race\controller;


class Gameagency
{
    public function __construct()
    {

    }

    //http://localhost/phpserver/public/index.php/race/gameagency/get_config
    public function get_config()
    {
        header("Access-Control-Allow-Origin: *");
        echo getJsonStringByParam(1, "success", config('gameAgencyConfig'));
    }
}