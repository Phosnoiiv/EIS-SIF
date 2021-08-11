<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)).'/core/init.php';
include_once ROOT_SIF_CACHE.'/gacha.php';

$sql = "SELECT * FROM gacha_v107";
$col = [['i','cost']];
$sGachas = DB::mySelect($sql, $col, 'code');

$sql = "SELECT * FROM gacha_box_v107";
$col = [['i','box'],['i','select']];
$sBoxes = DB::mySelect($sql, $col, 'code');

$sql = "SELECT * FROM gacha_lineup_group WHERE cache=0";
$rGroups = DB::mySelect($sql, [], 'id');
foreach ($rGroups as $groupID => $rGroup) {
    $sql = "SELECT * FROM gacha_lineup_list WHERE `group`=$groupID";
    $sCardIDs = DB::mySelect($sql, [['i','card']], '', ['s'=>true]);
    $rotatedCacheGachaGroups[$groupID] = json_encode($sCardIDs);
    DB::my_query("UPDATE gacha_lineup_group SET cache=1 WHERE id=$groupID");
    echo "Gacha Lineup Group $groupID cached<br>";
}
ksort($rotatedCacheGachaGroups);

$sql = "SELECT * FROM gacha_lineup_si_unique";
$rUniqueSIs = DB::mySelect($sql, [['i','si']], 'var', ['k'=>'card','s'=>true]);

$sql = "SELECT g.code,gl.* FROM gacha_v107 g INNER JOIN gacha_lineup gl ON g.lineup=gl.id WHERE g.cache=0";
$col = [['i','sel'],['i','ltd'],['i','ur'],['i','ssr'],['i','sr'],['i','r'],['i','n'],['i','si_unique'],['i','si_random']];
$rGachas = DB::mySelect($sql, $col, 'code');
$tRarityNums = [SIF::RARITY_SELECT, SIF::RARITY_LIMIT, 4, 5, 3, 2, 1];
foreach ($rGachas as $code => $rGacha) {
    $cGacha = [];
    for ($i=0; $i<=6; $i++) {
        if (empty($rGacha[$i])) continue;
        $tCardIDs = json_decode($rotatedCacheGachaGroups[$rGacha[$i]]);
        $cGacha['lineup'][$tRarityNums[$i]] = $tCardIDs;
        if (!empty($rGacha[7])) {
            foreach ($tCardIDs as $cardID) {
                if (empty($rUniqueSIs[$rGacha[7]][$cardID])) continue;
                $cGacha['uniqueSI'][$cardID] = $rUniqueSIs[$rGacha[7]][$cardID];
            }
        }
        if (!empty($rGacha[8])) {
            $cGacha['randomSI'] = $rGacha[8];
        }
    }
    Cache::writeJson('gacha/'.$code.'.json', $cGacha);
    DB::my_query("UPDATE gacha_v107 SET cache=1 WHERE code=$code");
    echo "Gacha $code cached<br>";
}

Cache::writePhp('gacha.php', [
    'cacheGachas' => $sGachas,
    'cacheBoxes' => $sBoxes,
    'rotatedCacheGachaGroups' => $rotatedCacheGachaGroups,
]);
