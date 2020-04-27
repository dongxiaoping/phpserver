<?php


namespace app\race\service\socket;

use think\Log;
use Workerman\Lib\Timer;
class Room
{
    public $socketData;
    public $socketServer;
    private $roomId;
    public $scoreList = array(); //已发布场次的玩家得分集合
    public $rapLandlordUserList = array();//当前场次发起抢庄者用户ID集合
    public $raceList = array();
    private $state = null;//房间状态
    private $runningRaceNum = 0;
    private $race_count;
    private $dealActionTimer;//摇色子、发牌定时器
    private $betTimer;//下注定时器
    private $showDownTimer;//比大小定时器
    private $rapLandlordTimer;//抢庄定时器
    private $createUserId = null; //房间创建者ID
    private $createTime; //房间创建的时间戳

    public function __construct($roomId , $createUserId, $race_count,SocketData $socketData, SocketServer $socketServer)
    {
        $this->socketData = $socketData;
        $this->socketServer = $socketServer;
        $this->race_count = $race_count;
        $this->roomId  = $roomId;
        $this->createUserId = $createUserId;
        $this->createTime = time();
        $this->initRace($race_count);
    }


    /**
     * @return null
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param null $state
     */
    public function setState($state): void
    {
        $this->state = $state;
    }

    /**
     * @return int
     */
    public function getRunningRaceNum(): int
    {
        return $this->runningRaceNum;
    }

    /**
     * @param int $runningRaceNum
     */
    public function setRunningRaceNum(int $runningRaceNum): void
    {
        $this->runningRaceNum = $runningRaceNum;
    }

    public function getRoomId(){
        return $this->roomId;
    }

    public function getRaceState($race_num)
    {
        return $this->raceList[$race_num]['state'];
    }

    public function getRaceLandlordId($race_num)
    {
        return $this->raceList[$race_num]['landlord_id'];
    }

    //为比赛选取一个庄家，返回改庄家的用户id
    public function getOneLandlordId()
    {
        $selected_landlord_user_id = null;
        if (count($this->rapLandlordUserList) <= 0) {//没有人抢庄
            $selected_landlord_user_id = $this->socketServer->get_rand_landlord_user_id($this->roomId);
        } else {
            $indexSet = rand(0, count($this->rapLandlordUserList) - 1);
            $selected_landlord_user_id = $this->rapLandlordUserList[$indexSet];
        }
        return $selected_landlord_user_id;
    }


    public function setRaceLandlord($race_num, $landlord_id)
    {
        try {
            $count = config('roomGameConfig.landlordLastCount');
            for ($i = 0; $i < $count; $i++) {
                if (isset($this->raceList[$race_num + $i])) {
                    $this->raceList[$race_num + $i]['landlord_id'] = $landlord_id;
                }
            }
        } catch (Exception $e) {
            //Log::write($e->getMessage(), 'error');
        }
    }

    private function setRaceState($race_num, $state)
    {
        $this->raceList[$race_num]['state'] = $state;
        $this->socketServer->change_race_state($this->roomId, $race_num, $state);
    }

    private function initRace($race_count)
    {
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        $this->state = $ROOM_STATE['OPEN'];
        $RACE_PLAY_STATE = json_decode(RACE_PLAY_STATE, true);
        for ($i = 0; $i < $race_count; $i++) {
            $item = array('state' => $RACE_PLAY_STATE['NOT_BEGIN'], 'landlord_id' => null);
            $this->raceList[$i] = $item;
        }
        //Log::write('workman/room:场次信息初始化完毕', 'info');
    }

    public function broadcastToAllMember($message)
    {
        $people_list = $this->socketData->get_connect_people_list_by_room_id($this->roomId);
        foreach ($people_list as $people) {
            $connection = $people->get_connection();
            $connection->send(json_encode($message));
        }
    }
    
    private function changeDealAction()
    { //发牌
        $race_play_state = json_decode(RACE_PLAY_STATE, true);
        $this->setRaceState($this->runningRaceNum, $race_play_state['DEAL']);
        $the_landlord_id = $this->getRaceLandlordId($this->runningRaceNum);
        $message = BackData::getRaceStateDealBack($this->runningRaceNum, $this->roomId, $the_landlord_id);
        $this->broadcastToAllMember($message);
    }

    private function changeRollBet()
    { //下注
        $race_play_state = json_decode(RACE_PLAY_STATE, true);
        $this->setRaceState($this->runningRaceNum, $race_play_state['BET']);
        $the_landlord_id = $this->getRaceLandlordId($this->runningRaceNum);
        $message = BackData::getRaceBetBack($this->runningRaceNum, $this->roomId, $the_landlord_id);
        $this->broadcastToAllMember($message);
        //Log::write('workman/room:启动下注流程，房间号：' . $this->roomId . '场次号：' . $this->runningRaceNum, 'info');
    }

