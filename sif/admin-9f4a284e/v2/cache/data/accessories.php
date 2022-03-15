<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV')) exit;

$v2DefData = [];
$v2DefData[1] = [
    'd' => function() {
        $timeBundle = V2Mid::getBundleReleaseStr();
        $sql = "SELECT * FROM d_accessory WHERE eis_on<='$timeBundle'";
        $col = [
            ['s','ja_name'],['s','en_name'],['s','zhs_name',''],
            ['s','image'],['i','rarity'],['i','card',0],['i','effect_type'],
            ['t','jp_on',1],['t','jp_off',1],['t','cn_on',3],['t','cn_off',3],
        ];
        return DB::mySelect($sql, $col, 'id', flags:DB::FLAG_MY_FILL_NULL);
    },
];
