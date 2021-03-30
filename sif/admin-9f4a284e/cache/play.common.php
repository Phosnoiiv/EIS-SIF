<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$configIDs = DB::ltSelect('eis.s3db', 'SELECT DISTINCT config FROM p_common', [['i','config']], '', ['s'=>true]);
foreach ($configIDs as $configID) {
    $items = [];
    $ints = DB::mySelect("SELECT * FROM play_config_int WHERE config=$configID", [['i','value']], 'id', ['z'=>true,'s'=>true]);
    $strings = DB::mySelect("SELECT * FROM play_config_string WHERE config=$configID", [['s','value']], 'id', ['z'=>true,'s'=>true]);
    $jsons = DB::mySelect("SELECT * FROM play_config_json WHERE config=$configID", [['s','json']], 'id', ['z'=>true,'s'=>true]);
    array_walk($jsons, function(&$value, $key) {
        $value = json_decode($value);
    });
    $columns = [['i','type'],['i','key'],['i','amount'],['i','game']];
    $sets = DB::mySelect("SELECT * FROM play_config_item WHERE config=$configID", $columns, 'id', ['z'=>true,'m'=>true]);
    $sql = "SELECT item_type,item_key,jp_name,en_name,cn_name,IFNULL(cn_image1,IFNULL(jp_image1,en_image1)) AS image1,IFNULL(cn_image2,IFNULL(jp_image2,en_image2)) AS image2,jp_desc,en_desc,cn_desc,play_desc FROM sif.item_general
        WHERE (item_type,item_key) IN (SELECT DISTINCT `type`,`key` FROM sif.play_config_item WHERE config=$configID AND game=1)
        UNION SELECT item_type,item_key,jp_name,en_name,cn_name,jp_image1,jp_image2,jp_desc,en_desc,cn_desc,play_desc FROM sifas.item_general
        WHERE (item_type,item_key) IN (SELECT DISTINCT `type`,`key` FROM sif.play_config_item WHERE config=$configID AND game=2)
    ";
    $columns = [['i','item_type'],['i','item_key'],['s','jp_name'],['s','en_name'],['s','cn_name'],['s','image1',''],['s','image2',''],['s','jp_desc'],['s','en_desc'],['s','cn_desc'],['s','play_desc']];
    $dbItems = DB::mySelect($sql, $columns, '');
    foreach ($dbItems as $dbItem) {
        $items[$dbItem[0]][$dbItem[1]] = [$dbItem[4]??$dbItem[2]??$dbItem[3], $dbItem[5], $dbItem[6], $dbItem[10]??$dbItem[9]??$dbItem[7]??$dbItem[8]??''];
    }
    $serverStrings = DB::mySelect("SELECT * FROM play_config_server WHERE config=$configID", [['s','value']], 'id', ['s'=>true]);
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
