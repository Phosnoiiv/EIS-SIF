<?php
namespace EIS\Lab\SIF;
require_once __DIR__.'/../../../core/init.php';

$v2NextRefresh = 9999999999;

$colTime = V2::$isDevServer ? 'time_debug' : 'time_release';
$sql = "SELECT * FROM sv2_data WHERE $colTime<=datetime('now','localtime') ORDER BY $colTime DESC, id DESC";
$col = [['s','name'],['s','patch'],['s','path'],['t',$colTime,3]];
$v2DataBundles = DB::ltSelect(DB_EIS_MAIN, $sql, $col, 'id');
$v2DataBundleName = $v2DataBundlePatchName = '';
foreach ($v2DataBundles as $bundleId => $dBundle) {
    if (empty($v2DataBundlePatchName) && !empty($dBundle[1])) $v2DataBundlePatchName = $dBundle[1];
    if (empty($v2DataBundleName) && !empty($dBundle[0])) {$v2DataBundleName = $dBundle[0]; break;}
}
$sql = "SELECT MIN($colTime) m FROM sv2_data WHERE $colTime>datetime('now','localtime')";
$tNextAt = DB::ltSelect(DB_EIS_MAIN, $sql, [['t','m',3]], '', ['s'=>true])[0];
if (!empty($tNextAt)) $v2NextRefresh = min($v2NextRefresh, $tNextAt);

Cache::writePhp('v2/server.php', [
    'v2NextRefresh' => $v2NextRefresh,
    'v2DataBundleName' => $v2DataBundleName,
    'v2DataBundlePatchName' => $v2DataBundlePatchName,
    'v2DataBundles' => $v2DataBundles,
]);
Cache::writePhp('v2/data.php', [
    'v2DataFiles' => [], // 清空
]);
