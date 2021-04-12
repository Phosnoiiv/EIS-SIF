<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$sql = 'SELECT * FROM arena_m ORDER BY arena_id DESC LIMIT 1';
$columns = [['i','arena_id'],['s','name'],['t','start_date',1],['t','end_date',1]];
$clientArena = DB::ltSelect('jp/arena.db_', $sql, $columns, '')[0];
$arenaID = $clientArena[0];

$sql = "SELECT * FROM event_arena_cheer_unit WHERE arena=$arenaID ORDER BY type DESC, amount2 DESC";
$columns = [['i','unit'],['i','type'],['i','amount1'],['i','amount2']];
$clientArena[4] = $cheers = DB::mySelect($sql, $columns, '');

$sql = 'SELECT * FROM unit WHERE unit_id IN (' . implode(',', array_column($cheers, 0)) . ')';
$columns = [['i','unit_number'],['i','unit_member'],['i','unit_rarity'],['i','attribute'],['s','jp_name',''],['i','idolized']];
$clientUnits = DB::mySelect($sql, $columns, 'unit_id');

$sql = 'SELECT * FROM member WHERE member_id IN (' . implode(',', array_unique(array_column($clientUnits, 1))) . ')';
$columns = [['s','jp_name']];
$clientMembers = DB::mySelect($sql, $columns, 'member_id');

$sql = "SELECT * FROM arena_stage_m WHERE arena_id=$arenaID";
$columns = [['i','capital_value']];
$stages = DB::ltSelect('jp/arena.db_', $sql, $columns, 'stage_id');
foreach ($stages as $stageID => $stage) {
    if (!isset($lastLPRange) || $lastLPRange[0]!=$stage[0]) {
        if (isset($lastLPRange)) $clientLPs[] = $lastLPRange;
        $lastLPRange = [$stage[0], $stageID, 0];
    }
    $lastLPRange[2] = $stageID;
}
$clientLPs[] = $lastLPRange;

$sql = 'SELECT * FROM arena_live_m WHERE live_difficulty_id>'.Util::readConfig('cache','arena_live_prev');
$columns = [['i','live_setting_id'],['i','random_flag']];
$arenaLives = DB::ltSelect('jp/arena.db_', $sql, $columns, '');

$listLives = implode(',', array_column($arenaLives, 0));
$sql = "SELECT * FROM live_track WHERE track_id IN (SELECT DISTINCT track_id FROM live_map WHERE map_id IN ($listLives))";
$columns = [['i','attribute'],['s','jp_name']];
$tracks = DB::mySelect($sql, $columns, 'track_id');

$sql = "SELECT * FROM live_map WHERE map_id IN ($listLives)";
$columns = [['i','track_id'],['i','difficulty'],['i','map_level'],['i','map_type'],['i','combo_s']];
$maps = DB::mySelect($sql, $columns, 'map_id');
foreach ($arenaLives as $arenaLive) {
    $mapID = $arenaLive[0]; $map = $maps[$mapID];
    $trackID = $map[0]; $difficulty = $map[1]; $level = $map[2]; $type = $map[3]; $notes = $map[4];
    if (!isset($lastTrackID) || $lastTrackID!=$trackID) {
        if (isset($lastTrack)) $clientLevels[] = $lastTrack;
        $lastTrack = array_merge($tracks[$trackID], ['','','',[],0,0,0,0,0]);
    }
    $lastTrackID = $trackID;
    $lastTrack[5][] = $difficulty;
    switch ($difficulty) {
        case 4:
            $lastTrack[6] = $level; $lastTrack[7] = $notes;
            break;
        case 6:
            $lastTrack[8] = $level; $lastTrack[9] = $type; $lastTrack[10] = $notes;
            break;
    }
}
$clientLevels[] = $lastTrack;

Cache::writeMultiJson('latest-arena.js', [
    'arena' => $clientArena,
    'units' => $clientUnits,
    'members' => $clientMembers,
    'lps' => $clientLPs,
    'levels' => $clientLevels,
]);
