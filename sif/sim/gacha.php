<?php
namespace EIS\Lab\SIF;
$pageID = 28;
require_once dirname(__DIR__).'/core/init.php';

require ROOT_SIF_WEB.'/common-0d763169/head1.php';
echo HTML::css('boxes-sim');
echo HTML::css('play/gacha');
echo HTML::js('play/play-d42c0d8a');
echo HTML::js('play/gacha');
?>
<script>
<?=Cache::read('gacha-sim.js')?>
</script>
<?php
require ROOT_SIF_WEB.'/common-0d763169/head2.php';
?>
<div id="page-gacha" class="page">
<h2 id="gacha-title"></h2>
<?php
require ROOT_SIF_DOC.'/temp/gacha.html';
?>
<div id="gacha-ne">
<div id="cost-switch" class="eis-sif-button-group sif-button-group">
<span onclick="switchCosts(1)">爱心</span>
<span onclick="switchCosts(2)">招募券</span>
</div>
</div>
<div id="gacha-se">
<div id="cost-buttons"></div>
<div id="select-button"></div>
</div>
<div id="gacha-sw">
<section id="box-remain">
<h4><span>BOX 剩余</span><span class="box-remain-sum"></span></h4>
<div id="box-remain-list"></div>
</section>
<div id="box-reset"></div>
<div><span id="button-list" class="eis-jq-button" onclick="showFullDialog('#dialog-list')">招募列表</span></div>
</div>
</div>
<div id="page-result" class="page">
<div id="results"></div>
<div id="result-buttons">
<span class="eis-jq-button" onclick="closeResult()">OK</span>
</div>
</div>
<div class="eis-sif-hidden">
<div id="dialog-list" class="eis-sif-dialog-init" title="招募列表" data-full=1>
<div class="eis-sif-button-group sif-button-group">
<span onclick="switchListProject(0)" data-default=1>全部</span>
<span onclick="switchListProject(3)">虹咲</span>
<span onclick="switchListProject(4)">Liella!</span>
</div>
<div id="gacha-list" class="eis-sif-gallery"></div>
</div>
<div id="dialog-select" data-width=650>
<p id="dialog-select-caption" class="dialog-caption"></p>
<div id="dialog-select-choices" class="eis-sif-gallery"></div>
</div>
<div id="dialog-confirm-draw" data-width=500>
<p class="dialog-caption gacha-name"></p>
<div class="eis-sif-item-diff-container gacha-cost-diff"></div>
</div>
</div>
<?php
require ROOT_SIF_WEB.'/common-d42c0d8a/boxes.php';
require ROOT_SIF_WEB.'/common-0d763169/foot.php';
