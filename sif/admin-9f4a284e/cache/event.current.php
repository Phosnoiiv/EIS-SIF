<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$sql = 'SELECT * FROM event WHERE jp_end>NOW() OR en_end>NOW() OR cn_end>NOW()';
$columns = [['i','type'],['i','category'],['s','jp_topic',''],['s','en_topic',''],['s','cn_topic',''],['t','jp_open',1],['t','jp_end',1],['t','en_open',2],['t','en_end',2],['t','cn_open',3],['t','cn_end',3]];
$clientEvents = DB::mySelect($sql, $columns, 'id');

Cache::writeMultiJson('event-current.js', [
    'currentEvents' => $clientEvents,
]);
