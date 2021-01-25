<?php
namespace EIS\Lab\SIFAS;
$pageID = 11;
require_once dirname(__DIR__) . '/core/init.php';

$latestFile = ROOT_SIFAS_CACHE . '/trades.js';
$relatedPage = [
    'href' => 'list/trades.php',
    'name' => 'SIF 活动交换所',
];
require ROOT_SIFAS_WEB . '/common-b63adcdf/head1.php';
echo HTML::css('trades');
echo HTML::js('trades');
?>
<script>
<?=Cache::read('trades.js')?>
</script>
<?php
require ROOT_SIFAS_WEB . '/common-b63adcdf/head2.php';
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
<span class="plan" data-type=3>Idol：<button type="button" onclick="setPlanFull(3)">全选</button><button type="button" onclick="setPlanEmpty(3)">清空</button></span>
<span class="plan" data-type=26>背景：<button type="button" onclick="setPlanFull(26)">全选</button><button type="button" onclick="setPlanEmpty(26)">清空</button></span>
<span class="plan" data-type=12>特训用：<button type="button" onclick="setPlanFull(12)">全选</button><button type="button" onclick="setPlanEmpty(12)">清空</button></span>
<span class="plan" data-type=6>合宿用：<button type="button" onclick="setPlanFull(6)">全选</button><button type="button" onclick="setPlanEmpty(6)">清空</button></span>
<p class="eis-sif-note">※ “交换全部”将忽略无交换次数限制的交换品。</p>
</section>
</aside>
<div id="categories" class="eis-sif-content eis-sif-hidden"></div>
<?php
require ROOT_SIF_WEB . '/common-4fcb29e1/foot.php';
