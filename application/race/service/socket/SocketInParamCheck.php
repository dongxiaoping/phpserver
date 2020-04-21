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
            case 'enterRoom': //进入房间
                if (isset($data['info']['roomId']) && isset($data['info']['userId'])) {
                    return true;
                }
            case 'startRoomGame': //
                if (isset($data['info']['roomId']) && isset($data['info']['userId'])) {
                    return true;
                }
            case 'landlordSelected': //
                if (isset($data['info']['roomId']) && isset($data['info']['raceNum']) && isset($data['info']['landlordId'])) {
                    return true;
                }
            case 'raceBet': //
                if (isset($data['info']['userId']) && isset($data['info']['roomId']) && isset($data['info']['raceNum'])
                    && isset($data['info']['betLocation']) && isset($data['info']['betVal'])) {
                    return true;
                }
            case 'cancelRaceBet': //
                if (isset($data['info']['userId']) && isset($data['info']['roomId']) && isset($data['info']['raceNum'])
                    && isset($data['info']['betLocation'])) {
                    return true;
                }
            case 'chatCartonMessage': //
                if (isset($data['info']['roomId']) && isset($data['info']['info'])) {
                    return true;
                }
            case 'kickOutMemberFromRoom': //
                if (isset($data['info']['roomId']) && isset($data['info']['kickUserId'])) {
                    return true;
                }
            default:
                return false;
        }
        return false;
    }

}