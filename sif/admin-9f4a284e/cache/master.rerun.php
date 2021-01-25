<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$master_rerun_schedule = [];
$db_master_rerun_schedule = DB::my_query('SELECT
        master_rerun_group_id,
        map_level, map_type, combo_s,
        jp_name, attribute
    FROM live_master_rerun_schedule
    JOIN live_map USING (map_id)
    JOIN live_track USING (track_id)
');
while ($schedule = $db_master_rerun_schedule->fetch_assoc()) {
    $group = intval($schedule['master_rerun_group_id']);
    $master_rerun_schedule[$group][] = [
        'jp_name' => $schedule['jp_name'],
        'attribute' => intval($schedule['attribute']),
        'level' => intval($schedule['map_level']),
        'is_swing' => $schedule['map_type'] & 1,
        'note_total' => intval($schedule['combo_s']),
    ];
}
Cache::writePhp('master.rerun.php', [
    'master_rerun_schedule' => $master_rerun_schedule,
]);
