<?php
namespace EIS\Lab\SIF;
$pageID = 6;
require_once dirname(__DIR__) . '/core/init.php';

require ROOT_SIF_WEB . '/common-4fcb29e1/head1.php';
echo HTML::css('mf');
echo HTML::js('mf.missions');
?>
<script>
<?=Cache::read('event.mf.mission.js')?>
</script>
<?php
require ROOT_SIF_WEB . '/common-4fcb29e1/head2.php';
?>
<div class="eis-sif-message eis-sif-message-warning">
<p>本页面展示国际版第 17 次 Medley Festival 活动（对应日语版第 20 次果希活动）的任务，未必适用于其它场次活动，仅供参考。</p>
</div>
<div class="eis-sif-message eis-sif-message-notice">
<p>本页面展示报酬中的 R 羊驼[白] 仅在 μ's 活动中出现，在 Aqours 活动中将会替换为 R 小香菇。</p>
</div>
<div id="list"></div>
<?php
require ROOT_SIF_WEB . '/common-4fcb29e1/foot.php';
