<?php
namespace EIS\Lab\SIF;
$pageID = 32;
require_once __DIR__.'/../core/init.php';

$latestFile = ROOT_SIF_CACHE . '/event-yell.json';
V2::$useV2Front = true;

require ROOT_SIF_WEB . '/common-d42c0d8a/head1.php';
echo HTML::js('event-cheers');
?>
<script>
<?=Cache::readJson('event-yell.json', 'events')?>
</script>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/head2.php';
?>
<div id="v2-container" class="v2-container v2-container-main"></div>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/foot.php';
