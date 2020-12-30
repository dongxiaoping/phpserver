<?php


namespace app\race\controller;


use app\race\service\CostServer;
use app\race\service\RechargeServer;
use app\race\service\socket\SocketServer;
use think\Log;
use Grafika\Grafika;
use app\race\service\SmsManage;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

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
        $SmsManage = new SmsManage();
        $SmsManage->sendSmsNotice('13396080754');
    }

    public function send()
    {
        AlibabaCloud::accessKeyClient('LTAI4G3aM1en9eTpGu4mmEB3', 'XA1IF3FYbb79y7GEfnVQ6I1kyo7TXV')
            ->regionId('cn-hangzhou')
            ->asDefaultClient();

        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => "cn-hangzhou",
                        'PhoneNumbers' => "13396080754",
                        'SignName' => "悦源动",
                        'TemplateCode' => "SMS_207953490",
                        'TemplateParam' => "{content:3456,code:555}",
                    ],
                ])
                ->request();
            print_r($result->toArray());
        } catch (ClientException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        } catch (ServerException $e) {
            echo $e->getErrorMessage() . PHP_EOL;
        }
    }
}