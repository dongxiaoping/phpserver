<?php
return [
    'dealAction' => 11, //摇色子以及发牌行为耗时 s
    'betTime' => 15, //下注持续时间 s
    'showDownTime' => 17, //开牌 大小对比 结果显示 s
    'landlordLastCount' => 5, //每一次当庄场次数 s
    'roomTimeoutTime' => 3600, //socket房间存在超时时间，主要产生原因是抢庄以及开始时间不确定s
    'invalidRoomCheckTime' => 600 //无效房间检查周期 s
];