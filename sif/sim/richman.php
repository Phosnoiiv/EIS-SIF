<?php
namespace EIS\Lab\SIF;
$pageID = 25;
require_once dirname(__DIR__) . '/core/init.php';

$playTypeID = 1;
$playCSS = ['play/richman'];
$playJS = ['play/richman'];

require ROOT_SIF_WEB . '/common-d42c0d8a/play-head.php';
?>
<div id="play-main">
<div id="map"></div>
<div id="panel">
<section class="eis-sif-subsection">
<h4 class="play-disp-mode"></h4>
<div class="eis-sif-info-gallery">
<div><div>天数</div><div class="play-disp-interval-primary"></div></div>
<div><div>活动点数</div><div class="play-disp-score"></div></div>
<div><div>特殊收集物</div><div class="inv-seal"></div></div>
</div>
</section>
<section class="eis-sif-section-noborder">
<h4>控制</h4>
<span class="eis-jq-button" onclick="playIntervalNext()">下一天<span class="play-flag-interval-primary eis-sif-flag static"></span></span>
<span class="eis-jq-button" onclick="useFree()">随机前进<span id="flag-free" class="eis-sif-flag"></span></span>
<span class="eis-jq-button" onclick="exchangeItem()">获取<img class="button-item-icon icon-item"/></span>
<span class="eis-jq-button" onclick="playQuitConfirm()">退出</span>
</section>
<section class="eis-sif-section-noborder">
<h4>指定前进<span id="flag-item" class="play-section-flag"></span></h4>
<div id="actions-item"></div>
<div></div>
</section>
</div>
</div>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/play-foot.php';
