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
        Log::record('检查用户是否满足进入房间的条件');
        $this->init();
        $user_info = $this->socket_server->get_user_info($this->user_id);
        if(!$user_info){
            Log::record('用户不存在');
            $this->enter_room_back->setMessage(WordDes::$USER_NOT_EXIST);
            return false;
        }
        $connect_people = $this->socket_data->get_connect_people_by_user_id($this->user_id);
        //1、用户重复登录  2、上次登录没有断开退出
        if($connect_people){
            Log::record('用户重复登录,上次登录没有断开退出', 'error');
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
            Log::record('房间不存在');
            $this->enter_room_back->setMessage(WordDes::$ROOM_NOT_EXIST);
            return false;
        }
        $ROOM_STATE = json_decode(ROOM_STATE, true);
        if ($this->room_info["roomState"] == $ROOM_STATE["CLOSE"]) {
            Log::record('房间已关闭');
            $this->enter_room_back->setMessage(WordDes::$GAME_OVER);
            return false;
        }
        $socket_room = $this->socket_data->get_room_by_id($this->room_id);
        if ($this->room_info["roomState"] == $ROOM_STATE["PLAYING"] && $socket_room == null) {
            Log::record('房间状态异常，强行关闭房间','error');
            $this->socket_server->change_room_state($this->room_id, $ROOM_STATE['CLOSE']);
            $this->enter_room_back->setMessage(WordDes::$GAME_CLOSE_ERROR);
            return false;
        }
        $ROOM_PLAY_MEMBER_TYPE = json_decode(ROOM_PLAY_MEMBER_TYPE, true);
        if(!$this->member_info){
            Log::record('异常进入，房间没有相关用户信息', 'error');
            $this->enter_room_back->setMessage(WordDes::$NO_THE_USER_IN_ROOM);
            return false;
        }
        if ($this->member_info['state'] == $ROOM_PLAY_MEMBER_STATE['KICK_OUT']
            || $this->member_info['roleType'] == $ROOM_PLAY_MEMBER_TYPE['LIMIT']) {
            Log::record('被禁止用户，不能进入房间');
            $this->enter_room_back->setMessage(WordDes::$USER_FORBID);
            return false;
        }
        if($this->member_info['roleType'] == $ROOM_PLAY_MEMBER_TYPE['PLAYER']){
            Log::record('玩家进入房间');
            $this->enter_room_back->setMessage("玩家成功进入房间");
        }else{
            Log::record('游客进入房间');
            $this->enter_room_back->setMessage("游客成功进入房间");
        }
        return true;
    }

    public function enter_room()
    {
        Log::record('执行用户进房间的流程，并对所有用户发出通知');
        $this->init();
        $ROOM_PLAY_MEMBER_STATE = json_decode(ROOM_PLAY_MEMBER_STATE, true);
        if ($this->member_info) {
            Log::record('用户房间成员表中存在，将用户状态改为在线');
            $this->socket_server->change_member_state_in_room($this->user_id, $this->room_id, $ROOM_PLAY_MEMBER_STATE['ON_LINE']);
        } else {
            Log::record('数据异常，用户房间成员表中不存在', 'error');
            return;
        }
        $room = $this->socket_data->get_room_by_id($this->room_id);
        if (!$room) {
            Log::record('socket房间不存在，创建一个');
            $room = new Room($this->room_id, $this->room_info["creatUserId"], $this->room_info["playCount"],
                $this->socket_data, $this->socket_server, $this->room_info["playMode"]);
            $this->socket_data->add_room($room);
            Log::record('EnterRoomAction：socket房间创建成功，房间号：'.$this->room_id);
            Log::record('EnterRoomAction：当前socket房间总数：'.count($this->socket_data->get_room_list()));
        }
        $connect_people = $this->socket_data->get_connect_people_by_connect_id($this->connection->id); //为空的情况
        if($connect_people == null){
            Log::record('数据异常，连接对象不存在', 'error');
            return;
        }
        $connect_people->set_user_id($this->user_id);
        $connect_people->set_room_id($this->room_id);
        $message = BackData::getMemberInRoomBack($this->member_info);
        $room->broadcastToAllMember($message);
        Log::record("用户进入房间，当前房间人数:".count($this->socket_data->get_connect_people_list_by_room_id($this->room_id)));
        //$this->enter_room_back->setData($this->socket_server->get_room_race_info($this->room_id));
        $this->enter_room_back->setData("");
    }
}