<?php
/**
 * Created by PhpStorm.
 * User: dongxiaoping-nb
 * Date: 2020/4/17
 * Time: 22:07
 * 进入房间业务逻辑集合
 */

namespace app\race\service\socket\action;
use app\race\service\socket\BackData;
use app\race\service\socket\Room;
use app\race\service\socket\SocketActionTag;
use app\race\service\socket\SocketData;
use app\race\service\socket\SocketServer;
use app\race\service\socket\WordDes;
use think\Log;

class EnterRoomAction
{
    private $room_id;
    private $connection;
    private $user_id;
    private $socket_server;
    private $socket_data;
    private $member_info = null;
    private $room_info = null;
    private $enter_room_back = null;

    public function __construct($room_id, $connection, $user_id, SocketServer $socket_server, SocketData $socket_data)
    {
        $this->room_id = $room_id;
        $this->connection = $connection;
        $this->user_id = $user_id;
        $this->socket_data = $socket_data;
        $this->socket_server = $socket_server;
        $this->enter_room_back = new BackData(SocketActionTag::$ENTER_ROOM_RES);
    }


    public function init(){
        $member_info = $this->socket_server->get_member_info_in_the_room($this->user_id, $this->room_id);
        if ($member_info) {
            $this->member_info = $member_info;
        }
        $room_info = $this->socket_server->get_room_info_by_id($this->room_id);
        if ($room_info) {
            $this->room_info = $room_info;
        }
    }

    public function get_enter_room_back():BackData{
        return $this->enter_room_back;
    }

    public function is_room_right_to_enter()
    {
        $this->init();
        $user_info = $this->socket_server->get_user_info($this->user_id);
        if(!$user_info){
            $this->enter_room_back->setMessage(WordDes::$USER_NOT_EXIST);
            return false;
        }
        $connect_people = $this->socket_data->get_connect_people_by_user_id($this->user_id);
        //1、用户重复登录  2、上次登录没有断开退出
        if($connect_people){
            $connectId = $connect_people->get_connection_id();
            $this->socket_data->remove_connect_people_by_connect_id($connectId);
            $connectOb = $connect_people->get_connection();
            $outRoomBack = new BackData(SocketActionTag::$MEMBER_OUT_ROOM_NOTICE);
            $outRoomBack->setFlag(1);
            $outRoomBack->setMessage(WordDes::$REPEAT_LOGIN_IN);
            $outRoomBack->setData(array('outType'=>OutRoomAction::$ANOTHER_IN_OUT, "userId"=>$this->user_id));
            $connectOb->send(json_encode($outRoomBack->getBackData()));
        }
        $ROOM_PLAY_MEMBER_STATE = json_decode(ROOM_PLAY_MEMBER_STATE, true);
        if (!$this->room_info) {
            $this->enter_room_back->setMessage(WordDes::$ROOM_NOT_EXIST);
            return false;
        }
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        if ($this->room_info["roomState"] == $ROOM_STATE["CLOSE"]) {
            $this->enter_room_back->setMessage(WordDes::$GAME_OVER);
            return false;
        }
        $socket_room = $this->socket_data->get_room_by_id($this->room_id);
        if ($this->room_info["roomState"] == $ROOM_STATE["PLAYING"] && $socket_room == null) {
            $this->socket_server->change_room_state($this->room_id, $ROOM_STATE['CLOSE']);
            $this->enter_room_back->setMessage(WordDes::$GAME_CLOSE_ERROR);
            return false;
        }
        if ($this->member_info) {
            if ($this->member_info['state'] == $ROOM_PLAY_MEMBER_STATE['KICK_OUT']) {
                $this->enter_room_back->setMessage(WordDes::$USER_FORBID);
                return false;
            }
        } else {
            if ($this->room_info["roomState"] != $ROOM_STATE["OPEN"]) {
                $this->enter_room_back->setMessage(WordDes::$GAME_PLAYING);
                return false;
            }
            $limit_count = $this->room_info["memberLimit"];
            $member_count = $this->socket_server->get_member_count_without_kickout($this->room_id);
            if ($member_count >= $limit_count) {
                $this->enter_room_back->setMessage(WordDes::$PEOPLE_OVER_LIMIT);
                return false;
            }
        }
        $this->enter_room_back->setMessage("成功进入房间");
        return true;
    }

    public function enter_room()
    {
        $this->init();
        $ROOM_PLAY_MEMBER_STATE = json_decode(ROOM_PLAY_MEMBER_STATE, true);
        if ($this->member_info) {
            $this->socket_server->change_member_state_in_room($this->user_id, $this->room_id, $ROOM_PLAY_MEMBER_STATE['ON_LINE']);
        } else {
            $this->member_info = $this->socket_server->member_in_to_room($this->room_id, $this->user_id);
        }
        $room = $this->socket_data->get_room_by_id($this->room_id);
        if ($room) {
            //Log::write('EnterRoomAction：socket房间存在，直接进入：'.$this->room_id, 'info');
        } else {
            $room = new Room($this->room_id, $this->room_info["creatUserId"], $this->room_info["playCount"],
                $this->socket_data, $this->socket_server);
            $this->socket_data->add_room($room);
            //Log::write('EnterRoomAction：socket房间创建成功，房间号：'.$this->room_id, 'info');
           // Log::write('EnterRoomAction：当前socket房间总数：'.count($this->socket_data->get_room_list()), 'info');
        }
        $connect_people = $this->socket_data->get_connect_people_by_connect_id($this->connection->id);
        $connect_people->set_user_id($this->user_id);
        $connect_people->set_room_id($this->room_id);
        $message = BackData::getMemberInRoomBack($this->member_info);
        $room->broadcastToAllMember($message);
        //Log::write("用户进入房间，当前房间人数:".count($this->socket_data->get_connect_people_list_by_room_id($this->room_id)), 'info');
        //$this->enter_room_back->setData($this->socket_server->get_room_race_info($this->room_id));
        $this->enter_room_back->setData("");
    }
}