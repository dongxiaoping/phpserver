<?php


namespace app\race\service\socket;


class BackData
{
    private $message = "";
    private $type;
    private $flag = 1;
    private $data;

    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getFlag()
    {
        return $this->flag;
    }

    /**
     * @param mixed $flag
     */
    public function setFlag($flag): void
    {
        $this->flag = $flag;
    }

    public function getBackData()
    {
        return array('type' => $this->type, 'info' => array('flag' => $this->flag,
            'message' => $this->message, 'data' => $this->data));
    }

    //获取发牌通知数据
    public static function getRaceStateDealBack($raceNum, $roomId, $landlordId)
    {
        return array('type' => SocketActionTag::$RACE_DEAL_NOTICE, 'info' => array(
            'raceNum' => $raceNum, 'roomId' => $roomId, 'landlordId' => $landlordId));
    }

    //获取下注通知数据
    public static function getRaceBetBack($raceNum, $roomId, $landlordId)
    {
        return array('type' => SocketActionTag::$RACE_BET_NOTICE, 'info' => array(
            'raceNum' => $raceNum, 'roomId' => $roomId, 'landlordId' => $landlordId));
    }

    //获取比大小通知数据
    public static function getRaceShowDownBack($raceNum, $roomId,$raceResult, $roomResult, $landlordId)
    {
        return array('type' => SocketActionTag::$RACEC_SHOW_DOWN_NOTICE, 'info' => array(
            'raceNum' => $raceNum, 'roomId' => $roomId,'raceResult' => $raceResult,
            'roomResult' => $roomResult, 'landlordId' => $landlordId));
    }

    //所有场次比赛结束通知数据
    public static function getAllRaceFinishBack($roomResult){
        return array('type' => SocketActionTag::$ALL_RACE_FINISHED_NOTICE,
            'info' => array('roomResult' => $roomResult));
    }

    //选地主通知
    public static function getChoiceLandLordBack($raceNum, $roomId){
        return array('type' => SocketActionTag::$RACE_CHOICE_LANDLORD_NOTICEC,
            'info' => array('raceNum' => $raceNum, 'roomId' => $roomId));
    }

    //选地主通知
    public static function getMemberInRoomBack($memberInfo){
        return array('type' => SocketActionTag::$MEMBER_IN_ROOM_NOTICE,
            'info' => $memberInfo);
    }
}