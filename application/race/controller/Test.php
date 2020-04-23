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
        header("Access-Control-Allow-Origin: *");
        $up_dir = './upload/';//存放在当前目录的upload文件夹下
        $baseData = trim($_POST['file']);
        if(!file_exists($up_dir)){
            mkdir($up_dir,0777);
        }
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $baseData, $result)) {
            $type = $result[2];
            if(in_array($type,array('pjpeg','jpeg','jpg','gif','bmp','png'))){
                $new_file = $up_dir.date('YmdHis_').'.'.$type;
                if(file_put_contents($new_file, base64_decode(str_replace($result[1], '', $baseData)))){
                    $img_path = str_replace('../../..', '', $new_file);
                    echo '图片上传成功</br>![](' .$img_path. ')';
                }else{
                    echo '图片上传失败</br>';

                }
                echo '文件正确';
            }else{
                echo '文件错误';
            }
            echo $baseData;
        }else{
           echo '数据异常';
        }
    }
}