<?php
namespace EIS\Lab\SIF;
$pageID = 2;
require_once dirname(__DIR__) . '/core/init.php';

$latestFile = ROOT_SIF_CACHE . '/login.js';
$relatedPage = [
    'href' => 'calendar/login.php',
    'name' => 'SIFAS 特殊登录奖励',
];
$barContentsAppend = [
    '<span class="ui-icon ui-icon-calendar"></span>月份：<input id="month" type="month" min="2018-09" max="' . date('Y-m') . '" value="' . date('Y-m',strtotime('-1 month')) . '" onchange="produce()"/>',
    '<span class="ui-icon ui-icon-comment"></span>服务器：<select id="server" onchange="produce()"><option value="1">日语版</option><option value="3" selected>简体字版</option><option value="2">国际版</option></select>',
];

require ROOT_SIF_WEB . '/common-d42c0d8a/head1.php';
echo HTML::css('login');
echo HTML::js('login');
?>
<script>
<?=Cache::readJson('gift.login.json', 'gifts')?>
<?=Cache::read('login.js')?>
</script>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/head2.php';
?>
<div id="panel-container">
<div id="panel-main">
<table class="eis-sif-calendar">
<thead>
<tr><td id="title" class="eis-sif-caption" colspan=7></td></tr>
<tr><th>Su</th><th>M</th><th>Tu</th><th>W</th><th>Th</th><th>F</th><th>Sa</th></tr>
</thead>
<tbody id="calendar"></tbody>
</table>
<p class="eis-sif-note">※ 本日历中的所有奖励均标于最早可能领取到的日期。</p>
<p class="eis-sif-note">※ 鼠标悬停或触屏点击日历中的任意图片，可以查看相关介绍。</p>
</div>
<div id="panel-side">
<section class="eis-sif-section">
<h4>本月统计</h4>
<div id="counts"></div>
<p class="eis-sif-note">※ 此处仅统计部分常见奖励，更多其它道具请在日历中查看。</p>
</section>
<section class="eis-sif-section">
<h4>明细</h4>
<ul id="list"></ul>
</section>
</div>
</div>
<div id="dialog-bonus" class="eis-sif-hidden" title="登录奖励详情" data-width=500>
<div class="eis-sif-dialog-head">
<span class="eis-sif-dialog-title"></span>
</div>
<div class="eis-sif-dialog-info"></div>
<table class="bonus-table"></table>
<h4 class="eis-sif-dialog-section-header">注记</h4>
<p class="eis-sif-note">※ 本页面所示的登录奖励时间基于游戏服务器所在时区。</p>
<p class="eis-sif-note">※ 鼠标悬停或触屏点击问号图标，可以查看对应道具的介绍。</p>
<div class="bonus-note-extra"></div>
</div>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/foot.php';
