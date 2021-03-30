<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$clientTemplates = [null];
$sql = 'SELECT * FROM box_template';
$dbTemplates = DB::my_query($sql);
while ($dbTemplate = $dbTemplates->fetch_assoc()) {
    $id = $dbTemplate['id'];
    $clientTemplates[$id] = [
        floatval($dbTemplate['rate_ur']),
        floatval($dbTemplate['rate_ssr']),
        floatval($dbTemplate['rate_sr']),
        floatval($dbTemplate['rate_r']),
        intval($dbTemplate['guarantee']),
        intval($dbTemplate['guarantee_count']),
        floatval($dbTemplate['rate_signed']),
    ];
}

$clientCosts = [null];
$sql = 'SELECT * FROM box_cost';
$dbCosts = DB::my_query($sql);
while ($dbCost = $dbCosts->fetch_assoc()) {
    $id = $dbCost['cost_group_id'];
    $clientCosts[$id][] = [
        intval($dbCost['item_type']),
        intval($dbCost['item_key']),
        intval($dbCost['amount']),
        intval($dbCost['count']),
    ];
}

$clientSteps = [null];
$sql = 'SELECT * FROM scout_step';
$dbSteps = DB::my_query($sql);
while ($dbStep = $dbSteps->fetch_assoc()) {
    $id = $dbStep['step_id'];
    $rateUR = floatval($dbStep['rate_ur']);
    $rateSSR = floatval($dbStep['rate_ssr']);
    $rateSR = floatval($dbStep['rate_sr']);
    $clientSteps[$id] = [
        intval($dbStep['cost_amount']),
        intval($dbStep['scout_count']),
        intval($dbStep['scout_guarantee']),
    ];
    if ($rateUR != 1 || $rateSSR != 4 || $rateSR != 15) {
        $clientSteps[$id][] = $rateUR;
        $clientSteps[$id][] = $rateSSR;
        $clientSteps[$id][] = $rateSR;
    }
}

$clientStepupSettings = [null];
$sql = 'SELECT * FROM scout_stepup';
$dbSettings = DB::my_query($sql);
while ($dbSetting = $dbSettings->fetch_assoc()) {
    $id = $dbSetting['stepup_id'];
    $num = $dbSetting['step_num'];
    $step = intval($dbSetting['step_id']);
    $gift = intval($dbSetting['step_gift_group_id']);
    $clientStepupSettings[$id][$num - 1] = empty($gift) ? $step : [$step, $gift];
}

$clientKnapsackSettings = [null];
$sql = 'SELECT * FROM box_knapsack';
$dbSettings = DB::my_query($sql);
while ($dbSetting = $dbSettings->fetch_assoc()) {
    $id = $dbSetting['id'];
    $clientKnapsackSettings[$id] = [
        intval($dbSetting['capacity_ur_selected']),
        intval($dbSetting['capacity_ur_limited']),
        intval($dbSetting['capacity_ur']),
        intval($dbSetting['capacity_ssr']),
        intval($dbSetting['capacity_sr']),
        intval($dbSetting['capacity_r']),
    ];
}

$clientBagSettings = [null];
$sql = 'SELECT * FROM box_bag';
$dbSettings = DB::my_query($sql);
while ($dbSetting = $dbSettings->fetch_assoc()) {
    $id = $dbSetting['bag_id'];
    $clientBagSettings[$id][] = [
        floatval($dbSetting['rate_ur']),
        floatval($dbSetting['rate_ssr']),
        floatval($dbSetting['rate_sr']),
        floatval($dbSetting['rate_r']),
    ];
}

