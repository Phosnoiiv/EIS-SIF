<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$versions = [null];
$db_versions = DB::my_query('SELECT * FROM site_version');
while ($version = $db_versions->fetch_assoc()) {
    $id = intval($version['id']);
    $versions[$id] = [
        $version['version_name'],
        strtotime($version['version_date'] . ' 0:00+0000') / 86400,
        $version['log'] ?? [],
    ];
}
$db_histories = DB::my_query('SELECT * FROM site_history');
while ($history = $db_histories->fetch_assoc()) {
    $id = intval($history['version_id']);
    $versions[$id][2][] = [
        intval($history['history_type']),
        intval($history['history_level']),
        $history['history_content'],
    ];
}
Cache::writeJson('site.history.json', $versions);
