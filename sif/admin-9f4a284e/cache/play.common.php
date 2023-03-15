<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$sql = "SELECT DISTINCT config,inherit FROM p_common WHERE time_close IS NULL OR time_close>=datetime('now','localtime')";
$col = [['i','config'],['i','inherit',0]];
$configPairs = DB::ltSelect(DB_EIS_MAIN, $sql, $col, '');
foreach ($configPairs as $configPair) {
    $configID = $configPair[0]; $inheritID = $configPair[1];
    $fltConfig = "config IN ($configID,$inheritID)";
    $items = [];
    $ints = DB::mySelect("SELECT * FROM play_config_int WHERE $fltConfig ORDER BY id ASC", [['i','value']], 'id', ['z'=>true,'s'=>true]);
    $strings = DB::mySelect("SELECT * FROM play_config_string WHERE $fltConfig ORDER BY id ASC", [['s','value']], 'id', ['z'=>true,'s'=>true]);
    $jsons = DB::mySelect("SELECT * FROM play_config_json WHERE $fltConfig ORDER BY id ASC", [['s','json']], 'id', ['z'=>true,'s'=>true]);
    array_walk($jsons, function(&$value, $key) {
        if (empty($value)) return;
        $value = json_decode($value);
    });
    $columns = [['i','type'],['i','key'],['i','amount'],['i','game']];
    $sets = DB::mySelect("SELECT * FROM play_config_item WHERE $fltConfig ORDER BY id ASC", $columns, 'id', ['z'=>true,'m'=>true]);
    $sql = "SELECT item_type,item_key,jp_name,en_name,cn_name,IFNULL(cn_image1,IFNULL(jp_image1,en_image1)) AS image1,IFNULL(cn_image2,IFNULL(jp_image2,en_image2)) AS image2,jp_desc,en_desc,cn_desc,play_desc FROM sif.item_general
        WHERE (item_type,item_key) IN (SELECT DISTINCT `type`,`key` FROM sif.play_config_item WHERE $fltConfig AND game=1)
        UNION SELECT `type`,`key`,jp_name,en_name,zhs_name,IFNULL(zhs_image1,IFNULL(jp_image1,en_image1)) AS image1,IFNULL(zhs_image2,IFNULL(jp_image2,en_image2)) AS image2,jp_desc,en_desc,zhs_desc,NULL AS play_desc FROM sif.item_v106
        WHERE (`type`,`key`) IN (SELECT DISTINCT `type`,`key` FROM sif.play_config_item WHERE $fltConfig AND game=1) AND (`type`,`key`) NOT IN (SELECT item_type,item_key FROM sif.item_general)
        UNION SELECT item_type,item_key,jp_name,en_name,cn_name,jp_image1,jp_image2,jp_desc,en_desc,cn_desc,play_desc FROM sifas.item_general
        WHERE (item_type,item_key) IN (SELECT DISTINCT `type`,`key` FROM sif.play_config_item WHERE $fltConfig AND game=2)
    ";
    $columns = [['i','item_type'],['i','item_key'],['s','jp_name'],['s','en_name'],['s','cn_name'],['s','image1',''],['s','image2',''],['s','jp_desc'],['s','en_desc'],['s','cn_desc'],['s','play_desc']];
    $dbItems = DB::mySelect($sql, $columns, '');
    foreach ($dbItems as $dbItem) {
        $items[$dbItem[0]][$dbItem[1]] = [$dbItem[4]??$dbItem[2]??$dbItem[3], $dbItem[5], $dbItem[6], $dbItem[10]??$dbItem[9]??$dbItem[7]??$dbItem[8]??''];
    }
    $serverStrings = DB::mySelect("SELECT * FROM play_config_server WHERE $fltConfig ORDER BY id ASC", [['s','value']], 'id', ['s'=>true]);
    Cache::writeMultiJson('play/config.'.$configID.'.js', [
        'CI' => $ints,
        'CS' => $strings,
        'CJ' => $jsons,
        'CT' => $sets,
        'items' => $items,
    ]);
    Cache::writePhp('play/config.'.$configID.'.php', [
        'CPC' => $serverStrings,
    ]);
}
