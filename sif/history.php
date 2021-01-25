<?php
namespace EIS\Lab\SIF;
$pageID = 4;
require_once __DIR__ . '/core/init.php';

require ROOT_SIF_WEB . '/common-d42c0d8a/head1.php';
echo HTML::css('article');
echo HTML::css('history');
echo HTML::js('history');
?>
<script>
<?=Cache::readJson('site.history.json', 'versions')?>
</script>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/head2.php';
?>
<article>
<div id="versions"></div>
</article>
<?php
require ROOT_SIF_WEB . '/common-4fcb29e1/foot.php';
