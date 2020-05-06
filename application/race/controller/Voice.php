<?php


namespace app\race\controller;
use think\Log;
use think\response\Json;

class Voice
{
    //http://127.0.0.1/phpserver/public/index.php/race/voice/uploadVoice
    public function uploadVoice()
    {
        header("Access-Control-Allow-Origin: *");
        try{
            $info =  file_get_contents('php://input');
            $list = json_decode($info,true);
            $file = $list["file"];
            $name = $this->loadVoiceFile($file);
            if($name == null){
                echo getJsonStringByParam(0, "上传异常", "");
            }else{
                echo getJsonStringByParam(1, "上传成功", $name);
            }
        } catch (Exception $e) {
            echo getJsonStringByParam(0, "上传失败", "");
        }
    }

    private function loadVoiceFile($baseData){
        try {
            $today = date( "Ymd ");
            $up_dir = './voice/'.$today."/";
            if (!file_exists($up_dir)) {
                mkdir($up_dir, 0777);
            }
            if (preg_match('/^(data:\s*audio\/(\w+);base64,)/', $baseData, $result)) {
                $type = $result[2];
                if (in_array($type, array('mp3'))) {
                    $picName = date('YmdHis') . '.' . $type;
                    $path = $up_dir . $picName;
                    if (file_put_contents($path, base64_decode(str_replace($result[1], '', $baseData)))) {
                        $img_path = str_replace('../../..', '', $path);
                        return $today."/".$picName;
                    } else {
                        return null;
                    }
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } catch (Exception $e) {
            return null;
        }

    }
}