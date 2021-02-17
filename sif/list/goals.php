<?php
namespace EIS\Lab\SIF;
$pageID = 14;
require_once dirname(__DIR__) . '/core/init.php';
include ROOT_SIF_CACHE . '/goals.php';

$useSIFStyle = true;
$latestFile = ROOT_SIF_CACHE . '/goals.js';
$barContentsAppend = [
    '<i class="fas fa-server"></i> 服务器：<select id="server" onchange="produce()"><option value="1" selected>日语版</option><option value="3">简体字版</option></select>',
];

require ROOT_SIF_WEB . '/common-d42c0d8a/head1.php';
echo HTML::css('goals');
echo HTML::js('goals');
?>
<style>
.sif-goal-tag.tag-6.server-1 {
    mask: url("/vio/sif/<?=$cacheTags[6][0]?>.png") center/48px;
    -webkit-mask: url("/vio/sif/<?=$cacheTags[6][0]?>.png") center/48px;
}
.sif-goal-tag.tag-6.server-3 {
    mask: url("/vio/sif/<?=$cacheTags[6][1]?>.png") center/48px;
    -webkit-mask: url("/vio/sif/<?=$cacheTags[6][1]?>.png") center/48px;
}
</style>
<script>
<?=Cache::read('goals.js')?>
</script>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/head2.php';
?>
<div class="eis-jq-tabs">
<ul>
<li><a href="#tab-recent">近期限时课题</a></li>
<li><a href="#tab-help">页面说明</a></li>
</ul>
<div id="tab-recent">
<p class="eis-sif-note">※ 课题分组标签在部分浏览器上可能无法显示。</p>
<div id="goals"></div>
</div>
<div id="tab-help">
<p>由于部分课题在查卡器中不显示文本，特建本页面以供查询此类限时课题的要求。</p>
<p>本页面不显示各课题的前置条件等信息，此类信息请去查卡器查询。</p>
<p>本页面仅收录近期的限时课题，不收录过去的限时课题和常驻课题。</p>
<p>虽然本页面可以自动分析课题数据，但是数据源是手动提取的，时效性不及查卡器。</p>
<p>国际版的限时课题一般都可在查卡器中查询文本，本页面仅提供日语版和简体字版的数据。</p>
</div>
</div>
<?php
require ROOT_SIF_WEB . '/common-4fcb29e1/foot.php';