    private function changeShowDown()
    { //比大小
        $race_play_state = json_decode(RACE_PLAY_STATE, true);
        $this->setRaceState($this->runningRaceNum, $race_play_state['SHOW_DOWN']);
        $race_result = $this->socketServer->get_race_result($this->roomId, $this->runningRaceNum);
        $this->addScoreList($race_result);
        $the_landlord_id = $this->getRaceLandlordId($this->runningRaceNum);
        $message = BackData::getRaceShowDownBack($this->runningRaceNum, $this->roomId, $race_result, $this->scoreList, $the_landlord_id);
        $this->broadcastToAllMember($message);
    }

    private function addScoreList($raceScoreList)
    {
        for ($i = 0; $i < count($raceScoreList); $i++) {
            $user_id = $raceScoreList[$i]['userId'];
            $score = $raceScoreList[$i]['score'];
            if (isset($this->scoreList[$user_id])) {
                $this->scoreList[$user_id] += $score;
            } else {
                $this->scoreList[$user_id] = $score;
            }
        }
    }

    public function destroy()
    {
        Timer::del($this->dealActionTimer);
        Timer::del($this->betTimer);
        Timer::del($this->showDownTimer);
        Timer::del($this->rapLandlordTimer);
        $this->socketData->remove_room_by_id($this->roomId);
        //Log::write('workman/room:房间结束销毁:' . $this->roomId);
    }

    public function startRace()
    {
        try {
            if ($this->runningRaceNum >= $this->race_count) {
                $ROOM_STATE = json_decode(ROOM_STATE, true);
                $this->socketServer->change_room_state($this->roomId, $ROOM_STATE['CLOSE']);
                $this->state = $ROOM_STATE['CLOSE'];
                $info = $this->socketServer->get_room_result($this->roomId, $this->race_count - 1);
                $message = BackData::getAllRaceFinishBack($info);
                $this->broadcastToAllMember($message);
                $this->destroy();
                return;
            }

            $this->socketServer->change_on_race($this->roomId, $this->runningRaceNum);
            $the_landlord_id = $this->getRaceLandlordId($this->runningRaceNum);
            if ($the_landlord_id == null) {
                $race_play_state = json_decode(RACE_PLAY_STATE, true);
                $this->setRaceState($this->runningRaceNum, $race_play_state['CHOICE_LANDLORD']);
                $message = BackData::getChoiceLandLordBack($this->runningRaceNum, $this->roomId);
                $this->broadcastToAllMember($message); //广播选地主
                $this->rapLandlordTimer = Timer::add(config('roomGameConfig.rapLandlordTime'), function () {
                    $selected_landlord_user_id = $this->getOneLandlordId();
                    if ($selected_landlord_user_id == null) {
                        $ROOM_STATE = json_decode(ROOM_STATE, true);
                        $this->socketServer->change_room_state($this->roomId, $ROOM_STATE['CLOSE']);
                        //Log::write('workman/room:没有可当地主的成员，房间关闭,房间号:' . $this->roomId);
                        $this->destroy();
                        return;
                    }
                    $this->setRaceLandlord($this->runningRaceNum, $selected_landlord_user_id);
                    $landlordLastCount = config('roomGameConfig.landlordLastCount');
                    $this->socketServer->change_race_landlord($this->roomId, $this->runningRaceNum, $selected_landlord_user_id, $landlordLastCount); //数据库修改
                    $this->nextRaceNoLandlordSelect();
                }, array(), false);
            } else {
                $this->nextRaceNoLandlordSelect();
            }

        } catch (Exception $e) {
            //Log::write($e->getMessage(), 'error');
        }
    }

    //除选地主外的比赛定时流程
    private function nextRaceNoLandlordSelect()
    {
        $this->changeDealAction();
        $this->dealActionTimer = Timer::add(config('roomGameConfig.dealAction'), function () {
            $this->changeRollBet();
            $this->betTimer = Timer::add(config('roomGameConfig.betTime'), function () {
                $this->changeShowDown();
                $this->showDownTimer = Timer::add(config('roomGameConfig.showDownTime'), function () {
                    $this->rapLandlordUserList = array();
                    $this->runningRaceNum = $this->runningRaceNum + 1;
                    $this->startRace();
                }, array(), false);
            }, array(), false);
        }, array(), false);
    }
}