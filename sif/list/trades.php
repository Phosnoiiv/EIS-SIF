<?php
namespace EIS\Lab\SIF;
$pageID = 10;
require_once dirname(__DIR__) . '/core/init.php';

$latestFile = ROOT_SIF_CACHE . '/trades.js';
$relatedPage = [
    'href' => 'list/trades.php',
    'name' => 'SIFAS 活动交换所',
];
require ROOT_SIF_WEB . '/common-d42c0d8a/head1.php';
echo HTML::css('trades');
echo HTML::js('trades');
?>
<script>
<?=Cache::read('trades.js')?>
</script>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/head2.php';
?>
<div class="eis-sif-content eis-sif-mark-top">
<p class="eis-sif-note">※ 从下列横幅中选择一个交换所以查看详细内容。横幅中文字的语言即表明该活动所在的服务器。</p>
<div id="trades" class="eis-sif-gallery"></div>
</div>
<div id="trade-detail"></div>
<aside class="eis-sif-hidden">
<section class="eis-sif-section">
<h4>道具</h4>
<div id="currencies"></div>
<h4>一键设定计划</h4>
<button type="button" onclick="setPlanFull()">交换全部</button>
<button type="button" onclick="setPlanEmpty()">清空计划</button>
<span class="plan" data-type=5100><img src="/vio/sif/type/5100s.png"/>称号：<button type="button" onclick="setPlanFull(5100)">全选</button><button type="button" onclick="setPlanEmpty(5100)">清空</button></span>
<span class="plan" data-type=5200><img src="/vio/sif/type/5200s.png"/>背景：<button type="button" onclick="setPlanFull(5200)">全选</button><button type="button" onclick="setPlanEmpty(5200)">清空</button></span>
<p class="eis-sif-note">※ “交换全部”将忽略无交换次数限制的交换品。</p>
</section>
</aside>
<div id="categories">
<ul id="trade-tabs-nav"></ul>
</div>
<p class="eis-sif-note">※ 鼠标悬停或触屏点击各处道具图片，可以查看道具名称。</p>
<div id="eis-sif-side-top" class="eis-sif-hidden">返回顶部</div>
<?php
require ROOT_SIF_WEB . '/common-4fcb29e1/foot.php';
