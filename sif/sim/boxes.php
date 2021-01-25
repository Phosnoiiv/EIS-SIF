<?php
namespace EIS\Lab\SIF;
$pageID = 17;
require_once dirname(__DIR__) . '/core/init.php';

$useSIFStyle = true;

require ROOT_SIF_WEB . '/common-d42c0d8a/head1.php';
echo HTML::css('boxes-d42c0d8a');
echo HTML::js('boxes-d42c0d8a');
echo HTML::css('boxes-sim');
echo HTML::js('boxes-sim');
?>
<script>
<?=Cache::read('boxes-sim.js')?>
</script>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/head2.php';
?>
<div id="maintab">
<ul>
<li><a href="#tab-index">首页</a></li>
<li><a href="#tab-box-history">社员</a></li>
<li><a href="#tab-box">招募</a></li>
<li><a href="#tab-shop">商店</a></li>
<li><a href="#tab-shop-history">其它</a></li>
</ul>
<div id="tab-index">
<section class="eis-sif-section">
<h4>欢迎</h4>
<p>在进入招募模拟前，请先在此选择初始设置。</p>
<p class="eis-sif-note">※ 所有模拟过程仅在当前浏览器标签页内有效。如果您刷新或关闭本页面，所有记录将清空。</p>
<div id="form-initial" class="eis-sif-form">
<p><label for="server">服务器</label><select id="server"><option value=1 selected>日语版</option><option value=3>简体字版</option><option value=2>国际版</option></select></p>
</div>
<p style="text-align:center"><span id="start" onclick="startConfirm()">开始模拟！</span></p>
</section>
</div>
<div id="tab-box">
<section id="panel-box-list" class="eis-sif-section">
<h4>招募列表</h4>
<p class="eis-sif-note">※ 本页面仅支持游戏内的一部分招募。本页面也包含一些游戏内实际并不存在的招募。</p>
<div class="eis-sif-pagebar" data-control="#boxes" data-size=15></div>
<ul id="boxes"></ul>
</section>
<div id="panel-box-main" class="eis-sif-hidden">
<h2 id="box-name"></h2>
<div id="box-time" class="eis-sif-timetip"></div>
<div id="box-rarities">
<table class="eis-sif-table"></table>
</div>
<div id="box-currencies"></div>
<div id="box-extra">
<span id="box-lineup" onclick="showLineup()">卡池内容</span>
<span id="box-sheet" onclick="showSheet()">招募印章</span>
<span id="box-select-member" onclick="selectMember()"><i class="fas fa-user-check"></i> 选择成员</span>
<span id="stepup-reset" onclick="resetStepupConfirm()">重置阶梯</span>
<span id="knapsack-reset" onclick="resetKnapsackConfirm()">重置 BOX</span>
<div id="box-bonus"></div>
</div>
<div id="box-series"></div>
<div id="box-ad"></div>
<div id="box-selection"></div>
<div id="box-buttons"></div>
</div>
<div class="ui-helper-clearfix"></div>
</div>
<div id="tab-box-history">
</div>
<div id="tab-shop">
</div>
<div id="tab-shop-history">
<div id="others" class="eis-jq-accordion">
<h4>道具列表</h4>
<div>
<div id="pocket"></div>
</div>
</div>
</div>
</div>
<div class="eis-sif-hidden">
<div id="dialog-confirm-start" title="即将开始招募模拟" data-width=400>
<p>确定以这个初始设置开始招募模拟吗？</p>
</div>
<div id="dialog-message-start" title="开始招募模拟" data-width=450>
<p>已开始招募模拟。</p>
<p>已跳转至“招募”页面。</p>
</div>
<div id="dialog-confirm-scout" title="招募确认" data-width=400>
<div class="eis-sif-dialog-head">
<span class="eis-sif-dialog-title dialog-box-title"></span>
</div>
<p>确定要进行 <span class="dialog-scout-count"></span> 次招募吗？</p>
<p class="dialog-scout-stepup-num">阶梯式招募：第 <span></span>/<span></span> 次</p>
<div class="dialog-confirm-scout-sheet">
<p>11 连限定　招募印章</p>
<div class="sheet-contents"></div>
<p class="eis-sif-note sheet-loop-note"></p>
</div>
<p>需要 <img class="dialog-confirm-scout-item"/>：<span id="dialog-confirm-scout-cost"></span></p>
<p>持有 <img class="dialog-confirm-scout-item"/>：<span id="dialog-confirm-scout-pocket"></span></p>
<p id="dialog-confirm-scout-insufficient">持有 <img class="dialog-confirm-scout-item"/> 数量不足！</p>
</div>
<div id="dialog-result-scout" title="招募结果" data-width=600>
<div class="eis-sif-dialog-head">
<span class="eis-sif-dialog-title dialog-box-title"></span>
</div>
<p>招募了 <span class="dialog-scout-count"></span> 位新社员！</p>
<p class="dialog-scout-stepup-num">阶梯式招募：第 <span></span>/<span></span> 次</p>
<div id="result"></div>
<table id="result-table" class="eis-sif-table">
<thead><tr><th>编号</th><th></th><th></th><th>名称</th><th colspan=2>技能</th><th>备注</th></tr></thead>
<tbody></tbody>
</table>
</div>
<div id="dialog-select-member" title="选择招募成员" data-width=400>
<p>请选择想要招募的成员：</p>
<div id="dialog-select-member-options"></div>
</div>
<table id="template-table-lineup" class="eis-sif-table lineup-table">
<tr><th>编号</th><th></th><th>名称</th><th colspan=2>技能</th><th>备注</th></tr>
</table>
<div id="dialog-sheet" title="11 连限定　招募印章" data-width=400>
<div class="eis-sif-dialog-head">
<span class="eis-sif-dialog-title dialog-box-title"></span>
</div>
<div class="sheet-contents"></div>
<p class="eis-sif-note sheet-loop-note"></p>
<p class="eis-sif-note">※ 获得全部印章前，不可手动重置印章。</p>
<p class="eis-sif-note">※ 招募印章的有效期限以游戏内实际为准。</p>
</div>
<div id="dialog-confirm-stepup-reset" title="确认重置阶梯" data-width=450>
<p>游戏内并不能重置阶梯，但是鉴于本页面为模拟，为了减少您刷新页面的麻烦，允许您重置阶梯。</p>
<p>确定要现在重置阶梯吗？</p>
</div>
<div id="dialog-message-stepup-reset" title="已重置阶梯" data-width=350>
<p>已重置本阶梯。</p>
</div>
<div id="dialog-confirm-knapsack-reset" title="确认重置 BOX" data-width=450>
<p>确定要现在重置 BOX 吗？</p>
<p class="eis-sif-note">※ 游戏内一部分 BOX 有重置限制。本页面为了减少您刷新页面的麻烦而允许您随意重置 BOX。</p>
</div>
<div id="dialog-message-knapsack-reset" title="已重置 BOX" data-width=350>
<p>已将 BOX 恢复至初始状态。</p>
</div>
<div id="dialog-income" title="获得新道具" data-width=500>
<p id="dialog-income-text"></p>
<div id="dialog-income-items"></div>
</div>
</div>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/boxes.php';
require ROOT_SIF_WEB . '/common-d42c0d8a/foot.php';
