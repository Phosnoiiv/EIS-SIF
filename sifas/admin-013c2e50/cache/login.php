<?php
namespace EIS\Lab\SIFAS;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$allItems = $cacheItems = [];
$clientCountItems = [null];
$sql = 'SELECT * FROM item_general';
$dbItems = DB::my_query($sql);
while ($dbItem = $dbItems->fetch_assoc()) {
    $type = intval($dbItem['item_type']);
    $key = intval($dbItem['item_key']);
    $image = intval($dbItem['calendar_login_image']);
    $cacheItem = [
        intval($dbItem['calendar_login_image']),
        $dbItem['jp_image1'] ?? '',
        $dbItem['jp_image2'] ?? '',
        intval($dbItem['calendar_login_position']),
        intval($dbItem['calendar_login_order']),
        $dbItem['jp_name'] ?? '',
        $dbItem['en_name'] ?? '',
        $dbItem['zh_name'] ?? '',
        $dbItem['jp_desc'] ?? '',
        $dbItem['en_desc'] ?? '',
        $dbItem['zh_desc'] ?? '',
        $dbItem['intro'] ?? '',
    ];
    if ($dbItem['calendar_login_cache']) {
        $cacheItems[$type][$key] = $cacheItem;
        $allItems[$type][$key] = true;
    } else {
        $allItems[$type][$key] = $cacheItem;
    }
    if ($count = intval($dbItem['calendar_login_count'])) {
        $clientCountItems[$count] = [$type, $key];
    }
}

$allGifts = [];
$cacheGifts = [null];
$sql = 'SELECT gift_group_id, gift_type, gift_key, gift_amount FROM login_gift';
$dbGifts = DB::my_query($sql);
while ($dbGift = $dbGifts->fetch_assoc()) {
    $id = intval($dbGift['gift_group_id']);
    $type = intval($dbGift['gift_type']);
    $key = intval($dbGift['gift_key']);
    if (!isset($allGifts[$id])) {
        $allGifts[$id] = $cacheGifts[$id] = [];
    }
    SIFAS::itemCollectionAppend(
        $cacheGifts[$id],
        $type,
        $key,
        intval($dbGift['gift_amount'])
    );
    if (!in_array([$type, $key], $allGifts[$id])) {
        $allGifts[$id][] = [$type, $key];
    }
}

$clientBirthdays = [null];
for ($i = 1; $i <= 12; $i++) {
    $clientBirthdays[$i] = [];
}
$sql = 'SELECT * FROM sif.member WHERE (category IS NOT NULL OR sifas_category IS NOT NULL) AND birthday IS NOT NULL';
$dbBirthdays = DB::my_query($sql);
while ($dbBirthday = $dbBirthdays->fetch_assoc()) {
    $birthday = strtotime($dbBirthday['birthday']);
    $month = date('n', $birthday);
    $day = date('j', $birthday);
    $clientBirthdays[$month][$day] = intval($dbBirthday['display_class']);
}

ksort($clientCountItems);
Cache::writeMultiJson('login.js', [
    'countItems' => $clientCountItems,
    'items' => $cacheItems,
    'gifts' => $cacheGifts,
    'birthdays' => $clientBirthdays,
]);
Cache::writePhp('login.php', [
    'cacheLoginItems' => $allItems,
    'cacheLoginGifts' => $allGifts,
]);
