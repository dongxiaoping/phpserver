<?php


namespace app\race\service\socket;


class WordDes
{
    public static $USER_NOT_EXIST = "用户不存在";
    public static $REPEAT_LOGIN_IN = "用户重复登录，您被迫退出";
    public static $ROOM_NOT_EXIST = "房间不存在";
    public static $GAME_OVER = "游戏已结束";
    public static $GAME_CLOSE_ERROR = "游戏异常关闭";
    public static $USER_FORBID = "已被禁止进入";
    public static $GAME_PLAYING = "游戏已开始";
    public static $PEOPLE_OVER_LIMIT = "人数超限";
    public static $ROOM_PLAYING = "游戏已开发";
    public static $USER_NOT_CONNECT = "用户不在线";
    public static $USER_NOT_IN_ROOM = "用户不在当前房间";
    public static $USER_OUT_SUCCESS = "用户退出房间";
}