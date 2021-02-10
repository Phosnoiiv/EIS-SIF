<?php
if (!defined('EIS_ENV'))
    exit;
?>
<div class="eis-sif-hidden">
<div id="play-dialog-end" title="<?=$title?>" data-width=500>
<div class="eis-sif-dialog-head">游戏结束</div>
<p id="play-dialog-end-score"></p>
</div>
<div id="play-dialog-quit-confirm" title="<?=$title?>" data-width=300>
<p>确定要退出当前游戏吗？</p>
</div>
<div id="play-dialog-add" title="<?=$title?>" data-width=500>
<div id="play-dialog-add-info"></div>
<div id="play-dialog-add-diff" class="eis-sif-item-diff-container"></div>
<section id="play-dialog-add-score">
<h4 class="eis-sif-dialog-section-header">获得活动点数</h4>
<ul id="play-dialog-add-score-details"></ul>
</section>
<div id="play-dialog-add-container"></div>
</div>
<div id="play-dialog-exchange" title="<?=$title?>" data-width=500>
<div id="play-dialog-exchange-img"></div>
<p class="eis-sif-button-line"><input id="play-dialog-exchange-amount" type="number" min=0 oninput="playExchangeInput()"/></p>
<div id="play-dialog-exchange-diff" class="eis-sif-item-diff-container"></div>
</div>
</div>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/foot.php';
