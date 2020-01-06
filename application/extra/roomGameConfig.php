<?php
return [
    'rollDiceTime' => 8.5, //摇色子持续时间 s  config('gameTime.rollDiceTime')  客户端的实际时间要在该时间的基础上减去延迟时间
    'dealTime' => 6, //发牌持续时间 s
    'betTime' => 17, //下注持续时间 s
    'showDownTime' => 11, //比大小持续时间 s //翻牌动画固定7.5s
    'showResultKeepTime' => 3, //结果显示停留时间 s
    'showResultTime' => 5, //显示结果持续时间 s
    'delayTime'=>2, //客户端和服务器延迟时间 s
    'landlordLastCount'=> 5 //每一次当庄场次数
];