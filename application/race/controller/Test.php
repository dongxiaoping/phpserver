<?php


namespace app\race\controller;


use app\race\service\CostServer;
use app\race\service\RechargeServer;

class Test
{
    public function __construct()
    {
        $this->ServerOb = new CostServer();
        $this->ServerOb2 = new RechargeServer();
    }

    public function test()
    {
       $this->ServerOb->add_cost_record('2','3',10);
      //  $this->ServerOb2->add_recharge_record('2',15,1);
        var_dump('welcome');
    }
}