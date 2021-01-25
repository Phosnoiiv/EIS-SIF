<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$items = [];
$itemValueStrength = [];
$itemValueAdjusted = [];
$db_items = DB::my_query('SELECT * FROM item_general
    WHERE item_type IN (1000,8000)
');
while ($item = $db_items->fetch_assoc()) {
    $type = intval($item['item_type']);
    $key = intval($item['item_key']);
    $image = $item['list_box_image'];
    $items[$type][$key] = [
        $item['jp_name'] ?? $item['cn_name'] ?? $item['en_name'],
        $item['jp_image' . $image] ?? $item['cn_image' . $image] ?? $item['en_image' . $image],
    ];
    $itemValueStrength[$type][$key] = floatval($item['value_strength']);
    $itemValueAdjusted[$type][$key] = floatval($item['value_adjusted']);
}
Cache::writeJson('item.step.json', $items);

$members = [];
$db_members = DB::my_query('SELECT
        member_id, jp_name, cn_name
    FROM member
    WHERE member_id IN (
        SELECT unit_member FROM unit
        WHERE unit_id IN (
            SELECT gift_key FROM scout_gift
            WHERE gift_type=1001
        )
    )
');
while ($member = $db_members->fetch_assoc()) {
    $id = intval($member['member_id']);
    $members[$id] = $member['cn_name'] ?? $member['jp_name'];
}
$units = [];
$unitValueStrength = [];
$unitValueAdjusted = [];
$db_units = DB::my_query('SELECT
        unit.unit_id, unit_number, unit_member, unit_rarity, attribute,
        jp_name, en_name, cn_name, unit_seal,
        exp_normal, exp_skill
    FROM unit
    LEFT JOIN unit_support ON unit.unit_id=unit_support.unit_id
    WHERE unit.unit_id IN (
        SELECT gift_key FROM scout_gift
        WHERE gift_type=1001
    )
    ORDER BY unit.unit_id ASC
');
while ($unit = $db_units->fetch_assoc()) {
    $id = intval($unit['unit_id']);
    $units[$id] = [
        intval($unit['unit_number']),
        intval($unit['unit_member']),
        intval($unit['unit_rarity']),
        intval($unit['attribute']),
        $unit['jp_name'] ?? '',
        $unit['en_name'] ?? '',
        $unit['cn_name'] ?? '',
    ];
    if (!empty($unit['exp_skill'])) {
        $unitValueStrength[$id] =
            $unit['exp_skill'] * SIF::VALUE_STRENGTH_EXP_SKILL;
        $unitValueAdjusted[$id] =
            $unit['exp_skill'] * SIF::VALUE_ADJUSTED_EXP_SKILL;
    }
}
Cache::writeMultiJson('unit.step.js', [
    'members' => $members,
    'units' => $units,
]);

$gifts = [null];
$giftValueStrength = [];
$giftValueAdjusted = [];
$db_gifts = DB::my_query('SELECT
        gift_group_id, gift_type, gift_key, gift_amount
    FROM scout_gift');
while ($gift = $db_gifts->fetch_assoc()) {
    $id = intval($gift['gift_group_id']);
    $type = $gift['gift_type'];
    $key = intval($gift['gift_key']);
    $amount = intval($gift['gift_amount']);
    switch ($type) {
        case 1000: // Item
        case 1001: // Unit
        case 8000: // Recovery item
            $gifts[$id][$type] = $gifts[$id][$type] ?? [];
            for ($i = $amount; $i > 0; $i--) {
                $gifts[$id][$type][] = $key;
            }
            break;
        case 5200: // Background
            $gifts[$id][$type] = $gifts[$id][$type] ?? [];
            $gifts[$id][$type][] = $key;
            break;
    }
    $valueStrength = $valueAdjusted = 0;
    switch ($type) {
        case 1000: // Item
        case 8000: // Recovery item
            $valueStrength += $itemValueStrength[$type][$key] * $amount;
            $valueAdjusted += $itemValueAdjusted[$type][$key] * $amount;
            break;
        case 1001: // Unit
            $valueStrength += ($unitValueStrength[$key] ?? 0) * $amount;
            $valueAdjusted += ($unitValueAdjusted[$key] ?? 0) * $amount;
            break;
    }
    $giftValueStrength[$id] = $giftValueStrength[$id] ?? 0;
    $giftValueStrength[$id] += $valueStrength;
    $giftValueAdjusted[$id] = $giftValueAdjusted[$id] ?? 0;
    $giftValueAdjusted[$id] += $valueAdjusted;
}
Cache::writeJson('gift.step.json', $gifts);

$steps = [null];
$stepExpectedUr = [];
$stepValueStrength = [];
$stepValueAdjusted = [];
$db_steps = DB::my_query('SELECT
        step_id, cost_amount, scout_count, scout_guarantee,
        rate_ur, rate_ssr, rate_sr
    FROM scout_step');
while ($step = $db_steps->fetch_assoc()) {
    $id = intval($step['step_id']);
    $count = intval($step['scout_count']);
    $guarantee = intval($step['scout_guarantee'] ?? 0);
    $steps[$id] = [
        intval($step['cost_amount']),
        $count,
        $guarantee,
    ];
    $rarities = ['ur', 'ssr', 'sr'];
    foreach ($rarities as $rarity) {
        $steps[$id][] = $rate = floatval($step['rate_' . $rarity]);
        ${'rate_' . $rarity} = $rate * 0.01;
    }
    $expect = SIF::scoutExpect($count, $guarantee, $rate_ur, $rate_ssr, $rate_sr);
    $stepExpectedUr[$id] = $expect['ur'];
    $stepValueStrength[$id] = SIF::scoutValueStrength($expect, $count);
    $stepValueAdjusted[$id] = SIF::scoutValueAdjusted($expect, $count);
}
Cache::writeJson('scout.step.json', $steps);

$pairs = [null];
$patterns = [null];
$db_patterns = DB::my_query('SELECT
        stepup_id, step_id, step_gift_group_id
    FROM scout_stepup
    ORDER BY stepup_id ASC, step_num ASC
');
while ($pattern = $db_patterns->fetch_assoc()) {
    $id = intval($pattern['stepup_id']);
    $step = intval($pattern['step_id']);
    $gift = intval($pattern['step_gift_group_id']);
    $pair = [
        $step,
        $gift,
        round($stepExpectedUr[$step], 3),
        round($stepValueStrength[$step] + ($giftValueStrength[$gift] ?? 0), 2),
        round($stepValueAdjusted[$step] + ($giftValueAdjusted[$gift] ?? 0), 2),
    ];
    if (($key_pair = array_search($pair, $pairs)) === false) {
        $key_pair = count($pairs);
        $pairs[$key_pair] = $pair;
    }
    $patterns[$id] = $patterns[$id] ?? [];
    $patterns[$id][] = $key_pair;
}
Cache::writeMultiJson('box.stepup.pattern.js', [
    'pairs' => $pairs,
    'patterns' => $patterns,
]);

$stepups = [];
$stepup_names = [''];
$db_stepups = DB::my_query('SELECT
        box_id, box_server, box_runid, box_name, time_open, time_close,
        limit_total, stepup_id, stepup_reset
    FROM box WHERE stepup_id IS NOT NULL AND box_category IN (4,6) AND `virtual`=0');
while ($stepup = $db_stepups->fetch_assoc()) {
    if (($key_name = array_search($stepup['box_name'], $stepup_names)) === false) {
        $key_name = count($stepup_names);
        $stepup_names[$key_name] = $stepup['box_name'];
    }
    $stepups[] = [
        intval($stepup['box_id']),
        intval($stepup['box_server']),
        intval($stepup['box_runid']),
        $key_name,
        strtotime($stepup['time_open'] . '+0000'),
        strtotime($stepup['time_close'] . '+0000'),
        intval($stepup['limit_total'] ?? 0),
        intval($stepup['stepup_id']),
        intval($stepup['stepup_reset'] ?? 0),
    ];
}
Cache::writeMultiJson('box.stepup.js', [
    'boxNames' => $stepup_names,
    'stepups' => $stepups,
]);
