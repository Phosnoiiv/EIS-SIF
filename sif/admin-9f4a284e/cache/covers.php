<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$clientCovers = [null];
$sql = 'SELECT * FROM cover';
$dbCovers = DB::my_query($sql);
while ($dbCover = $dbCovers->fetch_assoc()) {
    $id = $dbCover['id'];
    $server = intval($dbCover['server']);
    $timezone = SIF::getServerTimezone($server);
    $clientCovers[$id] = [
        $server,
        $dbCover['display_name'],
        $dbCover['jpg_key'],
        empty($dbCover['time_start']) ? 0 : strtotime($dbCover['time_start'] . $timezone),
        empty($dbCover['time_stop']) ? 0 : strtotime($dbCover['time_stop'] . $timezone),
        intval($dbCover['category']),
        intval($dbCover['type']),
    ];
}

Cache::writeMultiJson('covers.js', [
    'covers' => $clientCovers,
]);
