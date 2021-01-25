<?php
namespace EIS\Lab\SIF;
$pageID = 8;
require_once dirname(__DIR__) . '/core/init.php';

$latest_file = ROOT_SIF_CACHE . '/stories.js';
require ROOT_SIF_WEB . '/common-4fcb29e1/head1.php';
echo HTML::css('stories');
echo HTML::js('stories');
?>
<script>
<?=Cache::read('stories.js')?>
</script>
<?php
require ROOT_SIF_WEB . '/common-4fcb29e1/head2.php';
?>
<div class="eis-sif-message eis-sif-message-notice">
<p>各服务器现在的特别剧情总数：<span id="summary"></span>。</p>
</div>
<div id="stories"></div>
<?php
require ROOT_SIF_WEB . '/common-4fcb29e1/foot.php';
