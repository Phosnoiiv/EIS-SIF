<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$serverReminders = [];
$sql = 'SELECT * FROM reminder WHERE time_till>NOW()';
$dbReminders = DB::my_query($sql);
while ($dbReminder = $dbReminders->fetch_assoc()) {
    $serverReminders[] = [
        intval($dbReminder['type']),
        $server = intval($dbReminder['server']),
        $dbReminder['display_name'],
        SIF::toTimestamp($dbReminder['time_till'], ($server - 1) % 3 + 1),
        SIF::toTimestamp($dbReminder['time_show'], ($server - 1) % 3 + 1),
    ];
}
usort($serverReminders, function($r1, $r2) {
    return $r1[3] - $r2[3];
});

Cache::writePhp('reminders.php', [
    'cacheReminders' => $serverReminders,
]);
