<?php


namespace app\race\service;


use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class SmsManage
{
    public function __construct()
    {
        $this->setAccessKeyId("LTAI4G3aM1en9eTpGu4mmEB3");//AccessKeyId
        $this->setAccessKeySecret("XA1IF3FYbb79y7GEfnVQ6I1kyo7TXV"); //AccessKeySecret
        $this->setSignName("悦源动");//签名
        $this->setTemplateCode("SMS_207953490"); //短信模板号
    }

    public function sendSmsNotice($phoneNumber){
        $code = rand(1000,9999);
        $this->setTemplateParam($code);
        session("iphonecode",$phoneNumber.$code);//session存储手机号+验证码
        $this->send($phoneNumber);
    }

    private function send($phoneNumber)
    {
        AlibabaCloud::accessKeyClient($this->accessKeyId, 'XA1IF3FYbb79y7GEfnVQ6I1kyo7TXV')
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
                        'PhoneNumbers' => $phoneNumber,
                        'SignName' => $this->getSignName(),
                        'TemplateCode' => $this->getTemplateCode(),
                        'TemplateParam' => $this->getTemplateParam(),
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

    /**
     * @return string
     */
    private function getAccessKeyId(): string
    {
        return $this->accessKeyId;
    }

    /**
     * @param string $accessKeyId
     */
    private function setAccessKeyId(string $accessKeyId): void
    {
        $this->accessKeyId = $accessKeyId;
    }

    /**
     * @return string
     */
    private function getAccessKeySecret(): string
    {
        return $this->accessKeySecret;
    }

    /**
     * @param string $accessKeySecret
     */
    private function setAccessKeySecret(string $accessKeySecret): void
    {
        $this->accessKeySecret = $accessKeySecret;
    }

    /**
     * @return string
     */
    private function getSignName(): string
    {
        return $this->signName;
    }

    /**
     * @param string $signName
     */
    private function setSignName(string $signName): void
    {
        $this->signName = $signName;
    }

    /**
     * @return string
     */
    private function getTemplateCode(): string
    {
        return $this->templateCode;
    }

    /**
     * @param string $templateCode
     */
    private function setTemplateCode(string $templateCode): void
    {
        $this->templateCode = $templateCode;
    }

    /**
     * @return string
     */
    private function getTemplateParam(): string
    {
        return $this->templateParam;
    }

    /**
     * @param int $code 短信验证码
     */
    private function setTemplateParam(int $code): void
    {
        $this->templateParam = json_encode(array("code" => $code));
    }

}