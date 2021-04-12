<?php
namespace EIS\Lab\SIF;
$pageID = 22;
require_once dirname(__DIR__) . '/core/init.php';

$latestFile = ROOT_SIF_CACHE . '/latest-arena.js';

require ROOT_SIF_WEB . '/common-d42c0d8a/head1.php';
echo HTML::css('latest-event');
echo HTML::js('latest-arena');
?>
<script>
<?=Cache::read('latest-arena.js')?>
</script>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/head2.php';
?>
<h2 class="eis-sif-text"></h2>
<div class="eis-sif-timetip on-background"></div>
<section class="eis-sif-section-noborder">
<h4><i class="fas fa-user-plus fa-lg"></i> 活动应援社员</h4>
<div class="eis-sif-notice"><p><i class="fas fa-exclamation-circle"></i> 本页面数据更新于 <?=date('m/d H:i', filemtime($latestFile))?>。在此时间之后推出的新社员未在下表中列出，其应援效果请在游戏内查看。</p></div>
<table id="cheers" class="eis-sif-table">
<thead>
<tr><th>编号</th><th></th><th></th><th>名称</th><th>应援类型</th><th colspan=2>未觉醒</th><th colspan=2>觉醒后</th></tr>
</thead>
<tbody></tbody>
</table>
</section>
<section class="eis-sif-section-noborder">
<h4><i class="fas fa-battery-half"></i> Special Session 消费 LP</h4>
<p id="arena-lp"></p>
</section>
<section class="eis-sif-section-noborder">
<h4><i class="fas fa-music"></i> 参考歌单</h4>
<table id="arena-lives" class="eis-sif-table">
<thead>
<tr><th></th><th>歌曲</th><th>难度</th></tr>
</thead>
<tbody></tbody>
</table>
</section>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/foot.php';
