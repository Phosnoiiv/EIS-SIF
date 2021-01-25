<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$clientPosters = [];
$sql = 'SELECT * FROM box WHERE poster1 IS NOT NULL ORDER BY time_open DESC, box_server ASC, box_id ASC';
$dbPosters = DB::my_query($sql);
while ($dbPoster = $dbPosters->fetch_assoc()) {
    $server = intval($dbPoster['box_server']);
    for ($i = 1; $i <= 2 && !empty($dbPoster['poster' . $i]); $i++) {
        $clientPosters[] = [
            $server,
            $dbPoster['display_name'],
            $dbPoster['poster' . $i],
            SIF::toTimestamp($dbPoster['time_open'], $server),
            SIF::toTimestamp($dbPoster['time_close'], $server),
            intval($dbPoster['member_category']),
        ];
    }
}

Cache::writeMultiJson('posters.js', [
    'posters' => $clientPosters,
]);
