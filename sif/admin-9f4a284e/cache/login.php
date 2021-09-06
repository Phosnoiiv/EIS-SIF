<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$serverGifts = [];
$gifts = [null];
$db_gifts = DB::my_query('SELECT
        gift_group_id, gift_type, gift_key, gift_amount
    FROM login_gift');
while ($gift = $db_gifts->fetch_assoc()) {
    $id = intval($gift['gift_group_id']);
    $type = $gift['gift_type'];
    $key = intval($gift['gift_key']);
    $amount = intval($gift['gift_amount']);
    if (!isset($serverGifts[$id])) {
        $serverGifts[$id] = [];
    }
    switch ($type) {
        case 1000: // Item
        case 1001: // Unit
        case 3006: // Seal
        case 7000: // Exchange ticket
        case 7500: // Lottery ticket
        case 8000: // Recovery item
            $gifts[$id][$type] = $gifts[$id][$type] ?? [];
            $gifts[$id][$type][$key] = $amount;
            break;
        case 3000: // G
        case 3001: // Loveca
        case 3002: // Friend pt
            $gifts[$id][$type] = $amount;
            break;
        case 5100: // Title
        case 5200: // Background
        case 5600: // Stamp
            $gifts[$id][$type] = $gifts[$id][$type] ?? [];
            $gifts[$id][$type][] = $key;
            break;
    }
    if (!in_array([$type, $key], $serverGifts[$id])) {
        $serverGifts[$id][] = [$type, $key];
    }
}
Cache::writeJson('gift.login.json', $gifts);

$clientItems = $serverItems = [];
$clientCountItems = [null];
$db_items = DB::my_query('SELECT * FROM item_general
');
while ($item = $db_items->fetch_assoc()) {
    $type = intval($item['item_type']);
    $key = intval($item['item_key']);
    $cacheItem = [
        intval($item['calendar_login_image']),
        $item['jp_image1'] ?? '',
        $item['en_image1'] != $item['jp_image1'] ? $item['en_image1'] ?? '' : '',
        $item['cn_image1'] != $item['jp_image1'] ? $item['cn_image1'] ?? '' : '',
        $item['jp_image2'] ?? '',
        $item['en_image2'] != $item['jp_image2'] ? $item['en_image2'] ?? '' : '',
        $item['cn_image2'] != $item['jp_image2'] ? $item['cn_image2'] ?? '' : '',
        $item['jp_name'] ?? '',
        $item['en_name'] ?? '',
        $item['cn_name'] ?? '',
        $item['jp_desc'] ?? '',
        $item['en_desc'] ?? '',
        $item['cn_desc'] ?? '',
        $item['intro'] ?? '',
        intval($item['calendar_login_position']),
    ];
    if ($item['calendar_login_cache']) {
        $clientItems[$type][$key] = $cacheItem;
        $serverItems[$type][$key] = true;
    } else {
        $serverItems[$type][$key] = $cacheItem;
    }
    if ($count = intval($item['calendar_login_count'])) {
        $clientCountItems[$count] = [$type, $key];
    }
}

$clientMembers = [];
$clientBirthdays = [null, [], [], [], [], [], [], [], [], [], [], [], []];
$sql = "SELECT * FROM member_v107 WHERE sif_birthday IS NOT NULL OR sif_id IN (SELECT DISTINCT unit_member FROM unit WHERE unit_id IN (SELECT DISTINCT gift_key FROM login_gift WHERE gift_type=1001))";
$col = [['s','jp_name'],['s','en_name'],['s','zhs_name'],['s','birthday'],['i','no'],['i','sif_birthday']];
$dMembers = DB::mySelect($sql, $col, 'sif_id');
foreach ($dMembers as $memberID => $dMember) {
    $clientMembers[$memberID] = array_slice($dMember, 0, 3);
    if (empty($dMember[5])) continue;
    $tDateSplits = explode('-', $dMember[3]);
    $clientBirthdays[intval($tDateSplits[0])][intval($tDateSplits[1])] = array_slice($dMember, 4);
}

$serverUnits = [];
$sql = 'SELECT * FROM unit WHERE unit_id IN (SELECT gift_key FROM login_gift WHERE gift_type=1001) ORDER BY unit_id ASC';
$dbUnits = DB::my_query($sql);
while ($dbUnit = $dbUnits->fetch_assoc()) {
    $id = $dbUnit['unit_id'];
    $serverUnits[$id] = [
        intval($dbUnit['unit_number']),
        intval($dbUnit['unit_member']),
        intval($dbUnit['unit_rarity']),
        intval($dbUnit['attribute']),
        $dbUnit['jp_name'] ?? '',
        $dbUnit['en_name'] ?? '',
        $dbUnit['cn_name'] ?? '',
    ];
}

$serverBackgrounds = [];
$sql = 'SELECT * FROM item_background WHERE id IN (SELECT gift_key FROM login_gift WHERE gift_type=5200) ORDER BY id ASC';
$dbBackgrounds = DB::my_query($sql);
while ($dbBackground = $dbBackgrounds->fetch_assoc()) {
    $id = $dbBackground['id'];
    $serverBackgrounds[$id] = [
        intval($dbBackground['is_motion']),
        $dbBackground['jp_name'] ?? '',
        $dbBackground['en_name'] ?? '',
        $dbBackground['cn_name'] ?? '',
        $dbBackground['jp_desc'] ?? '',
        $dbBackground['en_desc'] ?? '',
        $dbBackground['cn_desc'] ?? '',
        $dbBackground['intro'] ?? '',
    ];
}

$titles = [];
$db_titles = DB::my_query('SELECT * FROM item_title
    WHERE title_key IN (
        SELECT gift_key FROM login_gift
        WHERE gift_type=5100
    )
    ORDER BY title_key ASC
');
while ($title = $db_titles->fetch_assoc()) {
    $key = intval($title['title_key']);
    $titles[$key] = [
        $title['jp_image'] ?? '',
        $title['en_image'] ?? '',
        $title['cn_image'] ?? '',
        $title['jp_name'] ?? '',
        $title['en_name'] ?? '',
        $title['cn_name'] ?? '',
        $title['jp_desc'] ?? '',
        $title['en_desc'] ?? '',
        $title['cn_desc'] ?? '',
        $title['intro'] ?? '',
    ];
}

$serverStamps = [];
$sql = 'SELECT * FROM item_stamp WHERE id IN (SELECT gift_key FROM login_gift WHERE gift_type=5600) ORDER BY id ASC';
$dbStamps = DB::my_query($sql);
while ($dbStamp = $dbStamps->fetch_assoc()) {
    $id = $dbStamp['id'];
    $serverStamps[$id] = [
        $dbStamp['jp_name'] ?? '',
        $dbStamp['en_name'] ?? '',
        $dbStamp['cn_name'] ?? '',
        $dbStamp['intro'] ?? '',
    ];
}

ksort($clientCountItems);
Cache::writeMultiJson('login.js', [
    'countItems' => $clientCountItems,
    'items' => $clientItems,
    'members' => $clientMembers,
    'birthdays' => $clientBirthdays,
    ]);
Cache::writePhp('login.php', [
    'cacheLoginGifts' => $serverGifts,
    'cacheLoginItems' => $serverItems,
    'cacheLoginUnits' => $serverUnits,
    'cacheLoginBackgrounds' => $serverBackgrounds,
    'cacheLoginTitles' => $titles,
    'cacheLoginStamps' => $serverStamps,
]);
