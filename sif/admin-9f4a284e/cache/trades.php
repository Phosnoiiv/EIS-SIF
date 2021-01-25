<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$serverTrades = [];
$clientTrades = [null];
$dictTrades = new Dict(true);
$sql = 'SELECT * FROM trade WHERE display_order>0 ORDER BY display_order ASC';
$dbTrades = DB::my_query($sql);
while ($dbTrade = $dbTrades->fetch_assoc()) {
    $id = $dictTrades->set($dbTrade['trade_id']);
    $server = intval($dbTrade['server']);
    $timezone = SIF::getServerTimezone($server);
    $clientTrades[$id] = [
        $server,
        $dbTrade['name'],
        $dbTrade['display_name'],
        strtotime($dbTrade['time_start'] . $timezone),
        strtotime($dbTrade['time_open'] . $timezone),
        strtotime($dbTrade['time_stop'] . $timezone),
        strtotime($dbTrade['time_close'] . $timezone),
        $dbTrade['banner'] ?? '',
        [],
    ];
    if ($clientTrades[$id][6] > time()) {
        $serverTrades[$id] = array_slice($clientTrades[$id], 0, 7);
    }
}

$allCategories = [];
$sql = 'SELECT * FROM trade_goods_group';
$dbGoodsGroups = DB::my_query($sql);
while ($dbGoodsGroup = $dbGoodsGroups->fetch_assoc()) {
    $category = $dbGoodsGroup['category_id'];
    $allCategories[$category] = $allCategories[$category] ?? [];
    $allCategories[$category][] = [
        intval($dbGoodsGroup['goods_group_id']),
        empty($dbGoodsGroup['time_available']) ? 0 : $dbGoodsGroup['time_available'],
    ];
}
$sql = 'SELECT * FROM trade_category';
$dbCategories = DB::my_query($sql);
while ($dbCategory = $dbCategories->fetch_assoc()) {
    $id = $dbCategory['category_id'];
    if (($trade = $dictTrades->find($dbCategory['trade_id'])) === false)
        continue;
    $server = $clientTrades[$trade][0];
    $timezone = SIF::getServerTimezone($server);
    $clientTrades[$trade][8][] = [
        $dbCategory['name'],
        $dbCategory['display_name'],
        empty($dbCategory['time_open']) ? 0 : strtotime($dbCategory['time_open'] . $timezone),
        empty($dbCategory['time_close']) ? 0 : strtotime($dbCategory['time_close'] . $timezone),
        array_map(function($a) {
            global $timezone;
            if ($a[1]) {
                $a[1] = strtotime($a[1] . $timezone);
            }
            return $a;
        }, $allCategories[$id]),
        intval($dbCategory['currency_group']),
        $dbCategory['intro'] ?? '',
    ];
}

$allItems = $clientItems = [];
$sql = 'SELECT * FROM item_general';
$dbItems = DB::my_query($sql);
while ($dbItem = $dbItems->fetch_assoc()) {
    $type = $dbItem['item_type'];
    $key = $dbItem['item_key'];
    $image = $dbItem['list_trade_image'];
    $allItems[$type][$key] = [
        $dbItem['jp_name'] ?? '',
        $dbItem['en_name'] ?? '',
        $dbItem['cn_name'] ?? '',
        $dbItem['jp_image' . $image] ?? '',
        $dbItem['en_image' . $image] ?? '',
        $dbItem['cn_image' . $image] ?? '',
    ];
}

$clientCurrencies = [null];
$sql = 'SELECT * FROM trade_currency';
$dbCurrencies = DB::my_query($sql);
while ($dbCurrency = $dbCurrencies->fetch_assoc()) {
    $id = $dbCurrency['currency_group_id'];
    $num = $dbCurrency['currency_num'];
    $type = intval($dbCurrency['item_type']);
    $key = intval($dbCurrency['item_key']);
    $clientCurrencies[$id][$num - 1] = [
        $type,
        $key,
    ];
    $clientItems[$type][$key] = $allItems[$type][$key];
}

$allGoodsCosts = [];
$sql = 'SELECT * FROM trade_cost';
$dbGoodsCosts = DB::my_query($sql);
while ($dbGoodsCost = $dbGoodsCosts->fetch_assoc()) {
    $id = $dbGoodsCost['goods_id'];
    $num = $dbGoodsCost['currency_num'];
    $allGoodsCosts[$id][$num - 1] = intval($dbGoodsCost['amount']);
}

