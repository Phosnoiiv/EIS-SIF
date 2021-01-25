<?php
namespace EIS\Lab\SIFAS;
$pageID = 23;
require_once dirname(__DIR__) . '/core/init.php';

$latestFile = ROOT_SIFAS_CACHE . '/goals.js';
require ROOT_SIFAS_WEB . '/common-b63adcdf/head1.php';
echo HTML::css('goals');
echo HTML::js('goals');
?>
<script>
<?=Cache::read('goals.js')?>
</script>
<?php
require ROOT_SIFAS_WEB . '/common-b63adcdf/head2.php';
?>
<div class="eis-sif-page-button" onclick="showDialogGroup()">选择课题组</div>
<div id="dialog-server" class="eis-sif-dialog-init" title="选择服务器" data-width=350>
<p>请选择要查看活动课题数据的服务器：</p>
<div id="servers" class="eis-sif-gallery"></div>
</div>
<div id="dialog-group" class="eis-sif-dialog-init" title="选择课题组" data-full=1>
<p class="eis-sif-button-line"><span class="eis-jq-button" onclick="showDialogServer()">切换服务器</span></p>
<div id="groups-container" class="eis-jq-accordion" data-expand=1 data-immediate=1>
<h4>当前活动课题</h4>
<div>
<p class="eis-sif-button-line"><span id="button-current" class="eis-jq-button">当前活动课题</span></p>
<p class="eis-sif-note">※ 只要某一课题组内至少有一个课题尚未截止，则将显示该组内的全部课题。</p>
</div>
<h4>剧情活动、交换所活动课题</h4>
<div>
<div class="eis-sif-pagebar" data-control="#events" data-size=12></div>
<div id="events" class="eis-sif-gallery"></div>
</div>
<h4>SBL、DLP 活动课题</h4>
<div>
<div id="subevents" class="eis-sif-gallery"></div>
</div>
<h4>其它活动课题</h4>
<div>
<div id="others" class="eis-sif-gallery"></div>
</div>
</div>
</div>
<p class="eis-sif-note">※ 点击礼盒图标可以查看礼盒内容。</p>
<p class="eis-sif-note">※ 称号、背景等课题仅能完成一次。如已持有报酬，则不会再出现相应的课题。</p>
<div id="goals"></div>
<div class="eis-sif-hidden">
<div id="dialog-pack" title="礼盒内容" data-width=500>
<div id="dialog-pack-items"></div>
</div>
</div>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/foot.php';
