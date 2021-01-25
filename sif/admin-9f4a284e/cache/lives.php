<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$clientTracks = $clientTrackAq = [];
$sql = 'SELECT * FROM live_track';
$dbTracks = DB::my_query($sql);
while ($dbTrack = $dbTracks->fetch_assoc()) {
    $id = $dbTrack['track_id'];
    $category = $dbTrack['category'];
    $track = $clientTracks[$id] = [
        intval($dbTrack['attribute']),
        $dbTrack['jp_name'],
        $dbTrack['en_name'],
        $dbTrack['cn_name'],
        $dbTrack['moe_name'],
    ];
    if ($category == 2) {
        $clientTrackAq[$id] = $track;
    }
}

Cache::writeMultiJson('live.track.aq.js', [
    'tracks' => $clientTrackAq,
]);
