<?php
// +----------------------------------------------------------------------
// | Copyright (php), BestTV.
// +----------------------------------------------------------------------
// | Author: karl.dong
// +----------------------------------------------------------------------
// | Date：2018/7/19
// +----------------------------------------------------------------------
// | Description: 
// +----------------------------------------------------------------------
/*
*
THINK_PATH 框架系统目录
APP_PATH 应用目录（默认为入口文件所在目录）
LIB_PATH 系统类库目录（默认为 THINK_PATH.'Library/'）
CORE_PATH 系统核心类库目录 （默认为 LIB_PATH.'Think/'）
MODE_PATH 系统应用模式目录 （默认为 THINK_PATH.'Mode/'）
BEHAVIOR_PATH 行为目录 （默认为 LIB_PATH.'Behavior/'）
COMMON_PATH 公共模块目录 （默认为 APP_PATH.'Common/'）
VENDOR_PATH 第三方类库目录（默认为 LIB_PATH.'Vendor/'）
RUNTIME_PATH 应用运行时目录（默认为 APP_PATH.'Runtime/'）
HTML_PATH 应用静态缓存目录（默认为 APP_PATH.'Html/'）
CONF_PATH 应用公共配置目录（默认为 COMMON_PATH.'Conf/'）
LANG_PATH 公共语言包目录 （默认为 COMMON_PATH.'Lang/'）
LOG_PATH 应用日志目录 （默认为 RUNTIME_PATH.'Logs/'）
CACHE_PATH 项目模板缓存目录（默认为 RUNTIME_PATH.'Cache/'）
TEMP_PATH 应用缓存目录（默认为 RUNTIME_PATH.'Temp/'）
DATA_PATH 应用数据目录 （默认为 RUNTIME_PATH.'Data/'）
 * */

/* 房间状态
 * OPEN ：开启
 * PLAYING：游戏中
 * CLOSE： 关闭
 * */
define('ROOM_STATE', '{ "OPEN": 1, "PLAYING": 2, "CLOSE": 3}');

//比赛状态
define('RACE_PLAY_STATE', '{ "NOT_BEGIN": 1, "CHOICE_LANDLORD": 2,"DEAL": 3, "BET": 4, "SHOW_DOWN": 5,"FINISHED": 6}');

//麻将值类型
define('MAJ_VALUE_TYPE', '{ "DUI_ZI": 1, "BI_SHI": 2, "DIAN": 3, "ER_BA_GANG": 4}'); //二八杠

//对比结果
define('COMPARE_DX_RE', '{ "BIG": "w", "SMALL": "s", "EQ": "e"}');

//当庄方式
define('BE_LANDLORD_WAY', '{ "TURN": 1,"RAP":2}');


//房间成员状态
define('ROOM_PLAY_MEMBER_STATE', '{ "ON_LINE": 1,"OFF_LINE": 2,"KICK_OUT":3}');

//房间成员类型
define('ROOM_PLAY_MEMBER_TYPE', '{ "ROOM_OWNER": 1,"PLAYER": 2,"WATCHER":3}');

//用户类型
define('USER_TYPE', '{ "VISIT_USER": 1,"NORMAL_USER": 2,"CHEAT_USER":3}');

//位置
define('BET_LOCATION', '{ "SKY": "sky","LAND": "land","MIDDLE":"middle","BRIDG": "bridg","SKY_CORNER":"skyCorner","LAND_CORNER":"landCorner"}');

//房间付费方式  AA 各自付费    CREATOR 代开
define('ROOM_PAY', '{ "AA": 1,"CREATOR":2}');

//充值平台
define('RECHARGE_PLATFORM', '{ "WECHAT": 1}');