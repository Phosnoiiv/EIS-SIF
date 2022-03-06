<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$sql = 'SELECT * FROM event_m ORDER BY event_id DESC LIMIT 1';
$dbEvent = DB::lt_query('jp/event_common.db_', $sql)->fetchArray(SQLITE3_ASSOC);
$eventID = $dbEvent['event_id'];
$clientEvent = [
    $eventType = $dbEvent['event_category_id'],
    $dbEvent['name'],
    SIF::toTimestamp($dbEvent['end_date'], 1),
    $dbEvent['member_category'],
    [],
];
$sql = "SELECT unit_id FROM unit WHERE (event1=$eventID OR event2=$eventID) AND unit_type=2";
$clientEvent[5] = DB::mySelect($sql, [['i','unit_id']], '', ['s'=>true]);

$previousEventID = $eventID - 2;
$sql = "SELECT unit_id FROM unit WHERE (event1=$previousEventID OR event2=$previousEventID) AND unit_type=2";
$clientPreviousEvent[0] = DB::mySelect($sql, [['i','unit_id']], '', ['s'=>true]);

$listUnits = [];
$sql = 'SELECT * FROM event_yell_unit WHERE event=' . $eventID . ' ORDER BY amount2 DESC, amount1 DESC, unit ASC';
$dbCheers = DB::my_query($sql);
while ($dbCheer = $dbCheers->fetch_assoc()) {
    $id = intval($dbCheer['unit']);
    $clientEvent[4][] = [
        $id,
        intval($dbCheer['amount1']),
        intval($dbCheer['amount2']),
    ];
    $listUnits[] = $id;
}

$clientUnits = $listMembers = $listSeries = [];
$sql = 'SELECT * FROM unit WHERE unit_id IN (' . implode(',', $listUnits) . ')';
$dbUnits = DB::my_query($sql);
while ($dbUnit = $dbUnits->fetch_assoc()) {
    $id = $dbUnit['unit_id'];
    $clientUnits[$id] = [
        intval($dbUnit['unit_number']),
        $member = intval($dbUnit['unit_member']),
        intval($dbUnit['unit_rarity']),
        intval($dbUnit['attribute']),
        $dbUnit['jp_name'] ?? '',
        $series = intval($dbUnit['unit_series']),
        intval($dbUnit['unit_type']),
        intval($dbUnit['idolized']),
    ];
    if (!in_array($member, $listMembers)) {
        $listMembers[] = $member;
    }
    if (!in_array($series, $listSeries)) {
        $listSeries[] = $series;
    }
}

$clientMembers = [];
$sql = 'SELECT * FROM member WHERE member_id IN (' . implode(',', $listMembers) . ')';
$dbMembers = DB::my_query($sql);
while ($dbMember = $dbMembers->fetch_assoc()) {
    $id = $dbMember['member_id'];
    $clientMembers[$id] = $dbMember['jp_name'] ?? '';
}

$clientSeries = [];
$sql = 'SELECT * FROM unit_series WHERE series_id IN (' . implode(',', $listSeries) . ')';
$dbSeries = DB::my_query($sql);
while ($dbSeries1 = $dbSeries->fetch_assoc()) {
    $id = $dbSeries1['series_id'];
    $clientSeries[$id] = $dbSeries1['jp_name'] ?? '';
}

