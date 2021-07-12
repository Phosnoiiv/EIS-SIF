<?php
namespace EIS\Lab\SIF;
$pageID = 27;
require_once dirname(__DIR__).'/core/init.php';

require ROOT_SIF_WEB.'/common-d42c0d8a/head1.php';
echo HTML::js('tool-accessories');
?>
<script>
<?=Cache::read('tool-accessories.js')?>
</script>
<?php
require ROOT_SIF_WEB.'/common-d42c0d8a/head2.php';
?>
<h2>通常饰品制作概率</h2>
<table class="eis-sif-table">
<tr><th></th><th>社员一</th><th>社员二</th></tr>
<tr><th>稀有度</th><td class="make-cell" data-type=1 data-num=1></td><td class="make-cell" data-type=1 data-num=2></td></tr>
<tr><th>等级</th><td class="make-cell" data-type=2 data-num=1></td><td class="make-cell" data-type=2 data-num=2></td></tr>
<tr><th>技能等级</th><td class="make-cell" data-type=3 data-num=1></td><td class="make-cell" data-type=3 data-num=2></td></tr>
</table>
<h4>结果</h4>
<table class="eis-sif-table">
<tr><th>UR 饰品</th><th>SSR 饰品</th><th>SR 饰品</th><th>R 饰品</th><th>N 饰品</th></tr>
<tr><td class="make-result" data-rarity=4></td><td class="make-result" data-rarity=5></td><td class="make-result" data-rarity=3></td><td class="make-result" data-rarity=2></td><td class="make-result" data-rarity=1></td></tr>
</table>
<?php
require ROOT_SIF_WEB.'/common-d42c0d8a/foot.php';
