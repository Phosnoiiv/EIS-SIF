<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
$pageID = 15;
require_once dirname(__DIR__) . '/core/init.php';

$latestFile = ROOT_SIFAS_CACHE . '/voices.js';

require ROOT_SIFAS_WEB . '/common-b63adcdf/head1.php';
echo HTML::css('voices');
echo HTML::js('voices');
?>
<script>
<?=Cache::read('voices.js')?>
</script>
<?php
require ROOT_SIFAS_WEB . '/common-b63adcdf/head2.php';
?>
<div class="eis-jq-tabs eis-sif-mark-top">
<ul>
<li><a href="#tab-member">按成员</a></li>
<li><a href="#tab-category">按类别</a></li>
</ul>
<div id="tab-member">
<div id="members" class="eis-sif-gallery"></div>
<p class="eis-sif-note">※ 各方块的颜色，以及所列各语音框的颜色，均不代表成员应援色。</p>
</div>
<div id="tab-category">
<div id="categories" class="eis-sif-gallery"></div>
<p class="eis-sif-note">※ 服装限定语音和卡片特训解锁语音不在本栏显示，此类语音请按成员查询。</p>
</div>
</div>
<section id="section-voices" class="eis-sif-section eis-sif-hidden">
<h4></h4>
<p class="eis-sif-note">※ 鼠标悬停或触屏点击各语音框，可以查看英语、简体中文和繁体中文文本。</p>
<div id="voices"></div>
</section>
<div id="eis-sif-side-top" class="eis-sif-hidden">返回顶部</div>
<?php
require ROOT_SIF_WEB . '/common-4fcb29e1/foot.php';
