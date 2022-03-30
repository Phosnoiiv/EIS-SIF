<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;

$sql = "SELECT * FROM p_common WHERE play=$playTypeID AND time_open<=datetime('now','localtime') AND (time_close IS NULL OR time_close>=datetime('now','localtime'))";
$columns = [['s','title'],['s','subtitle'],['t','time_open',3],['t','time_closing',3],['t','time_close',3],['i','config']];
$event = DB::ltSelect('eis.s3db', $sql, $columns, '');
if (empty($event)) {
    http_response_code(503);
    $simpleIcon = '<i class="fas fa-calendar-times"></i>';
    $simpleTitle = $title = '活动已结束';
    $simpleText = '本活动已结束，后续开放安排请关注主页告知。';
    require ROOT_SIF_WEB . '/common-d42c0d8a/simple.php';
    exit;
}
$event = $event[0];
$title = $event[0];

include ROOT_SIF_CACHE . '/play/config.'.$event[5].'.php';

require ROOT_SIF_WEB . '/common-d42c0d8a/head1.php';
echo HTML::css('play/play-d42c0d8a');
foreach ($playCSS ?? [] as $css) {
    echo HTML::css($css);
}
echo HTML::js('play/play-d42c0d8a');
foreach ($playJS ?? [] as $js) {
    echo HTML::js($js);
}
?>
<script>
<?=Cache::read('play/config.'.$event[5].'.js')?>
</script>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/head2.php';
?>
<section id="play-mode" class="eis-sif-section">
<h4>开始</h4>
<div class="eis-sif-button-line"><div id="play-mode-buttons" class="eis-sif-button-group"></div></div>
<?php
foreach ($playModes ?? [['s',1],['f',2],['c',3]] as $mode) {
    echo '<p class="play-mode-desc" data-mode="',$mode[0],'">',$CPC[$mode[1]],"</p>\n";
}
?>
<div id="play-customize" class="eis-jq-accordion" data-expand=1></div>
<div class="eis-sif-button-line"><span class="eis-jq-button" onclick="playStart()"><i class="fas fa-play"></i> 开始！</span></div>
</section>
