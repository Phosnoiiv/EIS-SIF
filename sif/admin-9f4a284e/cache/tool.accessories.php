<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)).'/core/init.php';

$vers = Util::readConfig('cache', 'accessory_db', isJson:true);
foreach ($vers as $ver) {
    $c = ['n'=>$ver[0], 'u'=>$ver[2]];
    $dbFolder = 'jp/'.$ver[1].'/';

    $sql = "SELECT * FROM accessory_lottery_cost_m";
    $col = [['i','from_value'],['i','to_value'],['i','cost_value']];
    $c['c'] = DB::ltSelect($dbFolder.'unit.db_', $sql, $col, 'status_type', ['m'=>true,'z'=>true]);

    $sql = "SELECT * FROM accessory_lottery_group_m";
    $col = [['i','from_cost'],['i','to_cost']];
    $c['g'] = DB::ltSelect($dbFolder.'unit.db_', $sql, $col, 'accessory_lottery_group_id', ['z'=>true]);

    $sql = "SELECT accessory_lottery_group_id,rarity,SUM(`weight`) s FROM accessory_lottery_list_m LEFT JOIN accessory_m USING (accessory_id) GROUP BY accessory_lottery_group_id,rarity";
    $c['l'] = DB::ltSelect($dbFolder.'unit.db_', $sql, [['i','s']], 'accessory_lottery_group_id', ['z'=>true,'s'=>true,'k'=>'rarity']);

    $cData[] = $c;
}

Cache::writeMultiJson('tool-accessories.js', [
    'data' => $cData,
]);
