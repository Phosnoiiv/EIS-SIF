<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
$pageID = 20;
require_once dirname(__DIR__) . '/core/init.php';
include ROOT_SIFAS_CACHE.'/live-detail.php';

$helpArticle = 30;
$latestFile = ROOT_SIFAS_CACHE . '/live-detail.js';
require ROOT_SIFAS_WEB . '/common-b63adcdf/head1.php';
echo HTML::css('live-detail');
echo HTML::js('live-detail');
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js" integrity="sha256-R4pqcOYV8lt7snxMQO/HSbVCFRPMdrhAFMH+vr9giYI=" crossorigin="anonymous"></script>
<script>
<?=Cache::readJson('common/buffs.json', 'buffIcons')?>
<?=Cache::read('live-detail.js')?>
<?=Cache::read('words.js')?>
<?=SIF\HTML::json('flags', array_reduce($cacheSongFlags, function($carry, $flag) {
    $tShow = SIF\SIF::toTimestamp($flag[3], $flag[2]);
    $tTill = SIF\SIF::toTimestamp($flag[4], $flag[2]);
    if ($tShow<=($time=time()) && ($tTill==0 || $tTill>=$time)) {
        $carry[] = [$flag[0], $flag[1], $flag[2], $tTill];
    }
    return $carry;
}, []))?>
</script>
<?php
require ROOT_SIFAS_WEB . '/common-b63adcdf/head2.php';
?>
<div class="eis-sif-page-button" onclick="showDialogSongs()">选择歌曲</div>
<div id="dialog-songs" title="选择歌曲" data-full=1>
<div id="dialog-songs-notice" class="eis-sif-notice eis-sif-hidden"></div>
<div id="g-songs" data-gallery-config=2001>
<div class="eis-sif-bar">
<span><i class="fas fa-search"></i> 搜索：<input type=text data-gallery-role=search /></span>
<span><span class="eis-sif-button-group tiny" data-click="changeGalleryFilter('#g-songs',1,$)" data-gallery-role=filter data-gallery-filter=1>
    <span data-click-arg=0>全部歌曲</span>
    <span data-click-arg=1>有标记歌曲</span>
</span></span>
<span>视图：<span class="eis-sif-button-group tiny" data-click="changeGalleryView('#g-songs',$);refreshSongTags()" data-gallery-role=views>
    <span data-click-arg=1>经典</span>
    <span data-click-arg=2>1.8</span>
</span></span>
<span>难度：<span class="eis-sif-button-group tiny" data-click="changeGalleryOption('#g-songs',1,$)" data-gallery-role=option data-gallery-option=1>
    <span data-click-arg=3>上级</span>
    <span data-click-arg=4>上级＋</span>
    <span data-click-arg=5>挑战</span>
