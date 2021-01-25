<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$clientCarnivals = [null];
$sql = 'SELECT * FROM event_carnival';
$dbCarnivals = DB::my_query($sql);
while ($dbCarnival = $dbCarnivals->fetch_assoc()) {
    $id = $dbCarnival['carnival_id'];
    $clientCarnivals[$id] = [
        intval($dbCarnival['track_group_id']),
        intval($dbCarnival['category']),
        intval($dbCarnival['has_master']),
        $dbCarnival['jp_name'] ?? '',
        $dbCarnival['en_name'] ?? '',
        $dbCarnival['cn_name'] ?? '',
        is_numeric($dbCarnival['jp_display_name']) ? intval($dbCarnival['jp_display_name']) : $dbCarnival['jp_display_name'] ?? 0,
        is_numeric($dbCarnival['en_display_name']) ? intval($dbCarnival['en_display_name']) : $dbCarnival['en_display_name'] ?? 0,
        is_numeric($dbCarnival['cn_display_name']) ? intval($dbCarnival['cn_display_name']) : $dbCarnival['cn_display_name'] ?? 0,
        SIF::toTimestamp($dbCarnival['jp_open'], 1),
        SIF::toTimestamp($dbCarnival['en_open'], 2),
        SIF::toTimestamp($dbCarnival['cn_open'], 3),
        SIF::toTimestamp($dbCarnival['jp_close'], 1),
        SIF::toTimestamp($dbCarnival['en_close'], 2),
        SIF::toTimestamp($dbCarnival['cn_close'], 3),
    ];
}

$clientGroups = [null];
$sql = 'SELECT * FROM event_carnival_live';
$dbGroups = DB::my_query($sql);
while ($dbGroup = $dbGroups->fetch_assoc()) {
    $id = $dbGroup['track_group_id'];
    $clientGroups[$id] = $clientGroups[$id] ?? [];
    $clientGroups[$id][] = intval($dbGroup['track_id']);
}

$clientTracks = [];
$sql = 'SELECT live_track.*,
        level_ex, note_ex, release_ex, level_m, swing_m, note_m, release_m
    FROM live_track
    JOIN (SELECT track_id, map_level AS level_ex, combo_s AS note_ex, jp_release AS release_ex FROM live_map WHERE difficulty=4 AND map_type=0) AS map_ex USING (track_id)
    LEFT JOIN (SELECT track_id, map_level AS level_m, map_type AS swing_m, combo_s AS note_m, jp_release AS release_m FROM live_map WHERE difficulty=6 AND map_type<2) AS map_m USING (track_id)
    WHERE display_rc=1';
$dbTracks = DB::my_query($sql);
while ($dbTrack = $dbTracks->fetch_assoc()) {
    $id = $dbTrack['track_id'];
    $track = $clientTracks[$id] = [
        intval($dbTrack['attribute']),
        $dbTrack['jp_name'],
        $dbTrack['en_name'],
        $dbTrack['cn_name'],
        $dbTrack['moe_name'],
        [],
        intval($dbTrack['category']),
        intval($dbTrack['level_ex']),
        intval($dbTrack['note_ex']),
        empty($dbTrack['release_ex']) ? 0 : strtotime($dbTrack['release_ex'] . ' 0:00+0000') / 86400 - 15000,
        intval($dbTrack['level_m']),
        intval($dbTrack['swing_m']),
        intval($dbTrack['note_m']),
        empty($dbTrack['release_m']) ? 0 : strtotime($dbTrack['release_m'] . ' 0:00+0000') / 86400 - 15000,
        $dbTrack['sifas_emblem'] ?? '',
    ];
}

Cache::writeMultiJson('event.rc.js', [
    'carnivals' => $clientCarnivals,
    'playlists' => $clientGroups,
    'tracks' => $clientTracks,
]);
