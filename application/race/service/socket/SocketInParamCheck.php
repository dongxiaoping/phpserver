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
            case SocketActionTag::$START_GAME_REQ: //
                if (isset($data['info']['roomId']) && isset($data['info']['userId'])) {
                    return true;
                }
            case SocketActionTag::$LANDLORD_SELECTED_REQ: //
                if (isset($data['info']['roomId']) && isset($data['info']['raceNum']) && isset($data['info']['landlordId'])) {
                    return true;
                }
            case SocketActionTag::$RACE_BET_REQ: //
                if (isset($data['info']['userId']) && isset($data['info']['roomId']) && isset($data['info']['raceNum'])
                    && isset($data['info']['betLocation']) && isset($data['info']['betVal'])) {
                    return true;
                }
            case SocketActionTag::$CANCEL_BET_REQ: //
                if (isset($data['info']['userId']) && isset($data['info']['roomId']) && isset($data['info']['raceNum'])
                    && isset($data['info']['betLocation'])) {
                    return true;
                }
            case SocketActionTag::$CHAT_CARTON_MESSAGE_REQ: //
                if (isset($data['info']['roomId']) && isset($data['info']['info'])) {
                    return true;
                }
            case SocketActionTag::$KICK_OUT_MEMBER_REQ: //
                if (isset($data['info']['roomId']) && isset($data['info']['kickUserId'])) {
                    return true;
                }
            case SocketActionTag::$AUDIO_PLAY_NOTICE: //
                if (isset($data['info']['roomId']) && isset($data['info']['userId'])
                    && isset($data['info']['voiceName'])) {
                    return true;
                }
            default:
                return false;
        }
        return false;
    }

}