<?php
namespace EIS\Lab\SIF;
$pageID = 13;
require_once dirname(__DIR__) . '/core/init.php';

$latestFile = ROOT_SIF_CACHE . '/event.rc.js';
$barContentsAppend = [
    '<i class="fas fa-language"></i> 歌名显示语言：<select id="bar-track-lang" onchange="trackLang(this.value)"><option value="1" selected>原名</option><option value="3">简体字版译名</option><option value="2">英语版译名</option><option value="4">萌娘百科词条名</option></select>',
];

require ROOT_SIF_WEB . '/common-d42c0d8a/head1.php';
echo HTML::css('rc');
echo HTML::js('rc');
?>
<script>
<?=Cache::read('event.rc.js')?>
</script>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/head2.php';
?>
<div class="eis-jq-tabs">
<ul>
<li><a href="#tab-carnival">按活动</a></li>
<li><a href="#tab-track">按歌曲</a></li>
</ul>
<div id="tab-carnival">
<table id="carnivals" class="eis-sif-table">
<thead>
<tr><th>#</th><th></th><th></th><th></th></tr>
</thead>
<tbody></tbody>
</table>
</div>
<div id="tab-track">
<div class="eis-sif-bar">
<span><i class="fas fa-search"></i> 快速搜索：<input id="search-track" type="text" oninput="filterTrack()"/></span>
<span><i class="fas fa-filter"></i> 筛选：
<img id="filter-category-1" src="/vio/sif/member/c1.png" class="eis-sif-switch" data-switch=1 alt="μ's" title="μ's"/>
<img id="filter-category-2" src="/vio/sif/member/c2.png" class="eis-sif-switch" data-switch=1 alt="Aqours" title="Aqours"/>
<img id="filter-attribute-1" src="/vio/sif/icon/a1.png" class="eis-sif-switch" data-switch=1 alt="Smile" title="Smile"/>
<img id="filter-attribute-2" src="/vio/sif/icon/a2.png" class="eis-sif-switch" data-switch=1 alt="Pure" title="Pure"/>
<img id="filter-attribute-3" src="/vio/sif/icon/a3.png" class="eis-sif-switch" data-switch=1 alt="Cool" title="Cool"/>
</span>
<span><i class="fas fa-sort"></i> 排序：<select id="sort-track" onchange="sortTrack()">
<option value=0 selected>默认顺序</option>
<option value=1>EXPERT 图标数</option>
<option value=2>MASTER 图标数</option>
<option value=3>JP 登场次数</option>
<option value=4>WW 登场次数</option>
</select><select id="sort-track-direction" onchange="sortTrack()">
<option value=1 selected>升序</option>
<option value=-1>降序</option>
</select></span>
</div>
<p class="eis-sif-note">※ 可使用支持的任一语言搜索歌名。英文不区分大小写。</p>
<p class="eis-sif-note eis-sif-hidden sort-info" data-sort=2>※ 粉色标签的为滑键谱面。</p>
<p class="eis-sif-note eis-sif-hidden sort-info" data-sort=3>※ 如多次活动使用完全相同的歌单，相关歌曲的登场次数不会重复计算。</p>
<p class="eis-sif-note eis-sif-hidden sort-info" data-sort=4>※ 如多次活动使用完全相同的歌单，相关歌曲的登场次数不会重复计算。</p>
<ul id="tracks"></ul>
</div>
</div>
<div id="dialog-carnival" class="eis-sif-hidden" title="活动详情" data-width=550>
<div class="dialog-title"></div>
<p class="eis-sif-note dialog-info"></p>
<table class="carnival-table eis-sif-table">
<thead>
<tr><th>#</th><th>歌曲</th><th>EXPERT</th></tr>
</thead>
<tbody></tbody>
</table>
<p class="eis-sif-note">※ 本窗口内的各歌名也可点击。</p>
<p class="eis-sif-note">※ 本页面所示数据可能有误，欢迎反馈，反馈方式见本站主页告知。</p>
</div>
<div id="dialog-track" class="eis-sif-hidden" title="歌曲详情" data-width=450>
<div class="dialog-title">
<div class="dialog-title-tags"></div>
</div>
<div class="eis-sif-dialog-info"></div>
<table class="track-table eis-sif-table">
<thead>
<tr><th>#</th><th>活动</th><th>日期</th></tr>
</thead>
<tbody></tbody>
</table>
<p class="eis-sif-note">※ 本窗口内的各活动名也可点击。</p>
<p class="eis-sif-note">※ 本页面所示数据可能有误，欢迎反馈，反馈方式见本站主页告知。</p>
</div>
<?php
require ROOT_SIF_WEB . '/common-4fcb29e1/foot.php';
