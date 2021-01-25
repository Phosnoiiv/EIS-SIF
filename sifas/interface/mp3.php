<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
require_once dirname(__DIR__) . '/core/init.php';

$pass = $_GET['p'] ?? '';
$code = $_GET['c'] ?? '';

$sql = "SELECT * FROM s_mp3_schedule WHERE pass='$pass'";
$dbSchedule = DB::lt_query('eis.s3db', $sql)->fetchArray(SQLITE3_ASSOC);
if (!$dbSchedule) {
    SIF\Basic::exit('Invalid argument', 403);
}
$time = time();
if (strtotime($dbSchedule['time_open'] . '+0800') > $time || strtotime($dbSchedule['time_close'] . '+0800') < $time) {
    SIF\Basic::exit('Not open now', 403);
}
$group = $dbSchedule['group'];
$sql = "SELECT * FROM s_mp3 WHERE code='$code' AND id IN (SELECT mp3 FROM s_mp3_group WHERE [group]=$group)";
$dbMP3 = DB::lt_query('eis.s3db', $sql)->fetchArray(SQLITE3_ASSOC);
if (!$dbMP3) {
    SIF\Basic::exit('Invalid argument', 403);
}

header('Content-Type: audio/mpeg');
header('Content-Disposition: inline; filename="' . str_replace('/', '_', $dbMP3['file']) . '.mp3"');
header('Cache-Control: max-age=86400');
readfile(ROOT_SIFAS_ASSET . '/sound/' . $dbMP3['file'] . '.mp3');
