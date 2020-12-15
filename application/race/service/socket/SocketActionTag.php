<?php


namespace app\race\service\socket;


class SocketActionTag
{
    public static $ENTER_ROOM_REQ = "enterRoom"; //进入房间事件请求
    public static $START_GAME_REQ = "startRoomGame";//开始游戏请求
    public static $LANDLORD_SELECTED_REQ = "landlordSelected";//选择当地主请求
    public static $RACE_BET_REQ = "raceBet";//下注请求
    public static $CANCEL_BET_REQ = "cancelRaceBet";//取消下注请求
    public static $CHAT_CARTON_MESSAGE_REQ = "chatCartonMessage";//图片表情发送请求
    public static $KICK_OUT_MEMBER_REQ = "kickOutMemberFromRoom";//踢出玩家请求
    public static $AUDIO_PLAY_NOTICE = "audioPlayNotice";//语音通知

    public static $ENTER_ROOM_RES = "enterRoomRes";//进入房间请求的回复
    public static $MEMBER_OUT_ROOM_NOTICE = "memberOutRoom";//成员离开房间通知
    public static $MEMBER_IN_ROOM_NOTICE = "memberInSocketRoom";//有成员进入房间通知
    public static $RACE_DEAL_NOTICE = "raceStateDeal";//发牌环节通知
    public static $TURN_LANDLORD_NOTICE = "turnLandlord";//轮庄通知
    public static $RACE_BET_NOTICE = "raceStateBet";//下注环节通知
    public static $RACEC_SHOW_DOWN_NOTICE = "raceStateShowDown";//比大小环节通知
    public static $ALL_RACE_FINISHED_NOTICE = "allRaceFinished";//所有场次比赛结束通知
    public static $RACE_CHOICE_LANDLORD_NOTICE = "raceStateChoiceLandlord";//选地主通知
    public static $CHECK_ROOM_MEMBER_NOTICE = "checkRoomMember";//房间成员核对
    public static $SURE_BE_LANDLORD_IN_TURN = "sureBeLandlordInTurn";//成员同意轮庄当庄

}