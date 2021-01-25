<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$allSteps = [];
$clientSteps = [null];
$sql = 'SELECT * FROM scout_step';
$dbSteps = DB::my_query($sql);
while ($dbStep = $dbSteps->fetch_assoc()) {
    $id = $dbStep['step_id'];
    $count = intval($dbStep['scout_count']);
    $guarantee = intval($dbStep['scout_guarantee'] ?? 0);
    $rateUR = floatval($dbStep['rate_ur']);
    $rateSSR = floatval($dbStep['rate_ssr']);
    $rateSR = floatval($dbStep['rate_sr']);
    $clientSteps[$id] = [
        intval($dbStep['cost_amount']),
        $count,
        $guarantee,
    ];
    if ($rateUR != 1 || $rateSSR != 4 || $rateSR != 15) {
        $clientSteps[$id][] = $rateUR;
        $clientSteps[$id][] = $rateSSR;
        $clientSteps[$id][] = $rateSR;
    }
    $allSteps[$id] = [
        SIF::scoutExpect($count, $guarantee, $rateUR * 0.01, $rateSSR * 0.01, $rateSR * 0.01),
    ];
}

$allGifts = [[[], 0, 0]];
$clientGifts = [null];
$sql = 'SELECT * FROM scout_gift';
$dbGifts = DB::my_query($sql);
while ($dbGift = $dbGifts->fetch_assoc()) {
    $id = $dbGift['gift_group_id'];
    $type = $dbGift['gift_type'];
    $key = intval($dbGift['gift_key']);
    $amount = intval($dbGift['gift_amount']);
    if (!isset($clientGifts[$id])) {
        $clientGifts[$id] = [];
        $allGifts[$id] = [[], 0, 0];
    }
    SIF::itemCollectionAppend($clientGifts[$id], $type, $key, $amount);
    $allGifts[$id][0][] = [$type, $key, $amount];
}

$allItems = $clientItems = [];
$sql = 'SELECT * FROM item_general';
$dbItems = DB::my_query($sql);
while ($dbItem = $dbItems->fetch_assoc()) {
    $type = $dbItem['item_type'];
    $key = $dbItem['item_key'];
    $image = $dbItem['list_box_image'];
    $allItems[$type][$key] = [
        $dbItem['jp_name'],
        $dbItem['en_name'],
        $dbItem['cn_name'],
        $dbItem['jp_image' . $image],
        $dbItem['en_image' . $image],
        $dbItem['cn_image' . $image],
        $dbItem['value_strength'],
        $dbItem['value_adjusted'],
    ];
}

$allUnits = [];
$sql = 'SELECT
    unit.unit_id, unit_number, unit_member, unit_rarity, attribute, jp_name, en_name, cn_name, unit_seal, exp_skill
    FROM unit LEFT JOIN unit_support USING (unit_id)
    WHERE unit.unit_id IN (SELECT gift_key FROM scout_gift WHERE gift_type=1001)
    ORDER BY unit.unit_id ASC';
$dbUnits = DB::my_query($sql);
while ($dbUnit = $dbUnits->fetch_assoc()) {
    $id = $dbUnit['unit_id'];
    $allUnits[$id] = [
        $dbUnit['unit_seal'],
        $dbUnit['exp_skill'],
    ];
}
foreach ($allGifts as $id => $gifts) {
    foreach ($gifts[0] as $gift) {
        if ($gift[0] == 1001) {
            $allGifts[$id][1] += $allUnits[$gift[1]][1] * SIF::VALUE_STRENGTH_EXP_SKILL * $gift[2];
            $allGifts[$id][2] += $allUnits[$gift[1]][1] * SIF::VALUE_ADJUSTED_EXP_SKILL * $gift[2];
        } else {
            $item = $allItems[$gift[0]][$gift[1]];
            $clientItems[$gift[0]][$gift[1]] = array_slice($item, 0, 6);
            $allGifts[$id][1] += $item[6] * $gift[2];
            $allGifts[$id][2] += $item[7] * $gift[2];
        }
    }
}

$clientMemberGroups = [null];
$sql = 'SELECT * FROM member_group';
$dbMemberGroups = DB::my_query($sql);
while ($dbMemberGroup = $dbMemberGroups->fetch_assoc()) {
    $id = $dbMemberGroup['member_group_id'];
    $clientMemberGroups[$id] = [
        $dbMemberGroup['site_name'] ?? $dbMemberGroup['jp_name'],
        intval($dbMemberGroup['group_type']),
        $dbMemberGroup['jp_image'],
    ];
}

$clientSeries = [];
$sql = 'SELECT * FROM unit_series WHERE series_id IN (SELECT content_series FROM box WHERE stepup_id IS NOT NULL)';
$dbSeries = DB::my_query($sql);
while ($dbSeries1 = $dbSeries->fetch_assoc()) {
    $id = $dbSeries1['series_id'];
    $clientSeries[$id] = [
        intval($dbSeries1['series_type']),
        $dbSeries1['jp_name'],
        $dbSeries1['en_name'],
        $dbSeries1['cn_name'],
        $dbSeries1['short_name'],
    ];
}

$clientSettings = [null];
$sql = 'SELECT * FROM scout_stepup';
$dbSettings = DB::my_query($sql);
while ($dbSetting = $dbSettings->fetch_assoc()) {
    $id = $dbSetting['stepup_id'];
    $num = $dbSetting['step_num'];
    $step = intval($dbSetting['step_id']);
    $gift = intval($dbSetting['step_gift_group_id']);
    $clientSettings[$id][$num - 1] = empty($gift) ? $step : [$step, $gift];
}

