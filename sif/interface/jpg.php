<?php
namespace EIS\Lab\SIF;
require_once dirname(__DIR__) . '/core/init.php';

$pass = $_GET['p'] ?? '';
$code = $_GET['c'] ?? '';
$key = $_GET['k'] ?? '';

if (!empty($pass) && !empty($code)) {
$sql = "SELECT * FROM s_jpg_schedule WHERE pass='$pass'";
$dbSchedule = DB::lt_query('eis.s3db', $sql)->fetchArray(SQLITE3_ASSOC);
if (!$dbSchedule) {
    Basic::exit('Invalid argument', 403);
}
$time = time();
if (strtotime($dbSchedule['time_open'] . '+0800') > $time || strtotime($dbSchedule['time_close'] . '+0800') < $time) {
    Basic::exit('Not open now', 403);
}
$group = $dbSchedule['group'];
$sql = "SELECT * FROM s_jpg WHERE code='$code' AND id IN (SELECT jpg FROM s_jpg_group WHERE [group]=$group)";
$dbJPG = DB::lt_query('eis.s3db', $sql)->fetchArray(SQLITE3_ASSOC);
} else {
    $consume = true;
    $sql = 'SELECT * FROM s_jpg WHERE key=:key';
    $dbJPG = DB::ltParamQuery('eis.s3db', $sql, [':key' => $key])->fetchArray(SQLITE3_ASSOC);
}
if (!$dbJPG) {
    Basic::exit('Invalid argument', 403);
}
if ($consume ?? false) {
    $userIdentity = $_SERVER['REMOTE_ADDR'];
    $limit = new Limit(Limit::TYPE_GALLERY, $userIdentity);
    if ($limit->current <= 0) {
        Basic::exit('Downloads run out', 403);
    }
    $limit->record($dbJPG['file']);
}

header('Content-Type: image/jpeg');
header('Content-Disposition: inline; filename="' . str_replace('/', '_', $dbJPG['file']) . '.jpg"');
header('Cache-Control: max-age=86400');
readfile(ROOT_SIF_ASSET . '/image/' . $dbJPG['file'] . '.jpg');
