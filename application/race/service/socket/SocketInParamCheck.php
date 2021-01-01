<?php


namespace app\race\service\socket;

/*传入的参数检测
 * */

use think\Log;

class SocketInParamCheck
{
    public static function isRight($data): bool
    {
        switch ($data['type']) {
            case SocketActionTag::$ENTER_ROOM_REQ: //进入房间
                if (isset($data['info']['roomId']) && isset($data['info']['userId'])) {
                    return true;
                }
                break;
            case SocketActionTag::$START_GAME_REQ: //
                if (isset($data['info']['roomId']) && isset($data['info']['userId'])) {
                    return true;
                }
                break;
            case SocketActionTag::$LANDLORD_SELECTED_REQ: //
                if (isset($data['info']['roomId']) && isset($data['info']['raceNum']) && isset($data['info']['landlordId'])) {
                    return true;
                }
                break;
            case SocketActionTag::$RACE_BET_REQ: //
                if (isset($data['info']['userId']) && isset($data['info']['roomId']) && isset($data['info']['raceNum'])
                    && isset($data['info']['betLocation']) && isset($data['info']['betVal'])) {
                    return true;
                }
                break;
            case SocketActionTag::$CANCEL_BET_REQ: //
                if (isset($data['info']['userId']) && isset($data['info']['roomId']) && isset($data['info']['raceNum'])
                    && isset($data['info']['betLocation'])) {
                    return true;
                }
                break;
            case SocketActionTag::$CHAT_CARTON_MESSAGE_REQ: //
                if (isset($data['info']['roomId']) && isset($data['info']['info'])) {
                    return true;
                }
                break;
            case SocketActionTag::$KICK_OUT_MEMBER_REQ: //
                if (isset($data['info']['roomId']) && isset($data['info']['kickUserId'])) {
                    return true;
                }
                break;
            case SocketActionTag::$AUDIO_PLAY_NOTICE: //
                if (isset($data['info']['roomId']) && isset($data['info']['userId'])
                    && isset($data['info']['voiceName'])) {
                    return true;
                }
                break;
            case SocketActionTag::$SURE_BE_LANDLORD_IN_TURN :
            case SocketActionTag::$SURE_BE_LANDLORD_PASS :
            if (isset($data['info']['roomId']) && isset($data['info']['userId'])
                    && isset($data['info']['raceNum'])) {
                    return true;
                }
                break;
            default:
                return false;
        }
        return false;
    }

}