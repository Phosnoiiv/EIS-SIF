<?php
namespace EIS\Lab\SIF;
$pageID = 30;
require_once __DIR__.'/../core/init.php';

$playTypeID = 3;
$playJS = ['play/mattribux','play/include/match3'];
$playModes = [['s',1],['e1',2]];

require ROOT_SIF_WEB.'/common-d42c0d8a/play-head.php';
?>
<div id="play-main">
<canvas id="mattribux-main" width=640 height=480></canvas>
</div>
<?php
require ROOT_SIF_WEB.'/common-d42c0d8a/play-foot.php';
