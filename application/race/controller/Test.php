<?php


namespace app\race\controller;


use app\race\service\CostServer;
use app\race\service\RechargeServer;
use app\race\service\socket\SocketServer;
use think\Log;
use Grafika\Grafika;
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
        $editor = Grafika::createEditor();
        $up_dir = './upload/20201229152624.jpeg';
        $editor->open( $image, $up_dir );
        $editor->resizeExact( $image, 200, 200 );
        $editor->save( $image, './upload/33333.jpeg', null, 90 );

    }
}