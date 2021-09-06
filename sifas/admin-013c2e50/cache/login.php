<?php
namespace EIS\Lab\SIFAS;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$sql = "SELECT * FROM cache_item";
$col = [['i','type'],['i','key'],['i','count']];
$dCacheItems = DB::mySelect($sql, $col, '');

$allItems = $cacheItems = [];
$clientCountItems = [null];
$sql = 'SELECT * FROM item_v105 WHERE (`type`,`key`) IN (SELECT DISTINCT gift_type,gift_key FROM login_gift)';
$col = [
    ['i','calendar_login_image'],['s','image1'],['s','image2',''],['i','calendar_login_position'],['i','calendar_login_order'],
    ['s','jp_name',''],['s','en_name',''],['s','zhs_name',''],
    ['s','jp_desc',''],['s','en_desc',''],['s','zhs_desc',''],['s','intro',''],
];
$allItems = DB::mySelect($sql, $col, 'type', ['k'=>'key']);
foreach ($dCacheItems as $dCacheItem) {
    $type = $dCacheItem[0]; $key = $dCacheItem[1]; $count = $dCacheItem[2];
    $cacheItems[$type][$key] = $allItems[$type][$key];
        $allItems[$type][$key] = true;
    if (!empty($count)) {
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

$sql = 'SELECT gift_group_id,COUNT(*) c FROM login_gift GROUP BY gift_group_id HAVING c>9';
$cLargeGifts = DB::mySelect($sql, [['i','gift_group_id']], '', ['s'=>true]);

$clientBirthdays = [null];
for ($i = 1; $i <= 12; $i++) {
    $clientBirthdays[$i] = [];
}
$sql = "SELECT * FROM v_member_v107 WHERE sifas_birthday IS NOT NULL";
$col = [['s','birthday'],['i','no'],['i','sifas_birthday']];
$dBirthdays = DB::mySelect($sql, $col, '');
foreach ($dBirthdays as $dBirthday) {
    $tDateSplits = explode('-', $dBirthday[0]);
    $clientBirthdays[intval($tDateSplits[0])][intval($tDateSplits[1])] = array_slice($dBirthday, 1);
}

ksort($clientCountItems);
Cache::writeMultiJson('login.js', [
    'countItems' => $clientCountItems,
    'items' => $cacheItems,
    'gifts' => $cacheGifts,
    'largeGiftIDs' => $cLargeGifts,
    'birthdays' => $clientBirthdays,
]);
Cache::writePhp('login.php', [
    'cacheLoginItems' => $allItems,
    'cacheLoginGifts' => $allGifts,
]);
