<?php
namespace EIS\Lab\SIF;
$pageID = 9;
require_once dirname(__DIR__) . '/core/init.php';

$latestFile = ROOT_SIF_CACHE . '/box.stepups.js';
require ROOT_SIF_WEB . '/common-d42c0d8a/head1.php';
echo HTML::css('boxes');
echo HTML::css('steps');
echo HTML::js('boxes');
echo HTML::js('stepups');
?>
<script>
<?=Cache::read('box.stepups.js')?>
</script>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/head2.php';
?>
<ul id="list"></ul>
<div id="dialog-stepup" class="eis-jq-dialog dialog-box" title="阶梯招募详情">
<div class="eis-sif-dialog-head">
<div class="eis-sif-dialog-tag"></div>
<span class="eis-sif-dialog-title"></span>
</div>
<div id="dialog-stepup-main"></div>
<p>※ 本页面正在开发中，页面数据和功能均可能存在错漏，页面样式和功能均有再次修改的可能。</p>
<p>※ 每次招募的简介中，凡未明确注明的均为 50 心 11 连，UR 1%，SSR 4%，SR 15%。</p>
<p>※ 由于本站“参考价值体系”预定于 2020 年冬季进行修订，现在相关计算数据暂不显示。标准技能分布下的单级数据可在阶梯单级页面查询。</p>
</div>
</div>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/foot.php';
