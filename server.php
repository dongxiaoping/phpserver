<?php
// +----------------------------------------------------------------------
// | Copyright (php), BestTV.
// +----------------------------------------------------------------------
// | Author: karl.dong
// +----------------------------------------------------------------------
// | Date：2019/11/15
// +----------------------------------------------------------------------
// | Description: 调起 websocket 服务  启动命令 php server.php 文档在thinkphp的说明文档中
// | php server.php start 调试模式  php server.php start -d 正式上线
// +----------------------------------------------------------------------
define('APP_PATH', __DIR__ . '/application/');
define('BIND_MODULE','race/Worker');
// 加载框架引导文件
require __DIR__ . '/globalConst.php';
require __DIR__ . '/thinkphp/start.php';
