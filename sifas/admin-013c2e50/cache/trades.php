<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$serverTrades = [];
$clientTrades = [null];
$sql = 'SELECT * FROM trade';
$dbTrades = DB::my_query($sql);
while ($dbTrade = $dbTrades->fetch_assoc()) {
    $id = $dbTrade['trade_id'];
    $server = intval($dbTrade['server']);
    $timezone = SIFAS::getServerTimezone($server);
    $clientTrades[$id] = [
        $server,
        $dbTrade['name'],
        $dbTrade['display_name'],
        strtotime($dbTrade['time_start'] . $timezone),
        strtotime($dbTrade['time_open'] . $timezone),
        strtotime($dbTrade['time_stop'] . $timezone),
        strtotime($dbTrade['time_close'] . $timezone),
        intval($dbTrade['currency_group_id']),
        [],
        $dbTrade['logo'] ?? $dbTrade['banner'],
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
    $trade = $dbCategory['trade_id'];
    $server = $clientTrades[$trade][0];
    $timezone = SIFAS::getServerTimezone($server);
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
        $dbItem['jp_image' . $image] ?? '',
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
    if ($type != 3) {
        $clientItems[$type][$key] = $allItems[$type][$key];
    }
}

Cache::writeMultiJson('trades.js', [
    'items' => $clientItems,
    'trades' => $clientTrades,
    'currencies' => $clientCurrencies,
    'goods' => $clientGoods,
]);
SIF\Cache::writePhp('sifas.trades.php', [
    'cacheSIFASTrades' => $serverTrades,
]);
