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
 * ALL_RACE_FINISHED：所有比赛结束
 * CLOSE： 关闭
 * */
define('ROOM_STATE', '{ "OPEN": 1, "PLAYING": 2, "ALL_RACE_FINISHED": 3,"CLOSE": 4}');

//比赛状态
define('RACE_PLAY_STATE', '{ "NOT_BEGIN": 1, "CHOICE_LANDLORD": 2, "ROLL_DICE": 3,
"DEAL": 4, "BET": 5, "SHOW_DOWN": 6,"SHOW_RESULT": 7,"FINISHED": 8}');


//值类型
define('MAJ_VALUE_TYPE', '{ "DUI_ZI": 1, "BI_SHI": 2, "DIAN": 3}');

//对比结果
define('COMPARE_DX_RE', '{ "BIG": "w", "SMALL": "s", "EQ": "e"}');

//当方式
define('BE_LANDLORD_WAY', '{ "TURN": 1,"RAP":2}');


//当方式
define('ROOM_PLAY_MEMBER_STATE', '{ "ON_LINE": 1,"OFF_LINE": 2,"KICK_OUT":3}');

//当方式
define('ROOM_PLAY_MEMBER_TYPE', '{ "ROOM_OWNER": 1,"PLAYER": 2,"WATCHER":3}');