</span></span>
<span><i class="fas fa-sort"></i> 排序：<span data-gallery-role=sort></span></span>
</div>
<div class="eis-sif-gallery" data-gallery-role=gallery></div>
</div>
</div>
<div id="detail" class="eis-sif-hidden">
<h2></h2>
<div id="titles"></div>
<div id="writers"></div>
<h3><i class="fas fa-tasks fa-lg"></i> 歌曲课题</h3>
<p class="eis-sif-note">※ 称号、背景等课题仅能完成一次。如已持有报酬，则不会再出现相应的课题。</p>
<div id="missions-focus" class="eis-sif-gallery"></div>
<div id="missions-container" class="eis-sif-fold">
<h4>更多课题</h4>
<div>
<p class="eis-sif-note">※ 现在暂不支持查看大礼包的内容。今后预定将追加支持。</p>
<div id="missions" class="eis-sif-gallery"></div>
</div>
</div>
<h3><i class="fas fa-history fa-lg"></i> 配信和活动历史</h3>
<span><i class="fas fa-server"></i> 服务器：<select id="events-server" onchange="showEvents()">
<option value=1 selected>日语版</option>
<option value=2>国际版</option>
</select></span>
<div id="events" class="eis-sif-gallery"></div>
<p id="events-none">此歌曲尚未于该服务器举办的活动中登场。</p>
<h3><i class="fas fa-list-alt fa-lg"></i> LIVE 资料</h3>
<section class="eis-sif-section">
<h4><i class="fas fa-hand-point-right fa-lg"></i> 选择难度</h4>
<div id="maps" class="eis-sif-gallery"></div>
</section>
<section id="map-detail" class="eis-sif-section">
<h4><i class="far fa-list-alt fa-lg"></i> LIVE 资料</h4>
<section class="eis-sif-subsection map-detail-type" data-type=5>
<h4 id="map-detail-tower-name"></h4>
<table class="map-detail-info map-detail-info-jump">
<tr><th>上一层</th><th>初回报酬</th><th>达成报酬</th><th>下一层</th></tr>
<tr>
<td id="map-detail-tower-prev"></td>
<td id="map-detail-tower-clear"></td>
<td id="map-detail-tower-progress"></td>
<td id="map-detail-tower-next"></td>
</tr>
</table>
</section>
<div class="eis-sif-info-gallery">
<div><?=HTML::dict('58','3b',tagName:'div')?><div class="map-data" data-index=2></div></div>
<div><?=HTML::dict('58','ib',tagName:'div')?><div class="map-data" data-index=3></div></div>
<div><div>消耗 LP</div><div class="map-data" data-index=5></div></div>
<div><div>获得经验值</div><div class="map-data" data-index=6></div></div>
<div><?=HTML::dict('58','8r',tagName:'div')?><div class="map-data" data-index=14></div></div>
<div><div><?=HTML::dict('58','hi')?>体力<?=HTML::dict('58','34')?></div><div class="map-data" data-index=15></div></div>
<div><div>表现<?=HTML::dict('58','xj').HTML::dict('58','vs')?>上限</div><div class="map-data" data-index=16></div></div>
<div><div><?=HTML::dict('58','co').HTML::dict('58','xj').HTML::dict('58','vs')?>上限</div><div class="map-data" data-index=17></div></div>
<div><div>技能<?=HTML::dict('58','xj').HTML::dict('58','vs')?>上限</div><div class="map-data" data-index=18></div></div>
<div><div>开工日期＊</div><div class="map-data" data-index=36 data-date=1 data-hide=1></div></div>
<div><div>完工日期＊</div><div class="map-data" data-index=37 data-date=1 data-hide=1></div></div>
<div><div><?=HTML::dict('58','hi')?>总数</div><div class="map-data" data-index=26 data-link=1 data-hide=1></div></div>
</div>
<p class="eis-sif-note">※ 关于部分标记＊的数据项，参见本页面帮助中的说明。本页面帮助链接位于页顶信息条中。</p>
<div class="map-chart-container"><canvas id="map-evaluation"></canvas></div>
<p class="map-data-string" data-index=20></p>
<p class="map-data-string" data-index=21></p>
<p class="map-data-string" data-index=22></p>
<p class="map-data-string" data-index=23></p>
<p class="eis-sif-note">※ 以下部分内容支持自动翻译成多种语言，详见页面顶部右上方设置。</p>
<?=HTML::dict('58','su',tagName:'h4')?>
<div id="map-notes" class="eis-sif-gallery"></div>
<p id="map-notes-none">此 LIVE 没有<?=HTML::dict('58','su')?>。</p>
<?=HTML::dict('58','7t',tagName:'h4')?>
<p class="eis-sif-note">※ <?=HTML::dict('58','7t')?>成功时<?=HTML::dict('58','xj').HTML::dict('58','vs')?>，失败时体力<?=HTML::dict('58','34')?>。</p>
<p><span data-flip-control=wave><span class="fa-stack" style="font-size:10px"><i class="far fa-circle fa-stack-2x"></i><i class="fas fa-sync-alt fa-stack-1x"></i></span></span></p>
<div id="map-waves" class="eis-sif-gallery"></div>
<p id="map-waves-none">此 LIVE 没有<?=HTML::dict('58','7t')?>。</p>
<h4>掉落道具</h4>
<div class="eis-sif-notice"><p><i class="fas fa-exclamation-circle"></i> 日替歌曲的掉落道具情报不准确，请以游戏内实际为准。</p></div>
<div class="eis-sif-notice"><p><i class="fas fa-exclamation-circle"></i> 除上述情况外，以下所列的掉落道具仅适用于日语版当前版本。</p></div>
<span id="map-drops-button" onclick="showDrops()"><i class="fas fa-cubes"></i> 显示掉落道具</span>
<table id="map-drops" class="eis-sif-table"></table>
</section>
</div>
<div class="eis-sif-hidden">
<div id="dialog-song-group-select" title="多版本歌曲选择提示" data-width=400>
<p>您选择了一首多版本歌曲。请您确认：</p>
<div id="dialog-song-group-songs" class="eis-sif-gallery"></div>
</div>
</div>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/foot.php';
