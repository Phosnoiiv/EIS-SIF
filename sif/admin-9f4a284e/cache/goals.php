<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$clientCategories = $clientGoals = $clientStrings = $clientUnitGroups = $clientMemberGroups = [null, [], null, []];
$listTracks = [];
foreach ([1 => 'jp', 3 => 'cn'] as $server => $serverName) {
    $sfxGL = $server>1 ? '_en' : '';
    $listTypes = $listUnitGroups = $listMemberGroups = [];
    $sql = 'SELECT * FROM achievement_m';
    $dbGoals = DB::lt_query($serverName . '/achievement.db_', $sql);
    $now = time();
    $clientIndex = -1;
    while ($dbGoal = $dbGoals->fetchArray(SQLITE3_ASSOC)) {
        $timeOpen = strtotime($dbGoal['start_date'] . SIF::getServerTimezone($server));
        $timeClose = strtotime($dbGoal['end_date'] . SIF::getServerTimezone($server));
        $yearClose = date('Y', $timeClose);
        $type = $dbGoal['achievement_type'];
        if (empty($dbGoal["description$sfxGL"]) && !empty($dbGoal['end_date']) && $timeClose >= $now && $yearClose < 2024) {
            for ($maxParam = 11; $maxParam > 0 && empty($dbGoal['params' . $maxParam]); $maxParam--);
            $clientGoals[$server][++$clientIndex] = [
                $dbGoal["title$sfxGL"],
                $timeOpen > $now ? $timeOpen : 0,
                $timeClose,
                $dbGoal['reset_type'],
                $dbGoal['achievement_filter_category_id'] ?? 0,
                $dbGoal['achievement_filter_type_id'] ?? 0,
                $type,
            ];
            for ($i = 1; $i <= $maxParam; $i++) {
                if ($maxParam >= $i) {
                    $clientGoals[$server][$clientIndex][] = $params[$i] = $dbGoal['params' . $i];
                }
            }
            if (!in_array($type, $listTypes)) {
                $listTypes[] = $type;
            }
            switch ($type) {
                case 6:
                    if (!in_array($params[1], $listUnitGroups)) {
                        $listUnitGroups[] = $params[1];
                    }
                    break;
                case 7:
                    if (!in_array($params[1], $listMemberGroups)) {
                        $listMemberGroups[] = $params[1];
                    }
                    break;
                case 9:
                    if (!in_array($params[1], $listTracks)) {
                        $listTracks[] = $params[1];
                    }
                    if (!in_array($params[2], $listMemberGroups)) {
                        $listMemberGroups[] = $params[2];
                    }
                    break;
                case 32:
                case 37:
                    if (!in_array($params[1], $listTracks)) {
                        $listTracks[] = $params[1];
                    }
                    break;
                case 50:
                    if (!in_array($params[1], $listTracks)) {
                        $listTracks[] = $params[1];
                    }
                    if ($params[7] == 1 && !in_array($params[8], $listUnitGroups)) {
                        $listUnitGroups[] = $params[8];
                    }
                    if ($params[7] == 2 && !in_array($params[8], $listMemberGroups)) {
                        $listMemberGroups[] = $params[8];
                    }
                    break;
            }
        }
    }
    $sql = 'SELECT * FROM achievement_description_m';
    $dbStrings = DB::lt_query($serverName . '/achievement.db_', $sql);
    while ($dbString = $dbStrings->fetchArray(SQLITE3_ASSOC)) {
        $type = $dbString['achievement_type'];
        if (!in_array($type, $listTypes))
            continue;
        $key = $dbString['common_id'];
        $clientStrings[$server][$type][$key] = $dbString["description$sfxGL"];
    }
    $sql = 'SELECT * FROM achievement_unit_group_name_m';
    $dbUnitGroups = DB::lt_query($serverName . '/achievement.db_', $sql);
    while ($dbUnitGroup = $dbUnitGroups->fetchArray(SQLITE3_ASSOC)) {
        $id = $dbUnitGroup['achievement_unit_group_id'];
        if (!in_array($id, $listUnitGroups))
            continue;
        $clientUnitGroups[$server][$id] = $dbUnitGroup["name$sfxGL"];
    }
    $sql = 'SELECT * FROM achievement_unit_type_group_name_m';
    $dbMemberGroups = DB::lt_query($serverName . '/achievement.db_', $sql);
    while ($dbMemberGroup = $dbMemberGroups->fetchArray(SQLITE3_ASSOC)) {
        $id = $dbMemberGroup['achievement_unit_type_group_id'];
        if (!in_array($id, $listMemberGroups))
            continue;
        $clientMemberGroups[$server][$id] = $dbMemberGroup["name$sfxGL"];
    }
    $sql = 'SELECT * FROM achievement_filter_type_m';
    $dbCategories = DB::lt_query($serverName . '/achievement.db_', $sql);
    while ($dbCategory = $dbCategories->fetchArray(SQLITE3_ASSOC)) {
        $id = $dbCategory['achievement_filter_type_id'];
        $clientCategories[$server][$id] = $dbCategory["name$sfxGL"];
    }
}

$clientTracks = [];
$sql = 'SELECT * FROM live_track';
$dbTracks = DB::my_query($sql);
while ($dbTrack = $dbTracks->fetch_assoc()) {
    $id = $dbTrack['track_id'];
    if (!in_array($id, $listTracks))
        continue;
    $track = $clientTracks[$id] = [
        intval($dbTrack['attribute']),
        $dbTrack['jp_name'],
        $dbTrack['en_name'],
        $dbTrack['cn_name'],
    ];
}

$sql = 'SELECT * FROM mission_tag';
$columns = [['s','jp_tag'],['s','cn_tag']];
$serverTags = DB::ltSelect('cache.s3db', $sql, $columns, 'id');

Cache::writeMultiJson('goals.js', [
    'categories' => $clientCategories,
    'goals' => $clientGoals,
    'strings' => $clientStrings,
    'unitGroups' => $clientUnitGroups,
    'memberGroups' => $clientMemberGroups,
    'tracks' => $clientTracks,
]);
Cache::writePhp('goals.php', [
    'cacheTags' => $serverTags,
]);
