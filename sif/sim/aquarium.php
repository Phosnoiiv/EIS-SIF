<?php
namespace EIS\Lab\SIF;
$pageID = 26;
require_once dirname(__DIR__) . '/core/init.php';

$playTypeID = 2;
$playCSS = ['play/aquarium'];
$playJS = ['play/aquarium'];

require ROOT_SIF_WEB . '/common-d42c0d8a/play-head.php';
?>
<div id="play-main">
<div id="board"></div>
<div id="panel">
<section class="eis-sif-subsection">
<h4 class="play-disp-mode"></h4>
<div class="eis-sif-info-gallery">
<div><div>天数</div><div class="play-disp-interval-primary"></div></div>
<div><div>时段</div><div class="play-disp-interval-sub"></div></div>
<div><div>活动点数</div><div class="play-disp-score"></div></div>
</div>
</section>
<section class="eis-sif-section-noborder">
<h4>控制<span class="play-section-flag play-flag-interval-primary"></span></h4>
<span class="eis-jq-button play-button-interval" onclick="playIntervalNext()">下一时段</span>
<span class="eis-jq-button" onclick="exchangeItem()">获取<img class="button-item-icon" src="/vio/sif/campaign/cut/aquarium/i2.png"/></span>
<span class="eis-jq-button" onclick="playQuitConfirm()">退出</span>
</section>
<section class="eis-sif-section-noborder">
<h4>游玩操作</h4>
<div id="actions">
<span id="button-free" class="eis-jq-button" onclick="useFree()">使用<img class="button-item-icon" src="/vio/sif/campaign/cut/aquarium/i1.png"/><span id="flag-free" class="eis-sif-flag"></span></span>
<span class="eis-jq-button" onclick="useItem()">使用<img class="button-item-icon" src="/vio/sif/campaign/cut/aquarium/i2.png"/><span id="flag-item" class="eis-sif-flag static"></span></span>
<span class="eis-jq-button" onclick="useRefresh()">使用<img class="button-item-icon" src="/vio/sif/campaign/cut/aquarium/i3.png"/><span id="flag-refresh" class="eis-sif-flag static"></span></span>
<div id="actions-disabled">请先选择一个印章</div>
</div>
</section>
<section class="eis-sif-section-noborder">
<h4>信息</h4>
<p id="history-yesterday"></p>
</section>
<section class="eis-sif-section-noborder">
<h4>人气印章收藏</h4>
<p id="history-popularity"></p>
<p><span id="history-popularity-goal"></span><span id="button-history-popularity-reward" class="eis-jq-button" onclick="receiveHistoryPopularityReward()">领取奖励</span></p>
</section>
</div>
</div>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/play-foot.php';
