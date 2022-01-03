<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
require_once dirname(__DIR__) . '/core/init.php';

$server = SIF\Basic::checkArg($_GET['s'] ?? false, range(1, 3));
$year = SIF\Basic::checkArg($_GET['y'] ?? false, range(2019, 2022));
$month = SIF\Basic::checkArg($_GET['m'] ?? false, range(1, 12));
include ROOT_SIFAS_CACHE . '/login.php';

$bonuses = $items = [];
$date = $year . '-' . $month . '-01 00:00:00';
$prfSrv = SIF\SIF::$prefixServer[$server];
$prfSrvOpen = $prfSrv.'_open';
$prfLng = SIF\SIF::$prefixLanguage[$server];
$prfLngName = $prfLng.'_name';
$fltBetween = "datetime('$date','-9 days') AND datetime('$date','+1 month')";
$sql = "SELECT * FROM d_login_v105 WHERE ($prfSrvOpen BETWEEN $fltBetween) OR ($prfSrvOpen IS NULL AND $prfLngName IS NOT NULL AND jp_open BETWEEN $fltBetween)";
$col = [
    ['s','jp_name',''],['s','en_name',''],['s','zhs_name',''],
    ['t','jp_open',1],['t','jp_close',1],['t','gl_open',2],['t','gl_close',2],['t','cn_open',3],['t','cn_close',3],
    ['i','project'],['i','type'],
    ['s','jp_image',''],['s','en_image',''],['s','zhs_image',''],
    ['i','gift1'],['i','gift2'],['i','gift3'],['i','gift4'],['i','gift5'],
    ['i','gift6'],['i','gift7'],['i','gift8'],['i','gift9'],['i','gift10'],
];
const COL_LOGIN_GIFT1 = 14;
$bonuses = DB::ltSelect(DB_EIS_MAIN, $sql, $col, '');
array_walk($bonuses, function(&$a) {
    global $cacheLoginGifts, $cacheLoginItems, $items;
    $gifts = [];
    for ($i = 0; $i < 10; $i++) {
        if (empty($gift = $a[COL_LOGIN_GIFT1+$i]))
            break;
        $gifts[] = $gift;
        foreach ($cacheLoginGifts[$gift] as $item) {
            if (is_array($cacheLoginItems[$item[0]][$item[1]])) {
                $items[$item[0]][$item[1]] = $cacheLoginItems[$item[0]][$item[1]];
                $cacheLoginItems[$item[0]][$item[1]] = true;
            }
        }
    }
    $a = array_merge(array_slice($a, 0, COL_LOGIN_GIFT1), [$gifts]);
});
header('Content-type: application/json');
echo json_encode([
    'bonuses' => $bonuses,
    'items' => $items,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