$clientItems = $clientFreeItems = $allItems = [];
$sql = 'SELECT * FROM item_general WHERE (item_type,item_key) IN (SELECT DISTINCT item_type,item_key FROM box_cost) OR (item_type,item_key) IN (SELECT DISTINCT item_type,item_key FROM box_stamp)';
$dbItems = DB::my_query($sql);
while ($dbItem = $dbItems->fetch_assoc()) {
    $type = intval($dbItem['item_type']);
    $key = intval($dbItem['item_key']);
    $clientItems[$type][$key] = [
        $dbItem['jp_image2'] ?? '',
        $dbItem['en_image2'] != $dbItem['jp_image2'] ? $dbItem['en_image2'] ?? '' : '',
        $dbItem['cn_image2'] != $dbItem['jp_image2'] ? $dbItem['cn_image2'] ?? '' : '',
        $dbItem['jp_name'] ?? '',
        $dbItem['en_name'] ?? '',
        $dbItem['cn_name'] ?? '',
        $dbItem['jp_desc'] ?? '',
        $dbItem['en_desc'] ?? '',
        $dbItem['cn_desc'] ?? '',
        $dbItem['intro'] ?? '',
        $dbItem['jp_image1'] ?? '',
        $dbItem['en_image1'] != $dbItem['jp_image1'] ? $dbItem['en_image1'] ?? '' : '',
        $dbItem['cn_image1'] != $dbItem['jp_image1'] ? $dbItem['cn_image1'] ?? '' : '',
    ];
    $freeAmountJP = intval($dbItem['sim_box_free_jp']);
    $freeAmountEN = intval($dbItem['sim_box_free_en']);
    $freeAmountCN = intval($dbItem['sim_box_free_cn']);
    if ($freeAmountJP + $freeAmountEN + $freeAmountCN) {
        $clientFreeItems[] = [$type, $key, $freeAmountJP, $freeAmountEN, $freeAmountCN];
    }
}

$clientSIGroups = [null];
$sql = 'SELECT * FROM box_si';
$columns = [['i','group'],['i','rarity'],['i','si']];
$dbSIGroups = DB::mySelect($sql, $columns, '');
foreach ($dbSIGroups as $dbSIGroup) {
    $clientSIGroups[$dbSIGroup[0]][$dbSIGroup[1]][] = $dbSIGroup[2];
}

$sql = 'SELECT * FROM box_unit_si';
$columns = [['i','si1']];
$clientSIUnits = DB::mySelect($sql, $columns, 'unit');

$sql = 'SELECT * FROM item_si WHERE id IN (SELECT DISTINCT si FROM box_si UNION SELECT DISTINCT si1 FROM box_unit_si) ORDER BY id ASC';
$columns = [['i','level'],['s','jp_name',''],['s','en_name',''],['s','cn_name',''],['s','image1'],['i','desc_string'],['d','value1']];
$clientSIs = DB::mySelect($sql, $columns, 'id');

$sql = 'SELECT * FROM item_si_string WHERE id IN (' . implode(',', array_unique(array_column($clientSIs, 5))) . ') ORDER BY id ASC';
$columns = [['s','jp_string',''],['s','en_string',''],['s','cn_string','']];
$clientSIStrings = DB::mySelect($sql, $columns, 'id');

$clientStamps = [null];
$sql = 'SELECT * FROM box_stamp';
$dbStamps = DB::my_query($sql);
while ($dbStamp = $dbStamps->fetch_assoc()) {
    $id = $dbStamp['id'];
    $type = intval($dbStamp['item_type']);
    $key = intval($dbStamp['item_key']);
    if ($type) {
        $clientStamps[$id]['i'] = [
            $type,
            $key,
            intval($dbStamp['item_amount']),
        ];
    }
    if ($guarantee = intval($dbStamp['guarantee'])) {
        $clientStamps[$id]['g'] = $guarantee;
    }
    if ($pick = intval($dbStamp['pick_flag'])) {
        $clientStamps[$id]['p'] = [
            $pick,
            intval($dbStamp['pick_count']),
        ];
    }
    $images = [
        $dbStamp['jp_image'] ?? '',
        $dbStamp['en_image'] != $dbStamp['jp_image'] ? $dbStamp['en_image'] ?? '' : '',
        $dbStamp['cn_image'] != $dbStamp['jp_image'] ? $dbStamp['cn_image'] ?? '' : '',
    ];
    if ($images[0] || $images[1] || $images[2]) {
        $clientStamps[$id]['m'] = $images;
    }
}

