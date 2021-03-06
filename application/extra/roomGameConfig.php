<?php
return [
    'rapLandlordTime' => 6,//抢庄倒计时时间
    'dealAction' => 11, //摇色子以及发牌行为耗时 s
    'betTime' => 15, //下注持续时间 s
    'showDownTime' => 13, //开牌 大小对比 结果显示 s
    'landlordLastCount' => 4, //每一次当庄场次数 s
    'roomTimeoutTime' => 3600, //socket房间存在超时时间，主要产生原因是抢庄以及开始时间不确定s
    'diamondPrice' => 0.5, //每个钻的单价 单位：元
    'chipValList' => [5, 10, 50, 100], //下注值集合
    'userAudioUrl'=>'https://www.toplaygame.cn/phpserver/public/voice/',//用户图标地址
    'userIconUrl'=>'https://www.toplaygame.cn/phpserver/public/upload/'//用户图标地址
];