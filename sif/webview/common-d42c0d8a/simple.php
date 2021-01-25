<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;

$hideTitle = true;
require ROOT_SIF_WEB . '/common-d42c0d8a/head1.php';
require ROOT_SIF_WEB . '/common-d42c0d8a/head2.php';
?>
<div class="eis-sif-simple-container">
<div class="eis-sif-simple-icon"><?=$simpleIcon?></div>
<h2 class="eis-sif-simple-title"><?=$simpleTitle?></h2>
<?php
if (!empty($simpleTime)) {
    echo '<div class="eis-sif-timetip on-background">' . $simpleTime . "</div>\n";
}
foreach ($simpleNotices ?? [] as $notice) {
    echo '<div class="eis-sif-notice"><p><i class="fas fa-exclamation-circle"></i> ' . $notice . "</p></div>\n";
}
?>
<div class="eis-sif-simple-text"><?=$simpleText?></div>
</div>
<?php
require ROOT_SIF_WEB . '/common-4fcb29e1/foot.php';
