<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)).'/core/init.php';

$sql = "SELECT * FROM accessory_m";
$col = [['i','smile_max'],['i','pure_max'],['i','cool_max'],['i','trigger_type'],['i','trigger_effect_type']];
$dAccessories = DB::ltSelect('jp/unit.db_', $sql, $col, 'accessory_id');

$sql = "SELECT * FROM accessory_level_m";
$col = [
    ['i','smile_diff'],['i','pure_diff'],['i','cool_diff'],
    ['i','effect_value'],['i','discharge_time'],['i','activation_rate'],['i','unit_skill_combo_pattern_id',0],
    ['i','trigger_value',0],['i','spark_count_limit',0],
];
$dAccessoryLevels = DB::ltSelect('jp/unit.db_', $sql, $col, 'accessory_id', ['k'=>'level']);

$sql = "SELECT * FROM accessory_effect_target_m";
$col = [['i','reference_type'],['i','effect_target']];
$dEffectTargets = DB::ltSelect('jp/unit.db_', $sql, $col, 'accessory_id', ['m'=>true]);

$sql = "SELECT id FROM d_accessory";
$col = [];
$cAccessories = DB::mySelect($sql, $col, 'id', ['z'=>true]);
foreach ($cAccessories as $accessoryID => &$cAccessory) {
    if ($accessoryID==0) continue;
    $dAccessory = $dAccessories[$accessoryID];
    $tLevels = $tAppend = [];
    foreach ($dAccessoryLevels[$accessoryID] as $levelID => $dLevel) {
        $tLevels[$levelID] = [
            $dAccessory[0]-$dLevel[0], $dAccessory[1]-$dLevel[1], $dAccessory[2]-$dLevel[2],
            ...array_slice($dLevel, 3),
        ];
    }
    if (isset($dEffectTargets[$accessoryID])) $tAppend['e'] = $dEffectTargets[$accessoryID];
    if (!empty($dAccessory[3])) $tAppend['t'] = $dAccessory[3];
    if (!empty($dAccessory[4])) $tAppend['te'] = $dAccessory[4];
    $cAccessory = array_merge($cAccessory, [array_filter($tLevels, function($k) {
        return in_array($k, [1,4,8]);
    }, ARRAY_FILTER_USE_KEY)]);
    if (!empty($tAppend)) array_push($cAccessory, $tAppend);
    $sAccessoryLevels[$accessoryID] = Util::toJSON($tLevels);
}

$sql = "SELECT unit_id,unit_member,idolized,unit_seal,us.effect_type,us.trigger_type,us.trigger_value_8
        FROM unit u LEFT JOIN unit_skill us ON u.unit_skill=us.id
        WHERE unit_id IN (SELECT `card` FROM d_accessory) ORDER BY unit_id ASC";
$col = [['i','unit_member'],['i','idolized'],['i','effect_type'],['i','trigger_type'],['i','trigger_value_8'],['i','unit_seal']];
$cCards = DB::mySelect($sql, $col, 'unit_id');

$sql = "SELECT * FROM member_v107 WHERE sif_id IN (SELECT DISTINCT unit_member FROM unit WHERE unit_id IN (SELECT `card` FROM d_accessory))";
$col = [['s','jp_name'],['s','en_name'],['s','zhs_name']];
$cMembers = DB::mySelect($sql, $col, 'sif_id');

$sql = "SELECT * FROM item_v106 WHERE `type`=3006 AND `key` IN (4,6)";
$col = [['s','jp_image2']];
$cSeals = DB::mySelect($sql, $col, 'type', ['k'=>'key']);

Cache::writeMultiJson('accessories.js', [
    'accessoryExtends' => $cAccessories,
    'cards' => $cCards,
    'members' => $cMembers,
    'items' => $cSeals,
]);
Cache::writePhp('accessories.php', [
    'cacheAccessoryLevels' => $sAccessoryLevels,
]);