$clientSheets = [null];
$sql = 'SELECT * FROM box_stamp_sheet';
$dbSheets = DB::my_query($sql);
while ($dbSheet = $dbSheets->fetch_assoc()) {
    $id = $dbSheet['id'];
    $clientSheets[$id] = [
        intval($dbSheet['loop_from']),
        intval($dbSheet['loop_till']),
    ];
    for ($i = 1; $i <= 10 && $stamp = intval($dbSheet['stamp' . $i]); $i++) {
        $clientSheets[$id][] = $stamp;
    }
}

$allSuffixes = [];
$sql = 'SELECT * FROM box_name_suffix';
$dbSuffixes = DB::my_query($sql);
while ($dbSuffix = $dbSuffixes->fetch_assoc()) {
    $allSuffixes[] = $dbSuffix['suffix'];
}

$replaceIntroFrom = $replaceIntroTo = [];
$sql = 'SELECT * FROM box_intro_string';
$dbStrings = DB::my_query($sql);
while ($dbString = $dbStrings->fetch_assoc()) {
    $replaceIntroFrom[] = '[' . $dbString['key'] . ']';
    $replaceIntroTo[] = $dbString['string'];
}

$sql = 'SELECT * FROM box2';
$columns = [['s','name'],['s','display_name']];
$boxes2 = DB::mySelect($sql, $columns, 'key');

$clientNormalBoxes = $clientStepups = $clientKnapsacks = $clientBags = [null, [], [], []];
$serverBoxes = [];
$sql = 'SELECT * FROM box WHERE disable_sim=0';
$dbBoxes = DB::my_query($sql);
while ($dbBox = $dbBoxes->fetch_assoc()) {
    $id = intval($dbBox['box_id']);
    $server = $dbBox['box_server'];
    $timezone = SIF::getServerTimezone($server);
    $runid = intval($dbBox['box_runid']);
    $box2 = empty($dbBox['box2']) ? [] : $boxes2[$dbBox['box2']];
    $box = [
        $runid ? [$id, $runid] : $id,
        $dbBox['display_name'] ?? $box2[1],
        intval($dbBox['virtual']),
        empty($dbBox['time_open']) ? 0 : strtotime($dbBox['time_open'] . $timezone),
        empty($dbBox['time_close']) ? 0 : strtotime($dbBox['time_close'] . $timezone),
        intval($dbBox['member_category']),
        intval($dbBox['sim_order']),
    ];
    if (!empty($stepupSettingID = intval($dbBox['stepup_id']))) {
        $reset = intval($dbBox['stepup_reset']);
        $limit = intval($dbBox['limit_total']);
        $clientStepups[$server][] = array_merge($box, [
            $stepupSettingID,
            $reset ? -$reset : $limit,
        ]);
    } elseif (!empty($knapsackSettingID = intval($dbBox['knapsack']))) {
        $clientKnapsacks[$server][] = array_merge($box, [
            $knapsackSettingID,
            intval($dbBox['cost_group']),
        ]);
    } elseif (!empty($bagID = intval($dbBox['bag']))) {
        $clientBags[$server][] = array_merge($box, [
            $bagID,
            intval($dbBox['cost_group']),
        ]);
    } else {
        $clientNormalBoxes[$server][] = array_merge($box, [
            intval($dbBox['cost_group']),
            intval($dbBox['template']),
            intval($dbBox['stamp_sheet']),
        ]);
    }
    $key = $id . '-' . $server . '-' . $runid;
    $serverBoxes[$key] = [
        $dbBox['box_name'],
        intval($dbBox['bonus']),
        intval($dbBox['low']),
        intval($dbBox['select']),
        str_replace($replaceIntroFrom, $replaceIntroTo, $dbBox['ad'] ?? ''),
        $dbBox['intro'] ?? '',
        [],
    ];
    if (!empty($dbBox['content_series'])) {
        $serverBoxes[$key][6][] = intval($dbBox['content_series']);
    }
    for ($i = 2; $i <= 2; $i++) {
        if (empty($dbBox['series' . $i]))
            break;
        $serverBoxes[$key][6][] = intval($dbBox['series' . $i]);
    }
}

