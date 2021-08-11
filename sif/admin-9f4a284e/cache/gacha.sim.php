<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)).'/core/init.php';

require_once './gacha.php';

$sql = "SELECT * FROM member_v107 WHERE sif_gacha=1";
$col = [['s','jp_name'],['s','en_name'],['s','zhs_name'],['i','project'],['i','no']];
$cMembers = DB::mySelect($sql, $col, 'sif_id');

$sql = "SELECT * FROM box_cost";
$col = [['i','item_type'],['i','item_key'],['i','amount'],['i','count']];
$cCosts = DB::mySelect($sql, $col, 'cost_group_id', ['m'=>true,'z'=>true]);

$sql = "SELECT * FROM item_general WHERE (item_type,item_key) IN (SELECT DISTINCT item_type,item_key FROM box_cost)";
$col = [
    ['s','jp_name',''],['s','en_name',''],['s','cn_name',''],
    ['s','jp_image1',''],['s','jp_image2',''],['s','en_image1',''],['s','en_image2',''],['s','cn_image1',''],['s','cn_image2',''],
    ['s','jp_desc',''],['s','en_desc',''],['s','cn_desc',''],['s','intro',''],
];
$cItems = DB::mySelect($sql, $col, 'item_type', ['k'=>'item_key']);

$sql = "SELECT * FROM gacha_sim_item";
$col = [['i','type'],['i','key'],['i','init_amount']];
$cInitItems = DB::mySelect($sql, $col, '');

$sql = "SELECT * FROM box_knapsack";
$col = [['i','capacity_ur_selected'],['i','capacity_ur_limited'],['i','capacity_ur'],['i','capacity_ssr'],['i','capacity_sr'],['i','capacity_r']];
$cBoxes = DB::mySelect($sql, $col, 'id', ['z'=>true]);

$sql = "SELECT * FROM gacha_v107";
$col = [['s','name'],['i','type'],['i','project'],['s','banner']];
$cGachas = DB::mySelect($sql, $col, 'code');

Cache::writeMultiJson('gacha-sim.js', [
    'members' => $cMembers,
    'costs' => $cCosts,
    'items' => $cItems,
    'initItems' => $cInitItems,
    'boxes' => $cBoxes,
    'gachas' => $cGachas,
]);