$allSuffixes = [];
$sql = 'SELECT suffix FROM box_name_suffix WHERE box_type=1';
$dbSuffixes = DB::my_query($sql);
while ($dbSuffix = $dbSuffixes->fetch_assoc()) {
    $allSuffixes[] = $dbSuffix['suffix'];
}

$clientStepups = $serverStepups = $serverSettings = [];
$dictSuffixes = new Dict;
$dictPrefixes = new Dict;
$dictServerSettings = new Dict(true);
$sql = 'SELECT
    box_id, box_server, box_runid, box_name, time_open, time_close,
    limit_total, content_member_group, content_series, rate_limited, rate_value, stepup_id, stepup_reset, disable_value, intro
    FROM box WHERE stepup_id IS NOT NULL AND box_category IN (4,6) AND `virtual`=0';
$dbStepups = DB::my_query($sql);
while ($dbStepup = $dbStepups->fetch_assoc()) {
    $server = intval($dbStepup['box_server']);
    $timezone = SIF::getServerTimezone($server);
    $name = $dbStepup['box_name'];
    $found = false;
    foreach ($allSuffixes as $suffix) {
        if (substr_compare($name, $suffix, -strlen($suffix)) == 0) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        $suffix = '';
    }
    $id = intval($dbStepup['box_id']);
    $runid = intval($dbStepup['box_runid']);
    $limit = intval($dbStepup['limit_total'] ?? 0);
    $rateLimited = floatval($dbStepup['rate_limited'] ?? 0);
    $rateValue = isset($dbStepup['rate_value']) ? floatval($dbStepup['rate_value']) : null;
    $rateValueC = isset($dbStepup['rate_value']) ? $rateValue * 0.01 : 1.0 / 3;
    $stepup = intval($dbStepup['stepup_id']);
    $reset = intval($dbStepup['stepup_reset'] ?? 0);
    $clientStepups[] = [
        $id,
        $server,
        $runid,
        $dictPrefixes->set(mb_substr($name, 0, mb_strlen($name) - mb_strlen($suffix))),
        $dictSuffixes->set($suffix),
        strtotime($dbStepup['time_open'] . $timezone),
        strtotime($dbStepup['time_close'] . $timezone),
        $limit,
        $stepup,
        $reset,
        intval($dbStepup['content_member_group']),
        intval($dbStepup['content_series']),
    ];
    $keySetting = $stepup * 1000000 + $limit * 100000 + $reset * 10000 + intval($rateLimited) * 100 + intval($rateValueC * 100);
    if (($key = $dictServerSettings->find($keySetting)) === false) {
        $key = $dictServerSettings->set($keySetting);
        $setting = $clientSettings[$stepup];
        for ($i = 0, $j = 1, $r = 1, $s = count($setting), $v1 = $v2 = 0, $d1 = $d2 = [1,0,0,0,0,0]; $i < 10; $i++, $j++) {
            if ($j > $s) {
                $j = $reset ? $reset : $s;
                $r++;
            }
            if ($limit && $r > $limit)
                break;
            $step = is_array($setting[$j - 1]) ? $setting[$j - 1][0] : $setting[$j - 1];
            $gift = is_array($setting[$j - 1]) ? $setting[$j - 1][1] : 0;
            $v1 += SIF::scoutValueStrength($allSteps[$step][0], $clientSteps[$step][1], $rateValueC) + $allGifts[$gift][1];
            $v2 += SIF::scoutValueAdjusted($allSteps[$step][0], $clientSteps[$step][1], $rateValueC) + $allGifts[$gift][2];
            $distribution = SIF::scoutDistribution(
                $clientSteps[$step][1], $clientSteps[$step][2],
                ($clientSteps[$step][3] ?? 1) * 0.01, ($clientSteps[$step][4] ?? 4) * 0.01, ($clientSteps[$step][5] ?? 15) * 0.01,
                $rateLimited * 0.01
            );
            $d1n = $d2n = [0,0,0,0,0,0];
            for ($k = 0; $k <= 5; $k++) {
                for ($l = 0; $l <= $k; $l++) {
                    $d1n[$k] += $d1[$l] * ($distribution[0][$k - $l] ?? 0);
                    $d2n[$k] += $d2[$l] * ($distribution[1][$k - $l] ?? 0);
                }
            }
            $d1 = $d1n; $d2 = $d2n;
            $serverSettings[$key][$i] = [
                round($v1, 4),
                round($v2, 4),
                array_map(function($a) {return round($a, 3);}, $d1),
            ];
            if ($rateLimited) {
                $serverSettings[$key][$i][] = array_map(function($a) {return round($a, 3);}, $d2);
            }
        }
    }
    $serverStepups[$id][$server][$runid] = [
        $rateLimited,
        $rateValue,
        $key,
        intval($dbStepup['disable_value']),
        $dbStepup['intro'] ?? '',
    ];
}

Cache::writeMultiJson('box.stepups.js', [
    'items' => $clientItems,
    'memberGroups' => $clientMemberGroups,
    'unitSeries' => $clientSeries,
    'steps' => $clientSteps,
    'stepupGifts' => $clientGifts,
    'stepupSettings' => $clientSettings,
    'stepupNameSuffixes' => $dictSuffixes->getAll(),
    'stepupNames' => $dictPrefixes->getAll(),
    'stepups' => $clientStepups,
]);
Cache::writePhp('box.stepups.php', [
    'cacheStepups' => $serverStepups,
    'cacheStepupSettings' => $serverSettings,
]);