$allUnits = [];
$sql = 'SELECT * FROM unit';
$dbUnits = DB::my_query($sql);
while ($dbUnit = $dbUnits->fetch_assoc()) {
    $id = $dbUnit['unit_id'];
    $allUnits[$id] = [
        intval($dbUnit['unit_number']),
        intval($dbUnit['unit_member']),
        intval($dbUnit['unit_rarity']),
        intval($dbUnit['attribute']),
        intval($dbUnit['unit_type']),
        intval($dbUnit['unit_series']),
        intval($dbUnit['unit_skill']),
        $dbUnit['jp_name'] ?? '',
        $dbUnit['en_name'] ?? '',
        $dbUnit['cn_name'] ?? '',
        empty($dbUnit['jp_inbox']) ? 0 : strtotime($dbUnit['jp_inbox'] . SIF::getServerTimezone(1)),
        empty($dbUnit['en_inbox']) ? 0 : strtotime($dbUnit['en_inbox'] . SIF::getServerTimezone(2)),
        empty($dbUnit['cn_inbox']) ? 0 : strtotime($dbUnit['cn_inbox'] . SIF::getServerTimezone(3)),
    ];
}

$allSkills = [];
$sql = 'SELECT * FROM unit_skill';
$dbSkills = DB::my_query($sql);
while ($dbSkill = $dbSkills->fetch_assoc()) {
    $id = $dbSkill['id'];
    $allSkills[$id] = [
        intval($dbSkill['effect_type']),
        intval($dbSkill['trigger_type']),
    ];
}

$allMemberGroups = [];
$sql = 'SELECT * FROM member_group_l';
$dbMemberGroupLists = DB::my_query($sql);
while ($dbMemberGroupList = $dbMemberGroupLists->fetch_assoc()) {
    $id = $dbMemberGroupList['member_group_id'];
    $allMemberGroups[$id][0][] = intval($dbMemberGroupList['member_id']);
}

$serverContents = $serverUnits = $serverSkills = $listMembers = $listSeries = [];
$sql = 'SELECT * FROM box_content LEFT JOIN box_content_family ON family=box_content_family.id';
$dbContents = DB::my_query($sql);
$filterRarity = [
    0 => [2,3,4,5],
    1 => [1], 2 => [2], 3 => [3], 4 => [4], 5 => [5],
    11 => [2,3,5],
    21 => [3,4,5], 22 => [4,5],
    31 => [3,5],
];
while ($dbContent = $dbContents->fetch_assoc()) {
    $id = $dbContent['box_id'];
    $server = $dbContent['box_server'];
    $timezone = SIF::getServerTimezone($server);
    $runid = $dbContent['box_runid'];
    $key = $id . '-' . $server . '-' . $runid;
    if (!isset($serverContents[$key])) {
        $serverContents[$key] = [[], [], [], [], [], [], [], []];
        $currentUnits = [];
    }
    $group = $dbContent['content_group_id'];
    if (!isset($serverContents[$key][0][$group])) {
        $serverContents[$key][0][$group] = [
            intval($dbContent['flag']),
            floatval($dbContent['content_group_rate']),
            $dbContent['content_group_name'] ?? '',
            intval($dbContent['si_fixed']),
            intval($dbContent['si_group']),
        ];
        for ($i = 1; $i <= 7; $i++) {
            $serverContents[$key][$i][$group] = [];
        }
    }
    $periodServer = empty($dbContent['period_server']) ? $server : $dbContent['period_server'];
    $from = empty($dbContent['period_from']) ? 0 : SIF::toTimestamp($dbContent['period_from'], $periodServer);
    $to = empty($dbContent['period_till']) ? time() : SIF::toTimestamp($dbContent['period_till'], $periodServer);
    foreach ($allUnits as $id => $unit) {
        if (in_array($id, $currentUnits))
            continue;
        if ($unit[9 + $periodServer] < $from || $unit[9 + $periodServer] > $to)
            continue;
        if (empty($type = $dbContent['filter_type']) && empty($dbContent['filter_series']) && $unit[4] > 2)
            continue;
        if (!empty($type) && $unit[4] != $type && !($type==10 && ($unit[4]==1 || $unit[2]==5)))
            continue;
        if (!empty($series = $dbContent['filter_series']) && $unit[5] != $series) continue;
        if (!empty($memberGroup = $dbContent['filter_member_group']) && !in_array($unit[1], $allMemberGroups[$memberGroup][0]))
            continue;
        if (!in_array($unit[2], $filterRarity[intval($dbContent['filter_rarity'])]))
            continue;
        if (!empty($attribute = $dbContent['filter_attribute']) && $unit[3] != $attribute)
            continue;
        $family = $unit[2];
        if (in_array($dbContent['flag'], [1, 2])) {
            $family = 5 + $dbContent['flag'];
        }
        $serverContents[$key][$family][$group][] = $id;
        $currentUnits[] = $id;
        if (!isset($serverUnits[$id])) {
            $serverUnits[$id] = array_slice($allUnits[$id], 0, 10);
        }
        if (!empty($unit[6]) && !isset($serverSkills[$unit[6]])) {
            $serverSkills[$unit[6]] = $allSkills[$unit[6]];
        }
        if (!in_array($unit[1], $listMembers)) {
            $listMembers[] = $unit[1];
        }
        if (!in_array($unit[5], $listSeries)) {
            $listSeries[] = $unit[5];
        }
    }
    for ($i = 0; $i <= 7; $i++) {
        ksort($serverContents[$key][$i]);
    }
}