$clientLives = $listLives = [];
switch ($eventType) {
    case 2:
        $sql = 'SELECT * FROM event_battle_live_m WHERE live_difficulty_id>' . $config['event_live_prev'][2];
        $dbLives = DB::lt_query('jp/battle.db_', $sql);
        while ($dbLive = $dbLives->fetchArray(SQLITE3_ASSOC)) {
            $id = $dbLive['live_setting_id'];
            $listLives[$id] = [
                $dbLive['random_flag'],
                $dbLive['stage_level'],
            ];
        }
        break;
    case 3:
        $sql = 'SELECT * FROM event_festival_live_m WHERE use_flag=1';
        $columns = [['i','random_flag']];
        $listLives = DB::ltSelect('jp/festival.db_', $sql, $columns, 'live_setting_id');
        break;
    case 4:
        if (empty($config['event_db_prev'][4])) {
        $sql = 'SELECT * FROM event_challenge_live_m WHERE live_difficulty_id>' . $config['event_live_prev'][4];
        } else {
            DB::ltAttach('jp/challenge.db_', 'jp/' . $config['event_db_prev'][4] . '/challenge.db_', 'p');
            $sql = 'SELECT * FROM event_challenge_live_m WHERE live_difficulty_id NOT IN (SELECT live_difficulty_id FROM p.event_challenge_live_m)';
        }
        $dbLives = DB::lt_query('jp/challenge.db_', $sql);
        while ($dbLive = $dbLives->fetchArray(SQLITE3_ASSOC)) {
            $id = $dbLive['live_setting_id'];
            $listLives[$id] = [
                $dbLive['random_flag'],
                $dbLive['stage_level'],
            ];
        }
        break;
    case 6:
        $sql = 'SELECT * FROM event_team_duty_live_m WHERE live_difficulty_id>' . $config['event_live_prev'][6];
        $dbLives = DB::lt_query('jp/team_duty.db_', $sql);
        while ($dbLive = $dbLives->fetchArray(SQLITE3_ASSOC)) {
            $id = $dbLive['live_setting_id'];
            $listLives[$id] = [
                $dbLive['random_flag'],
            ];
        }
        break;
}
if (!empty($listLives)) {
    $implodeLives = implode(',', array_keys($listLives));
    $sql = "SELECT live_track.*, ex.*, m.*, lane5_flag FROM live_track
        LEFT JOIN (SELECT track_id AS ex_track, map_id AS ex_map, map_level AS ex_level, combo_s AS ex_notes, weight AS ex_weight FROM live_map WHERE difficulty=4 AND map_type=0) AS ex ON track_id=ex_track
        LEFT JOIN (SELECT track_id AS m_track, map_level AS m_level, map_type AS m_type, combo_s AS m_notes, weight AS m_weight FROM live_map WHERE difficulty=6 AND map_type<2) AS m ON track_id=m_track
        LEFT JOIN (SELECT track_id AS lane5_track, MAX(map_type=3) AS lane5_flag FROM live_map WHERE map_id IN ($implodeLives) GROUP BY track_id) AS lane5 ON track_id=lane5_track
    WHERE track_id IN (SELECT DISTINCT track_id FROM live_map WHERE map_id IN ($implodeLives)) ORDER BY attribute ASC, live_track.track_id ASC";
    $dbTracks = DB::my_query($sql);
    while ($dbTrack = $dbTracks->fetch_assoc()) {
        $clientLives[] = [
            intval($dbTrack['attribute']),
            $dbTrack['jp_name'],
            '',
            '',
            '',
            intval($dbTrack['ex_level']),
            intval($dbTrack['ex_notes']),
            floatval($dbTrack['ex_weight']),
            intval($dbTrack['m_level']),
            intval($dbTrack['m_type']),
            intval($dbTrack['m_notes']),
            floatval($dbTrack['m_weight']),
            intval($listLives[$dbTrack['ex_map']][1] ?? 0),
            intval($dbTrack['track_id']),
            intval($dbTrack['lane5_flag']),
        ];
    }
    if ($eventType == 4) {
        $sql = 'SELECT * FROM live_setting_m WHERE live_setting_id IN (' . implode(',', array_keys($listLives)) . ')';
        $dbMaps = DB::lt_query('jp/live.db_', $sql);
        while ($dbMap = $dbMaps->fetchArray(SQLITE3_ASSOC)) {
            $mapID = $dbMap['live_setting_id'];
            $trackID = $dbMap['live_track_id'];
            $clientDifficulties[$trackID][] = [
                $dbMap['difficulty'],
                $listLives[$mapID][0],
                $dbMap['swing_flag'],
                $dbMap['lane_count'],
            ];
        }
    }
}

Cache::writeMultiJson('latest-event.js', [
    'event' => $clientEvent,
    'previousEvent' => $clientPreviousEvent,
    'units' => $clientUnits,
    'members' => $clientMembers,
    'series' => $clientSeries,
    'lives' => $clientLives,
    'difficulties' => $clientDifficulties ?? [],
]);
