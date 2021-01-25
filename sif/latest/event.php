<?php
namespace EIS\Lab\SIF;
$pageID = 18;
require_once dirname(__DIR__) . '/core/init.php';

$latestFile = ROOT_SIF_CACHE . '/latest-event.js';

require ROOT_SIF_WEB . '/common-d42c0d8a/head1.php';
echo HTML::css('latest-event');
echo HTML::js('latest-event');
?>
<script>
<?=Cache::read('latest-event.js')?>
</script>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/head2.php';
?>
<h2 class="eis-sif-text"></h2>
<div class="eis-sif-timetip on-background"></div>
<section class="eis-sif-section-noborder">
<h4><i class="fas fa-user-plus fa-lg"></i> 活动加成社员</h4>
<table id="cheers" class="eis-sif-table">
<thead>
<tr><th>编号</th><th></th><th></th><th>名称</th><th>系列</th><th colspan=2>未觉醒</th><th colspan=2>觉醒后</th></tr>
</thead>
<tbody></tbody>
</table>
</section>
<section class="eis-sif-section-noborder">
<h4 class="event3 event5"><i class="fas fa-music"></i> 歌单</h4>
<h4 class="event2 event4 event6"><i class="fas fa-music"></i> 快速歌单</h4>
<div class="eis-sif-notice event2 event4 event6"><p><i class="fas fa-exclamation-circle"></i> 本栏旨在以尽可能快的速度提供一部分歌单信息。当前类型的活动无法快速提供完整准确的歌单，以下数据可能有缺漏。</p></div>
<div class="eis-sif-notice event6"><p><i class="fas fa-exclamation-circle"></i> 下表中 MASTER 一列的数据为平常的 MASTER 谱面数据。5 键 MASTER 谱面的数据不在此列出。</p></div>
<table id="lives" class="eis-sif-table">
<thead>
<tr><th></th><th>备注</th><th>EXPERT</th><th>MASTER</th></tr>
</thead>
<tbody></tbody>
</table>
<p class="eis-sif-note event2 event4">※ 备注中仅列出初次在同类活动中推出随机谱面且星级与同难度的通常谱面不同的情况。以前在同类活动中推出过随机谱面的不再备注。随机谱面星级与同难度的通常谱面相同的不再备注。</p>
</section>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/foot.php';