$clientGoods = [null];
$sql = 'SELECT * FROM trade_goods';
$dbGoods = DB::my_query($sql);
while ($dbGoods1 = $dbGoods->fetch_assoc()) {
    $id = $dbGoods1['goods_id'];
    $group = $dbGoods1['goods_group_id'];
    $type = intval($dbGoods1['item_type']);
    $key = intval($dbGoods1['item_key']);
    $clientGoods[$group] = $clientGoods[$group] ?? [];
    $clientGoods[$group][] = [
        $type,
        $key,
        intval($dbGoods1['amount']),
        intval($dbGoods1['limit']),
        intval($dbGoods1['reset']),
        intval($dbGoods1['display_order']),
        count($allGoodsCosts[$id]) > 1 ? $allGoodsCosts[$id] : $allGoodsCosts[$id][0],
    ];
    if (isset($allItems[$type][$key])) {
        $clientItems[$type][$key] = $allItems[$type][$key];
    }
}

$clientMembers = [];
$sql = 'SELECT * FROM member WHERE member_id IN (SELECT unit_member FROM unit WHERE unit_id IN (SELECT item_key FROM trade_goods WHERE item_type=1001))';
$dbMembers = DB::my_query($sql);
while ($dbMember = $dbMembers->fetch_assoc()) {
    $id = $dbMember['member_id'];
    $clientMembers[$id] = [
        $dbMember['jp_name'],
        $dbMember['en_name'],
        $dbMember['cn_name'],
    ];
}

$clientUnits = [];
$sql = 'SELECT * FROM unit WHERE unit_id IN (SELECT item_key FROM trade_goods WHERE item_type=1001) ORDER BY unit_id ASC';
$dbUnits = DB::my_query($sql);
while ($dbUnit = $dbUnits->fetch_assoc()) {
    $id = $dbUnit['unit_id'];
    $clientUnits[$id] = [
        intval($dbUnit['unit_number']),
        intval($dbUnit['unit_member']),
        intval($dbUnit['unit_rarity']),
        intval($dbUnit['attribute']),
        $dbUnit['jp_name'] ?? '',
        $dbUnit['en_name'] ?? '',
        $dbUnit['cn_name'] ?? '',
    ];
}

$clientTitles = [];
$sql = 'SELECT * FROM item_title WHERE title_key IN (SELECT item_key FROM trade_goods WHERE item_type=5100) ORDER BY title_key ASC';
$dbTitles = DB::my_query($sql);
while ($dbTitle = $dbTitles->fetch_assoc()) {
    $id = $dbTitle['title_key'];
    $clientTitles[$id] = [
        $dbTitle['jp_image'] ?? '',
        $dbTitle['en_image'] ?? '',
        $dbTitle['cn_image'] ?? '',
        $dbTitle['jp_name'] ?? '',
        $dbTitle['en_name'] ?? '',
        $dbTitle['cn_name'] ?? '',
        $dbTitle['intro'] ?? '',
    ];
}

$clientBackgrounds = [];
$sql = 'SELECT * FROM item_background WHERE id IN (SELECT item_key FROM trade_goods WHERE item_type=5200) ORDER BY id ASC';
$dbBackgrounds = DB::my_query($sql);
while ($dbBackground = $dbBackgrounds->fetch_assoc()) {
    $id = $dbBackground['id'];
    $clientBackgrounds[$id] = [
        intval($dbBackground['is_motion']),
        $dbBackground['jp_name'] ?? '',
        $dbBackground['en_name'] ?? '',
        $dbBackground['cn_name'] ?? '',
        $dbBackground['intro'] ?? '',
    ];
}

$sql = 'SELECT * FROM item_si WHERE id IN (SELECT item_key FROM trade_goods WHERE item_type=5500) ORDER BY id ASC';
$columns = [['i','level'],['s','jp_name',''],['s','en_name',''],['s','cn_name',''],['s','image1'],['i','desc_string'],['d','value1']];
$clientSIs = DB::mySelect($sql, $columns, 'id');

$sql = 'SELECT * FROM item_si_string WHERE id IN (' . implode(',', array_unique(array_column($clientSIs, 5))) . ') ORDER BY id ASC';
$columns = [['s','jp_string',''],['s','en_string',''],['s','cn_string','']];
$clientSIStrings = DB::mySelect($sql, $columns, 'id');

Cache::writeMultiJson('trades.js', [
    'items' => $clientItems,
    'trades' => $clientTrades,
    'currencies' => $clientCurrencies,
    'goods' => $clientGoods,
    'members' => $clientMembers,
    'units' => $clientUnits,
    'titles' => $clientTitles,
    'backgrounds' => $clientBackgrounds,
    'SIs' => $clientSIs,
    'SIStrings' => $clientSIStrings,
]);
Cache::writePhp('trades.php', [
    'cacheTrades' => $serverTrades,
]);
