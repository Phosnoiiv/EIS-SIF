<?php
namespace EIS\Lab\SIF;

$homeButtons = [
       2 => ['page'=>2, /* SIF 特殊登录奖励 */ 'servers'=>[1,3,2], 'desc'=>'2018/09～已断更', 'cache'=>'login.js'],
       3 => ['page'=>3, 'name'=>'阶梯招募单级', 'servers'=>[1,2,3], 'desc'=>'2018/08～2020/02', 'cache'=>'box.stepup.js'],
       6 => ['page'=>6, 'name'=>'Medley Festival 任务', 'servers'=>[2], 'desc'=>'以第 20 次为例', 'cache'=>'event.mf.mission.js'],
       7 => ['page'=>7, /* SIFAS 特殊登录奖励 */ 'servers'=>[4,5], 'desc'=>'附背景', 'cache'=>'login.js'],
       8 => ['page'=>8, 'name'=>'特别剧情开放情况', 'servers'=>[1,2,3], 'desc'=>'已断更', 'cache'=>'stories.js'],
       9 => ['page'=>9, 'name'=>'阶梯招募列表', 'servers'=>[1,2,3], 'desc'=>'2018/08～2020/02', 'cache'=>'box.stepups.js'],
      10 => ['page'=>10, /* SIF 活动交换所 */ 'servers'=>[1,3,2], 'desc'=>'2020/01～已断更', 'cache'=>'trades.js'],
      11 => ['page'=>11, /* SIFAS 活动交换所 */ 'servers'=>[4], 'desc'=>'已断更', 'cache'=>'trades.js'],
      13 => ['page'=>13, /* 节奏嘉年华活动歌单 */ 'servers'=>[1,2,3], 'desc'=>'已断更', 'cache'=>'event.rc.js'],
      14 => ['page'=>14, /* 部分限时课题文本 */ 'servers'=>[1,3], 'desc'=>'仅限查卡器无文字的课题', 'cache'=>'goals.js'],
      15 => ['page'=>15, /* SIFAS 语音文本 */ 'servers'=>[4,5,6], 'desc'=>'不含音频／已断更', 'cache'=>'voices.js'],
      16 => ['page'=>16, /* 封面背景图库 */ 'servers'=>[1,3,2], 'desc'=>'2019/02～2023/01', 'cache'=>'covers.js'],
      19 => ['page'=>19, /* 招募画像图库 */ 'servers'=>[1], 'desc'=>'7 周年纪念主卡池画像', 'cache'=>'posters.js'],
      20 => ['page'=>20, /* SIFAS 歌曲综合资料 */ 'servers'=>[4,5], 'cache'=>'live-detail.js'],
      21 => ['page'=>21, 'name'=>'活动点数计算器', 'desc'=>'含控分计算'],
      23 => ['page'=>23, /* SIFAS 活动课题 */ 'servers'=>[4,5,6], 'desc'=>'已断更', 'cache'=>'goals.js'],
      27 => ['page'=>27, 'name'=>'通常饰品制作概率计算器'],
      29 => ['page'=>29, /* SIF 饰品一览 */ 'servers'=>[1,3], 'cache'=>[290101]],
      31 => ['page'=>31, /* Live ♪ Arena 剧情合集 */ 'servers'=>[1,2,3]],
      32 => ['page'=>32, /* SIF 活动加成一览 */ 'servers'=>[1], 'desc'=>'庆典活动（2022/04～2023/03）<br>Live ♪ Arena（2020/11～2023/03）', 'cache'=>'event-yell.json'],
];
$homeButtonRegions = [
    1 => [29, 32, 31, 16, 19, 6, 21, 27],
    2 => [2, 13, 10, 9, 3, 8, 14],
    3 => [7, 20],
    4 => [15, 23, 11],
];
