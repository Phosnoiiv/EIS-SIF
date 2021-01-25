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

Cache::writeMultiJson('latest-arena.js', [
    'arena' => $clientArena,
    'units' => $clientUnits,
    'members' => $clientMembers,
]);
