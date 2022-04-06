<?php
namespace EIS\Lab\SIF;
$pageID = 21;
require_once dirname(__DIR__) . '/core/init.php';

$helpArticle = 35;
$barContentsAppend = [
    '<input id="log-control" type="checkbox"/><label for="log-control">生成调试信息</label>',
];

require ROOT_SIF_WEB . '/common-d42c0d8a/head1.php';
echo HTML::css('tool-event');
echo HTML::js('tool-event');
?>
<script>
<?=Cache::read('event-current.js')?>
<?=Cache::read('tool-event.js')?>
</script>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/head2.php';
$buttonCurrentEvent = '<span class="eis-jq-button" onclick="showCurrentEvents()">当前活动</span>';
?>
<p class="eis-sif-note">※ 仅目标计算模式会生成调试信息。当目标 pt 较高时，生成调试信息可能导致计算速度变慢。</p>
<section class="eis-sif-section">
<h4><i class="fas fa-cogs"></i> 基础配置</h4>
<div class="eis-sif-form">
<p><label for="type">活动类型</label><select id="type" onchange="changeType()"></select><?=$buttonCurrentEvent?></p>
<p class="eis-sif-note">※ 当前暂未支持全部活动类型。</p>
<p class="eis-sif-note">※ Icon Collection 活动不提供控分计算。</p>
<p><label for="gained">当前活动 pt</label><input id="gained" type="number" min="0" value="0"/></p>
<p><label for="goal">目标活动 pt</label><input id="goal" type="number" min="0" value="0"/></p>
<p class="eis-sif-note">※ 如无目标，欲计算平刷活动 pt，可将目标填写为 0。</p>
</div>
</section>
<div id="tabs-main" class="eis-jq-tabs">
<ul>
<li><a href="#tab-goal">目标计算</a></li>
<li><a href="#tab-adjust">控分计算</a></li>
</ul>
<div id="tab-goal">
<section class="eis-sif-section">
<h4><i class="fas fa-cogs"></i> 基础配置</h4>
<div class="eis-sif-form">
<p><label for="rank">当前等级</label><input id="rank" type="number" min="2" max="1100" value="300" onchange="changeRank()"/></p>
<p><label for="exp">当前经验</label><input id="exp" type="number" min="0" value="0"/> /<span id="exp-rank"></span></p>
<p class="eis-sif-note">※ 1000 级以后的升级经验有微小误差。</p>
<p><label for="time">剩余时间</label><input id="time" type="number" min="0" value="239"/> 小时 <?=$buttonCurrentEvent?></p>
<p><label for="lp">剩余 LP</label><input id="lp" type="number" min="0" value="175"/> /<span id="lp-rank"></span></p>
<p><label for="collected">当前活动图标数</label><input id="collected" type="number" min="0" value="0"/></p>
</div>
</section>
<section class="eis-sif-section">
<h4><i class="fas fa-gamepad"></i> 玩法配置</h4>
<div class="eis-sif-form">
<div id="config-play"></div>
</div>
</section>
<section class="eis-sif-section">
<h4><i class="fas fa-server"></i> 活动配置</h4>
<p class="eis-sif-note">※ 当前只支持覆盖活动全程的演唱会应援活动。当前暂未支持御守。</p>
<div class="eis-sif-form">
<div id="config-campaign"></div>
<div id="config-lucky"></div>
</div>
</section>
<section class="eis-sif-section">
<h4><i class="fas fa-plus-square"></i> 加成配置</h4>
<div id="yell-choices" class="eis-sif-gallery"></div>
<table><tr>
<td><div class="eis-sif-info-gallery"><div><div>效果：活动 pt</div><div id="yell-effect"></div></div></div></td>
<td><div id="yell-chosen" class="eis-sif-gallery"></div></td>
</tr></table>
</section>
<p style="text-align:center"><span id="calculate-button" onclick="calculate()"><i class="fas fa-calculator"></i> 计算！</span></p>
<section id="result" class="eis-sif-section eis-sif-hidden">
<h4><i class="fas fa-flag"></i> 计算结果</h4>
<p class="eis-sif-note">※ Medley Festival、Challenge Festival、散步拉力赛活动的“平均情况”是指随机应援按“活动配置”中输入的数值计算，“最差情况”是指完全无随机应援。</p>
<p class="eis-sif-note">※ Icon Collection、Score Match、友情大合战活动不区分三种情况。</p>
<p class="eis-sif-note">※ Icon Collection、Score Match、友情大合战活动不提供多倍 LP 消费的计算，对除演唱会次数以外的其它所有结果均无影响，如您使用多倍 LP 消费，自行将演唱会次数除以倍数即可。</p>
<table class="eis-sif-table">
<tr><th></th><th>平均情况</th><th>最好情况</th><th>最差情况</th></tr>
<tr id="result-free-pt"><th>平刷活动 pt</th><td></td><td></td><td></td></tr>
<tr id="result-free-rank"><th>平刷等级提升</th><td></td><td></td><td></td></tr>
<tr id="result-free-count"><th>平刷演唱会次数</th><td></td><td></td><td></td></tr>
<tr id="result-lp"><th>尚缺 LP</th><td></td><td></td><td></td></tr>
<tr id="result-loveca"><th>折算爱心</th><td></td><td></td><td></td></tr>
<tr id="result-pt"><th>最终活动 pt</th><td></td><td></td><td></td></tr>
<tr id="result-rank"><th>等级提升</th><td></td><td></td><td></td></tr>
<tr id="result-count"><th>演唱会次数</th><td></td><td></td><td></td></tr>
</table>
</section>
</div>
<div id="tab-adjust">
<section class="eis-sif-section">
<h4><i class="fas fa-plus-square"></i> 加成配置</h4>
<p>请输入您当前持有的各类加成数值的社员总数。</p>
<div id="yell-stores" class="eis-sif-form"></div>
</section>
<p class="eis-sif-button-line"><span class="eis-jq-button" onclick="calculateAdjust()"><i class="fas fa-calculator"></i> 计算！</span></p>
<section id="result-section-adjust" class="eis-sif-section eis-sif-hidden">
<h4><i class="fas fa-flag"></i> 计算结果</h4>
<div class="eis-sif-notice"><p><i class="fas fa-exclamation-circle"></i> 本站的控分计算器尚未经过充分测试，存在出错的可能性，请谨慎使用。</p></div>
<div class="eis-sif-notice event-specific" data-event3=1><p><i class="fas fa-exclamation-circle"></i> Medley Festival 活动控分时，如果计算器给出<b>同一场内混搭不同难度</b>的步骤，除非您非常清楚相关机制，否则不要交换前后顺序，以免控分失败！</p><p class="eis-sif-note">例如，如果计算器给出“选择 2 曲并按顺序选择 EASY，HARD 难度”，不要擅自更换成第 1 曲 HARD、第 2 曲 EASY。</p></div>
<p class="event-specific" data-event3=1>另请您根据抽选到的歌曲，自行提前查找资料计算好所需的连击数。</p>
<div id="result-adjust"></div>
</section>
</div>
</div>
<div id="log" class="eis-sif-fold eis-sif-hidden">
<h4>调试信息</h4>
<div>
<div id="log-set" class="log"></div>
<div id="log-best" class="log"></div>
<div id="log-worst" class="log"></div>
</div>
</div>
<div class="eis-sif-hidden">
<div id="dialog-current" class="eis-sif-dialog-init" title="当前活动" data-width=600>
<ul id="dialog-current-events"></ul>
<p class="eis-sif-note">※ 将根据选定的活动，自动填写“活动类型”和“剩余时间”。</p>
</div>
<div id="dialog-yell-full" title="加成配置" data-width=350>
<p>最多只能选择 5 名社员提供加成。</p>
</div>
</div>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/foot.php';
