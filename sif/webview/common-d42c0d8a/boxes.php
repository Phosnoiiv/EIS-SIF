<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;
?>
<div class="eis-sif-hidden">
<div id="box-detail" title="招募详情" data-width=600 data-full=1>
<div class="eis-sif-dialog-head">
<span class="eis-sif-dialog-title dialog-box-title"></span>
</div>
<div id="box-detail-tabs" class="eis-jq-tabs" data-scroll=1>
<ul>
<li id="box-detail-card-rate"><a href="#box-detail-tab-rate">提供概率</a></li>
<li id="box-detail-card-lineup"><a href="#box-detail-tab-lineup">招募范围</a></li>
<li id="box-detail-card-si"><a href="#box-detail-tab-si">SI 技能</a></li>
</ul>
<div id="box-detail-tab-rate">
</div>
<div id="box-detail-tab-lineup">
<p class="eis-sif-note">※ 技能触发条件中，“C”表示连击数触发，“N”表示图标数触发，“P”表示 PERFECT 数触发，“T”表示时间触发。</p>
<div id="box-detail-lineup" class="dialog-lineup"></div>
</div>
<div id="box-detail-tab-si">
<p class="box-detail-caption">没有专用 SI 技能随附的情况下，根据稀有度随机地随附以下 SI 技能</p>
<div id="box-detail-si"></div>
</div>
</div>
</div>
</div>
