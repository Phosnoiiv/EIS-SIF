<?php
namespace EIS\Lab\SIF;
require_once __DIR__.'/../../core/init.php';

$sql = "SELECT * FROM event_yell ORDER BY id DESC";
$col = [['i','event_type'],['i','event_id'],['s','units'],['i','is_current']];
$dYells = DB::mySelect($sql, $col, '');
foreach ($dYells as $dYell) {
    if ($dYell[0] > 0) {
        $sql = "SELECT * FROM event_m WHERE event_id={$dYell[1]}";
        $col = [['t','start_date',1],['t','end_date',1]];
        $dEvent = DB::ltSelect('jp/event_common.db_', $sql, $col, '')[0];
        $tCategory = 1;
        $tDate = round(($dEvent[0] + $dEvent[1]) / 2);
    } else {
        $sql = "SELECT * FROM arena_m WHERE arena_id={$dYell[1]}";
        $col = [['t','start_date',1],['t','end_date',1]];
        $dArena = DB::ltSelect('jp/arena.db_', $sql, $col, '')[0];
        $tCategory = 2;
        $tDate = round(($dArena[0] + $dArena[1]) / 2);
    }
    $tYell = array_filter([
        'type' => $dYell[0],
        'id' => $dYell[1],
        'date' => date('Y/m', $tDate),
        'units' => array_map('intval', explode(',', $dYell[2])),
        'is_current' => $dYell[3] ? true : null,
    ], fn($x) => $x !== null);
    if (count($tYells[$tCategory] ?? []) >= 2) {
        $tCategory = 0;
    }
    $tYells[$tCategory][] = $tYell;
}
$cYells = [$tYells[1][0], $tYells[2][0], $tYells[1][1], $tYells[2][1], ...$tYells[0]];

Cache::writeJson('event-yell.json', $cYells);
