<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$sql = 'SELECT * FROM m_live_drop_content';
$dbDrops = DB::lt_query('jp/masterdata.db', $sql);
while ($dbDrop = $dbDrops->fetchArray(SQLITE3_ASSOC)) {
    $id = $dbDrop['drop_content_group_id'];
    $type = $dbDrop['content_type'];
    $key = in_array($type, [4, 10]) ? 0 : $dbDrop['content_id'];
    $amount = $dbDrop['amount'];
    $drops[$id][] = $amount > 1 ? [$type, $key, $amount] : [$type, $key];
}

$sql = 'SELECT DISTINCT content_type,content_id FROM m_live_drop_content';
$columns = [['i','content_type'],['i','content_id']];
$cacheDropItems = DB::ltSelect('jp/masterdata.db', $sql, $columns, '');

$sql = 'SELECT * FROM m_live_drop_group WHERE drop_count>0';
$columns = [['i','drop_color'],['i','drop_content_group_id']];
$dropGroups = DB::ltSelect('jp/masterdata.db', $sql, $columns, 'group_id', ['m'=>true]);

foreach ($dropGroups as $id => $dropGroup) {
    $contents = [];
    foreach ($dropGroup as $contentGroup) {
        $contents[] = [$contentGroup[0], $drops[$contentGroup[1]]];
    }
    $cacheDropGroups[$id] = json_encode($contents);
}
Cache::writePhp('drops.php', [
    'cacheDropGroups' => $cacheDropGroups,
    'cacheDropItems' => $cacheDropItems,
]);
