<?php
namespace EIS\Lab\SIF;
$pageID = 3;
require_once dirname(__DIR__) . '/core/init.php';

$latest_file = ROOT_SIF_CACHE . '/box.stepup.js';
require ROOT_SIF_WEB . '/common-4fcb29e1/head1.php';
echo HTML::css('steps');
echo HTML::js('steps');
?>
<script>
<?=Cache::readJson('gift.step.json', 'gifts')?>
<?=Cache::readJson('item.step.json', 'items')?>
<?=Cache::read('unit.step.js')?>
<?=Cache::readJson('scout.step.json', 'steps')?>
<?=Cache::read('box.stepup.pattern.js')?>
<?=Cache::read('box.stepup.js')?>
</script>
<?php
require ROOT_SIF_WEB . '/common-4fcb29e1/head2.php';
?>
<label>筛选范围</label>
<input id="range1" type="date" min="2018-08-01" max="<?=date('Y-m-d')?>" value="2018-08-01" onchange="filter()"/>
<input id="range2" type="date" min="2018-08-01" max="<?=date('Y-m-d')?>" value="<?=date('Y-m-d')?>" onchange="filter()"/>
<select id="sort" onchange="listSort()">
<option value="id-asc">按首次出现时间顺序排列（↑）</option>
<option value="id-desc">按首次出现时间顺序排列（↓）</option>
<option value="ur-desc">按 UR 期望排列（↓）</option>
<option value="ur-ratio-asc" selected>按每 UR 所需 Loveca 期望排列（↑）</option>
<option value="strength-desc">按强度价值排列（↓）</option>
<option value="strength-ratio-desc">按强度收益率排列（↓）</option>
<option value="adjusted-desc">按参考价值排列（↓）</option>
<option value="adjusted-ratio-desc">按参考收益率排列（↓）</option>
</select>
<div class="eis-sif-message eis-sif-message-warning">
<p>2017～2018 年数据正在补录中；本表所列强度价值不考虑卡池技能分布的实际情况；本表现在暂不显示常规限定阶梯和完全免费的阶梯。</p>
<p>由于表格较宽，建议在 PC 设备上浏览本页面。</p>
</div>
<div class="eis-sif-message eis-sif-message-notice">
<p>您可以点击左侧三列的数字以查看相关阶梯的名称、日期等信息。</p>
<p>有关右侧两列的“参考价值”，参见<a href="/sif/article/?1" target="_blank">此说明</a>。</p>
</div>
<table>
<thead>
<tr>
    <th>JP</th>
    <th>CN</th>
    <th>WW</th>
    <th>Loveca</th>
    <th>招募数</th>
    <th>保底</th>
    <th>UR</th>
    <th>SSR</th>
    <th>SR</th>
    <th>赠品</th>
    <th>UR 期望</th>
    <th>Loveca/UR</th>
    <th>强度价值</th>
    <th>强度收益率</th>
    <th>参考价值</th>
    <th>参考收益率</th>
</tr>
</thead>
<tbody id="list"></tbody>
</table>
<?php
require ROOT_SIF_WEB . '/common-4fcb29e1/foot.php';
