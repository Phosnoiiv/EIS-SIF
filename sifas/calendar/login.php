<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
$pageID = 7;
require_once dirname(__DIR__) . '/core/init.php';

$limitType = SIF\Limit::TYPE_GALLERY;
$latestFile = ROOT_SIFAS_CACHE . '/login.js';
$relatedPage = [
    'href' => 'calendar/login.php',
    'name' => 'SIF 特殊登录奖励',
];
$barContentsAppend = [
    '<i class="fas fa-calendar"></i> 月份：<input id="month" type="month" min="2019-09" max="' . date('Y-m') . '" value="' . date('Y-m') . '" onchange="produce()"/>',
    '<i class="fas fa-server"></i> 服务器：<select id="server" onchange="produce()"><option value="1" selected>日语版</option><option value="3">简体字版</option><option value="2">国际版</option></select>',
];

$instantGroups = $instantPNGs = [];
$dictGroups = new SIF\Dict(true);
$sql = 'SELECT * FROM s_jpg_schedule WHERE time_open<=datetime("now","localtime") AND time_close>=datetime("now","localtime")';
$dbGroups = DB::lt_query('eis.s3db', $sql);
while ($dbGroup = $dbGroups->fetchArray(SQLITE3_ASSOC)) {
    $id = $dictGroups->set($dbGroup['group']);
    $instantGroups[$id] = [
        strtotime($dbGroup['time_close'] . '+0800'),
        $dbGroup['pass'],
    ];
    $sql = 'SELECT * FROM s_jpg WHERE id IN (SELECT jpg FROM s_jpg_group WHERE [group]=' . $dbGroup['group'] . ')';
    $dbPNGs = DB::lt_query('eis.s3db', $sql);
    while ($dbPNG = $dbPNGs->fetchArray(SQLITE3_ASSOC)) {
        $key = $dbPNG['key'];
        $instantPNGs[$key] = [
            $id,
            $dbPNG['code'],
            round($dbPNG['size'] / 1024),
        ];
    }
}

require ROOT_SIFAS_WEB . '/common-b63adcdf/head1.php';
echo SIF\HTML::css('gallery-d42c0d8a');
echo HTML::css('login');
echo HTML::js('login');
?>
<script>
<?=SIF\HTML::json('availablePNGGroups', $instantGroups)?>
<?=SIF\HTML::json('availablePNGs', $instantPNGs)?>
<?=Cache::read('login.js')?>
</script>
<?php
require ROOT_SIFAS_WEB . '/common-b63adcdf/head2.php';
?>
<div id="panel-container">
<div id="panel-main">
<table id="calendar" class="eis-sif-calendar">
<thead>
<tr><td id="caption" class="eis-sif-caption" colspan=7></td></tr>
<tr><th>Su</th><th>M</th><th>Tu</th><th>W</th><th>Th</th><th>F</th><th>Sa</th></tr>
</thead>
<tbody></tbody>
</table>
<p class="eis-sif-note">※ 本日历中的所有奖励均标于最早可能领取到的日期。</p>
</div>
<div id="panel-side">
<section class="eis-sif-section">
<h4>本月统计</h4>
<div id="counts"></div>
<p class="eis-sif-note">※ 此处仅统计部分常见奖励，更多其它道具请在日历中查看。</p>
</section>
<section class="eis-sif-section">
<h4>明细</h4>
<ul id="bonuses"></ul>
<p class="eis-sif-note">※ 列表中的颜色仅用于区分活动名义，游戏内没有实质区别。</p>
</section>
</div>
</div>
<div id="dialog-bonus" class="eis-sif-hidden" title="登录奖励详情" data-width=500>
<div class="eis-sif-dialog-head">
<span class="eis-sif-dialog-title"></span>
</div>
<div class="eis-sif-dialog-info"></div>
<table class="bonus-table"></table>
<h4 class="eis-sif-dialog-section-header">背景</h4>
<img class="bonus-bg-thumb"/>
<div id="dialog-gallery-panel">
<div id="dialog-gallery-limit"><i class="fas fa-bolt"></i> <span class="limit-capacity-name" data-limit=<?=$limitType?>></span>：<span class="limit-capacity-amount" data-limit=<?=$limitType?>></span></div>
<div id="dialog-gallery-free" class="eis-sif-notice">
<span id="dialog-gallery-flag">剩余 <span class="eis-sif-countdown" data-time=0 data-countdown-short=1></span></span>
<p><i class="fas fa-charging-station"></i> 现在下载此背景不消耗次数。</p>
</div>
<p class="eis-sif-button-line"><a id="dialog-gallery-link" class="eis-jq-button" target="_blank" onclick="galleryDownloaded()"><i class="fas fa-download"></i> 下载此背景</a></p>
</div>
<div id="dialog-gallery-notes"></div>
<div id="bonus-bg-none">
<p>此登录奖励的背景信息暂缺。</p>
</div>
<h4 class="eis-sif-dialog-section-header">注记</h4>
<p class="eis-sif-note">※ 本页面所示的登录奖励时间基于游戏服务器所在时区，而您在游戏内看到的时间是基于您设备的时区设置。</p>
<p class="eis-sif-note">※ SIFAS 的登录奖励刷新时间为每天 4:00。</p>
<p class="eis-sif-note">※ 鼠标悬停或触屏点击问号图标，可以查看道具的官方介绍。</p>
</div>
<?php
require ROOT_SIF_WEB . '/common-4fcb29e1/foot.php';
