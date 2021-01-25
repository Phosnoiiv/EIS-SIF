<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$conditions = [null];
$sql = 'SELECT * FROM event_festival_mission_condition_m';
$dbConditions = DB::lt_query('kr/festival_svonly.db_', $sql);
while ($dbCondition = $dbConditions->fetchArray(SQLITE3_ASSOC)) {
    $id = $dbCondition['event_festival_mission_condition_id'];
    $conditions[$id] = [
        $dbCondition['condition_type'],
        json_decode($dbCondition['condition_params']),
    ];
}

$missions = [null];
$sql = 'SELECT level, repeat_type, group_id FROM event_festival_mission_reward_list_m';
$dbRewardGroups = DB::lt_query('kr/festival_svonly.db_', $sql);
while ($dbRewardGroup = $dbRewardGroups->fetchArray(SQLITE3_ASSOC)) {
    $level = $dbRewardGroup['level'];
    $repeat = $dbRewardGroup['repeat_type'];
    $missions[$level][$repeat] = $dbRewardGroup['group_id'];
}
$count = $ref = $sum = [];
$sql = 'SELECT event_festival_mission_id, level, weight, chance_count, time_limit FROM event_festival_mission_m';
$dbMissions = DB::lt_query('kr/festival_svonly.db_', $sql);
while ($dbMission = $dbMissions->fetchArray(SQLITE3_ASSOC)) {
    $id = $dbMission['event_festival_mission_id'];
    $level = $dbMission['level'];
    $count[$level] = $count[$level] ?? 0;
    $ref[$id] = [$level, $count[$level]++];
    $sum[$level] = $sum[$level] ?? 0;
    $sum[$level] += $dbMission['weight'];
    $missions[$level][2][] = [
        $dbMission['weight'],
        $dbMission['chance_count'],
        $dbMission['time_limit'],
        [],
    ];
}
for ($level = count($missions) - 1; $level >= 1; $level--) {
    for ($mission = count($missions[$level][2]) - 1; $mission >= 0; $mission--) {
        $missions[$level][2][$mission][0] = round($missions[$level][2][$mission][0] / $sum[$level], 4);
    }
}
$sql = 'SELECT * FROM event_festival_mission_achievement_list_m';
$dbDetails = DB::lt_query('kr/festival_svonly.db_', $sql);
while ($dbDetail = $dbDetails->fetchArray(SQLITE3_ASSOC)) {
    $id = $dbDetail['event_festival_mission_id'];
    $missions[$ref[$id][0]][2][$ref[$id][1]][3][] = $dbDetail['event_festival_mission_condition_id'];
}

$rewards = [null];
$listUnits = $sum = [];
$sql = 'SELECT group_id, item_id, amount, add_type, weight FROM event_festival_mission_reward_item_m';
$dbRewards = DB::lt_query('jp/festival.db_', $sql);
while ($dbReward = $dbRewards->fetchArray(SQLITE3_ASSOC)) {
    $id = $dbReward['group_id'];
    $sum[$id] = $sum[$id] ?? 0;
    $sum[$id] += $dbReward['weight'];
    $rewards[$id][] = [
        $dbReward['add_type'],
        $dbReward['item_id'],
        $dbReward['amount'],
        $dbReward['weight'],
    ];
    if ($dbReward['add_type'] == 1001 && !in_array($dbReward['item_id'], $listUnits)) {
        $listUnits[] = $dbReward['item_id'];
    }
}
$sql = 'SELECT group_id, bonus_id, bonus_param, weight FROM event_festival_mission_reward_bonus_m';
$dbRewards = DB::lt_query('jp/festival.db_', $sql);
while ($dbReward = $dbRewards->fetchArray(SQLITE3_ASSOC)) {
    $id = $dbReward['group_id'];
    $sum[$id] = $sum[$id] ?? 0;
    $sum[$id] += $dbReward['weight'];
    $rewards[$id][] = [
        1,
        $dbReward['bonus_id'],
        $dbReward['bonus_param'],
        $dbReward['weight'],
    ];
}
for ($group = count($rewards) - 1; $group >= 1; $group--) {
    for ($reward = count($rewards[$group]) - 1; $reward >= 0; $reward--) {
        $rewards[$group][$reward][3] = round($rewards[$group][$reward][3] / $sum[$group], 4);
    }
}

$items = [];
$sql = 'SELECT
    item_type, item_key, jp_name, cn_name, jp_image2, cn_image2
    FROM item_general WHERE item_type IN (3006,8000)';
$dbItems = DB::my_query($sql);
while ($dbItem = $dbItems->fetch_assoc()) {
    $type = $dbItem['item_type'];
    $key = $dbItem['item_key'];
    $items[$type][$key] = [
        $dbItem['cn_name'] ?? $dbItem['jp_name'],
        $dbItem['cn_image2'] ?? $dbItem['jp_image2'],
    ];
}

$filterUnits = implode(',', $listUnits);
$members = [];
$sql = "SELECT member_id, jp_name, cn_name FROM member WHERE member_id IN (
    SELECT unit_member FROM unit WHERE unit_id IN ($filterUnits)
)";
$dbMembers = DB::my_query($sql);
while ($dbMember = $dbMembers->fetch_assoc()) {
    $id = $dbMember['member_id'];
    $members[$id] = $dbMember['cn_name'] ?? $dbMember['jp_name'];
}

$units = [];
$sql = "SELECT
    unit_id, unit_member, unit_rarity, attribute, jp_name, cn_name
    FROM unit WHERE unit_id IN ($filterUnits)";
$dbUnits = DB::my_query($sql);
while ($dbUnit = $dbUnits->fetch_assoc()) {
    $id = $dbUnit['unit_id'];
    $units[$id] = [
        intval($dbUnit['unit_member']),
        intval($dbUnit['unit_rarity']),
        intval($dbUnit['attribute']),
        $dbUnit['cn_name'] ?? $dbUnit['jp_name'] ?? '',
    ];
}

Cache::writeMultiJson('event.mf.mission.js', [
    'conditions' => $conditions,
    'missions' => $missions,
    'rewards' => $rewards,
    'items' => $items,
    'members' => $members,
    'units' => $units,
]);
