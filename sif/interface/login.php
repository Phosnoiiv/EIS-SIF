<?php
namespace EIS\Lab\SIF;
require_once dirname(__DIR__) . '/core/init.php';

$server = Basic::checkArg($_GET['s'] ?? false, range(1, 3));
$year = Basic::checkArg($_GET['y'] ?? false, range(2018, 2021));
$month = Basic::checkArg($_GET['m'] ?? false, range(1, 12));
include ROOT_SIF_CACHE . '/login.php';

$bonuses = $items = $units = $titles = $backgrounds = $stamps = [];
$date = $year . '-' . $month . '-01 00:00:00';
$timezone = SIF::getServerTimezone($server);
$dbBonuses = DB::lt_query('eis.s3db', "SELECT
        bonus_name, time_open, time_close,
        gift1, gift2, gift3, gift4, gift5, gift6, gift7, gift8, gift9, gift10
    FROM d_login
    WHERE bonus_server=$server
        AND time_open>=datetime('$date','-9 days')
        AND time_open<=datetime('$date','+1 month')");
while ($db_bonus = $dbBonuses->fetchArray(SQLITE3_ASSOC)) {
    if (empty($db_bonus['time_close'])) {
        $close = 0;
    } else {
        $close = strtotime($db_bonus['time_close'] . $timezone);
    }
    $bonus = [
        $db_bonus['bonus_name'],
        strtotime($db_bonus['time_open'] . $timezone),
        $close,
    ];
    $gifts = [];
    for ($i = 1; $i <= 10; $i++) {
        if (($gift = intval($db_bonus['gift' . $i])) == 0)
            break;
        $gifts[] = $gift;
        foreach ($cacheLoginGifts[$gift] as $item) {
            $type = $item[0]; $key = $item[1];
            if ($type == 1001) {
                $units[$key] = $cacheLoginUnits[$key];
            } else if ($type == 5100) {
                $titles[$key] = $cacheLoginTitles[$key];
            } else if ($type == 5200) {
                $backgrounds[$key] = $cacheLoginBackgrounds[$key];
            } else if ($type == 5600) {
                $stamps[$key] = $cacheLoginStamps[$key];
            } else if (is_array($cacheLoginItems[$type][$key] ?? null)) {
                $items[$type][$key] = $cacheLoginItems[$type][$key];
                $cacheLoginItems[$type][$key] = true;
            }
        }
    }
    $bonus[] = $gifts;
    $bonuses[] = $bonus;
}
header('Content-type: application/json');
echo HTML::json('', [
    'bonuses' => $bonuses,
    'items' => $items,
    'units' => $units,
    'titles' => $titles,
    'backgrounds' => $backgrounds,
    'stamps' => $stamps,
]);
