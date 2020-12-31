<?php


namespace app\race\service\socket\action;


use app\race\service\socket\Room;
use app\race\service\socket\SocketData;
use app\race\service\socket\SocketServer;
use think\Log;

class LandlordSelectedAction
{
    private $socketServer;
    private $socketData;

    public function __construct(SocketServer $socketServer, SocketData $socketData)
    {
        $this->socketData = $socketData;
        $this->socketServer = $socketServer;
    }

    //抢庄选择当地主
    public function landlordSelected(Room $room, $raceNum, $landlordId)
    {
        Log::record('抢庄选择当庄');
        if(!$this->isRaceForLandlord($room, $raceNum)){
            Log::record('抢庄选择当庄异常', 'error');
            return false;
        }
        $room->rapLandlordUserList[] = $landlordId;
        $message = array('type' => 'memberWaitForLandlord', 'info' => array('userId' => $landlordId));//用户选择当庄
        $room->broadcastToAllMember($message);
    }

    //轮庄中选择当庄
    public function turnLandlordSelected(Room $room, $raceNum, $landlordId)
    {
        Log::record('轮庄选择当庄');
        if(!$this->isRaceForLandlord($room, $raceNum)){
            Log::record('轮庄选择当庄异常', 'error');
            return false;
        }
        if($room->getRaceLandlordId($raceNum) == null){
            Log::record('检查合理，用户可以轮流选择当庄');
            $room->setRaceLandlord($raceNum, $landlordId);
            $landlordLastCount = config('roomGameConfig.landlordLastCount');
            $this->socketServer->change_race_landlord($room->getRoomId(), $room->getRunningRaceNum(),
                $landlordId, $landlordLastCount);
            Log::record('将庄家信息存到数据库和虚拟房间中');
            $room->turnLandlordProcess($landlordId);
        }else{
            Log::record('该局已经有庄，用户的请求是异常行为');
            return false;
        }
    }

    //轮庄中放弃当庄
    public function turnLandlordPass(Room $room, $raceNum, $userId)
    {
        Log::record('轮庄放弃当庄');
        if(!$this->isRaceForLandlord($room, $raceNum)){
            Log::record('轮庄放弃当庄异常', 'error');
            return false;
        }
        if($room->getPlayerToTurnUserId() == $userId){
            Log::record('玩家放弃当庄，立即开启下一个轮询，玩家：'.$userId);
            $room->turnLandlordProcess($userId);
        }else{
            Log::record('当前轮询用户非该玩家：'.$userId.'轮询用户：'.$room->getPlayerToTurnUserId(), 'error');
        }
    }

    //判断当前比赛场次是否处于选庄阶段
    private function isRaceForLandlord(Room $room, $raceNum) : bool{
        if ($room->getRunningRaceNum() != $raceNum) {
            Log::record('当前进行中场次号不对','error');
            return false;
        }
        $RACE_PLAY_STATE = json_decode(RACE_PLAY_STATE, true);
        $theRaceState = $room->getRaceState($raceNum);
        if ($theRaceState != $RACE_PLAY_STATE['CHOICE_LANDLORD']) {
            Log::record('当前不处于选庄阶段', 'error');
            return false;
        }
        return true;
    }
}