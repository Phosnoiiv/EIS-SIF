<?php
namespace EIS\Lab\SIFAS;
$pageID = 20;
require_once dirname(__DIR__) . '/core/init.php';

$helpArticle = 30;
$latestFile = ROOT_SIFAS_CACHE . '/live-detail.js';
require ROOT_SIFAS_WEB . '/common-b63adcdf/head1.php';
echo HTML::css('live-detail');
echo HTML::js('live-detail');
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js" integrity="sha256-R4pqcOYV8lt7snxMQO/HSbVCFRPMdrhAFMH+vr9giYI=" crossorigin="anonymous"></script>
<script>
<?=Cache::read('live-detail.js')?>
<?=Cache::read('words.js')?>
</script>
<?php
require ROOT_SIFAS_WEB . '/common-b63adcdf/head2.php';
?>
<div class="eis-sif-page-button" onclick="showDialogSongs()">选择歌曲</div>
<div id="dialog-songs" title="选择歌曲" data-full=1>
<div class="eis-sif-bar">
<span><i class="fas fa-search"></i> 快速搜索：<input id="search-song" type="text" oninput="filterSong()"/></span>
<span><i class="fas fa-sort"></i> 排序：<select id="sort-song" onchange="sortSong()">
<option value=2 selected>推荐顺序</option>
<option value=1>游戏内默认顺序</option>
<option value=0>歌曲 ID</option>
<option value=4>上级目标张力</option>
<option value=5>上级建议 LIVE 力</option>
<option value=6>上级建议体力</option>
<option value=7>上级节奏图示消耗体力</option>
<option value=16>上级＋目标热度</option>
</select><select id="sort-song-direction" onchange="sortSong()">
<option value=1 selected>升序</option>
<option value=-1>降序</option>
</select></span>
</div>
<div id="dialog-songs-notice" class="eis-sif-notice eis-sif-hidden"></div>
<p class="eis-sif-note">※ 彩色信息标签表示该歌曲为 3D 歌曲，灰色为 2D 歌曲。</p>
<p class="eis-sif-note">※ 快速搜索支持使用原名、简体字版译名、英语版译名。英文不区分大小写。</p>
<div id="search-song-none" class="eis-sif-notice eis-sif-hidden"><p><i class="fas fa-exclamation-circle"></i> 未找到符合条件的歌曲。请尝试更换搜索文本。</p></div>
<div id="songs" class="eis-sif-gallery"></div>
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
<div><div>建议 LIVE 力</div><div class="map-data" data-index=2></div></div>
<div><div>建议体力</div><div class="map-data" data-index=3></div></div>
<div><div>消耗 LP</div><div class="map-data" data-index=5></div></div>
<div><div>获得经验值</div><div class="map-data" data-index=6></div></div>
<div><div>组合技量表最大值</div><div class="map-data" data-index=14></div></div>
<div><div>节奏图示消耗体力</div><div class="map-data" data-index=15></div></div>
<div><div>表现累积张力上限</div><div class="map-data" data-index=16></div></div>
<div><div>组合技累积张力上限</div><div class="map-data" data-index=17></div></div>
<div><div>技能累积张力上限</div><div class="map-data" data-index=18></div></div>
<div><div>开工日期＊</div><div class="map-data" data-index=36 data-date=1 data-hide=1></div></div>
<div><div>完工日期＊</div><div class="map-data" data-index=37 data-date=1 data-hide=1></div></div>
<div><div>节奏图示总数</div><div class="map-data" data-index=26 data-link=1 data-hide=1></div></div>
</div>
<p class="eis-sif-note">※ 关于部分标记＊的数据项，参见本页面帮助中的说明。本页面帮助链接位于页顶信息条中。</p>
<div class="map-chart-container"><canvas id="map-evaluation"></canvas></div>
<p class="map-data-string" data-index=20></p>
<p class="map-data-string" data-index=21></p>
<p class="map-data-string" data-index=22></p>
<p class="map-data-string" data-index=23></p>
<h4>节奏图示特效</h4>
<div id="map-notes" class="eis-sif-gallery"></div>
<p id="map-notes-none">此 LIVE 没有节奏图示特效。</p>
<h4>表现时间</h4>
<p class="eis-sif-note">※ 表现时间成功时张力 UP，失败时消耗体力。</p>
<div id="map-waves" class="eis-sif-gallery"></div>
<p id="map-waves-none">此 LIVE 没有表现时间。</p>
<h4>掉落道具</h4>
<div class="eis-sif-notice"><p><i class="fas fa-exclamation-circle"></i> 日替歌曲的掉落道具情报不准确，请以游戏内实际为准。</p></div>
<div class="eis-sif-notice"><p><i class="fas fa-exclamation-circle"></i> 除上述情况外，以下所列的掉落道具仅适用于日语版当前版本。</p></div>
<span id="map-drops-button" onclick="showDrops()"><i class="fas fa-cubes"></i> 显示掉落道具</span>
<table id="map-drops" class="eis-sif-table"></table>
</section>
</div>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/foot.php';
