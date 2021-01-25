<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
require_once dirname(__DIR__) . '/core/init.php';

$server = SIF\Basic::checkArg($_GET['s'] ?? false, range(1, 2));
$year = SIF\Basic::checkArg($_GET['y'] ?? false, range(2019, 2021));
$month = SIF\Basic::checkArg($_GET['m'] ?? false, range(1, 12));
include ROOT_SIFAS_CACHE . '/login.php';

$bonuses = $items = [];
$date = $year . '-' . $month . '-01 00:00:00';
$timezone = SIFAS::getServerTimezone($server);
$sql = "SELECT * FROM d_login WHERE server=$server AND time_open>=datetime('$date','-9 days') AND time_open<=datetime('$date','+1 month')";
$dbBonuses = DB::lt_query('eis.s3db', $sql);
while ($dbBonus = $dbBonuses->fetchArray(SQLITE3_ASSOC)) {
    if (empty($dbBonus['time_close'])) {
        $close = 0;
    } else {
        $close = strtotime($dbBonus['time_close'] . $timezone);
    }
    $bonus = [
        $dbBonus['login_name'],
        strtotime($dbBonus['time_open'] . $timezone),
        $close,
        $dbBonus['category'],
        $dbBonus['png_key'] ?? '',
    ];
    $gifts = [];
    for ($i = 1; $i <= 10; $i++) {
        if (($gift = intval($dbBonus['gift' . $i])) == 0)
            break;
        $gifts[] = $gift;
        foreach ($cacheLoginGifts[$gift] as $item) {
            if (is_array($cacheLoginItems[$item[0]][$item[1]])) {
                $items[$item[0]][$item[1]] = $cacheLoginItems[$item[0]][$item[1]];
                $cacheLoginItems[$item[0]][$item[1]] = true;
            }
        }
    }
    $bonus[] = $gifts;
    $bonuses[] = $bonus;
}
header('Content-type: application/json');
echo json_encode([
    'bonuses' => $bonuses,
    'items' => $items,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
