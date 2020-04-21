<?php


namespace app\race\controller;


use app\race\service\CostServer;
use app\race\service\RechargeServer;
use app\race\service\socket\SocketServer;
use think\Log;

class Test
{
    public function __construct()
    {
        $this->ServerOb = new CostServer();
        $this->ServerOb2 = new RechargeServer();
    }
//http://127.0.0.1/phpserver/public/index.php/race/test/test
    public function test()
    {
        $this->ServerOb = new SocketServer();
        $cc = $this->ServerOb->get_member_info_in_the_room(3348,1067);
        var_dump($cc);
//       $this->ServerOb->add_cost_record('2','3',10);
//      //  $this->ServerOb2->add_recharge_record('2',15,1);
//        var_dump('welcome');
//        $c = 3;
//        try{
//        }catch (Exception $e){
//            Log::write($e->getMessage(), 'error');
//        }
    }
}