$clientMembers = [];
$sql = 'SELECT * FROM member WHERE member_id IN (' . implode(',', $listMembers) . ')';
$dbMembers = DB::my_query($sql);
while ($dbMember = $dbMembers->fetch_assoc()) {
    $id = $dbMember['member_id'];
    $clientMembers[$id] = [
        $dbMember['jp_name'] ?? '',
        $dbMember['en_name'] ?? '',
        $dbMember['cn_name'] ?? '',
    ];
}

$clientSeries = [];
$sql = 'SELECT * FROM unit_series WHERE series_id IN (' . implode(',', $listSeries) . ')';
$dbSeries = DB::my_query($sql);
while ($dbSeries1 = $dbSeries->fetch_assoc()) {
    $id = $dbSeries1['series_id'];
    $clientSeries[$id] = [
        $dbSeries1['jp_name'] ?? '',
        $dbSeries1['en_name'] ?? '',
        $dbSeries1['cn_name'] ?? $dbSeries1['cn_append'] ?? '',
    ];
    if (!empty($dbSeries1['album1'])) {
        $clientSeries[$id][] = $dbSeries1['album1'];
    }
    if (!empty($dbSeries1['album2'])) {
        $clientSeries[$id][] = $dbSeries1['album2'];
    }
}

Cache::writeMultiJson('boxes-sim.js', [
    'items' => $clientItems,
    'SIs' => $clientSIs,
    'SIStrings' => $clientSIStrings,
    'freeItems' => $clientFreeItems,
    'members' => $clientMembers,
    'series' => $clientSeries,
    'boxTemplates' => $clientTemplates,
    'boxCosts' => $clientCosts,
    'boxStamps' => $clientStamps,
    'boxStampSheets' => $clientSheets,
    'SIGroups' => $clientSIGroups,
    'SIUnits' => $clientSIUnits,
    'normalBoxes' => $clientNormalBoxes,
    'steps' => $clientSteps,
    'stepupSettings' => $clientStepupSettings,
    'stepups' => $clientStepups,
    'knapsackSettings' => $clientKnapsackSettings,
    'knapsacks' => $clientKnapsacks,
    'bagSettings' => $clientBagSettings,
    'bags' => $clientBags,
]);
Cache::writePhp('boxes-units.php', [
    'cacheBoxUnits' => $serverUnits,
    'cacheBoxSkills' => $serverSkills,
]);
foreach ($serverBoxes as $key => $box) {
    Cache::writeJson('boxes/' . $key . '.json', [
        'box' => $box,
        'contents' => $serverContents[$key],
    ], true);
}
