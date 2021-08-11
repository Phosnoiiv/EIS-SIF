<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)).'/core/init.php';

$rCachedCardIDs = DB::ltSelect(DB_EIS_CACHED, "SELECT id FROM card_v107", [['i','id']], '', ['s'=>true]);
$rAllCardIDs = DB::mySelect("SELECT id FROM card_v107", [['i','id']], '', ['s'=>true]);
$rDiffCardIDs = array_diff($rAllCardIDs, $rCachedCardIDs);
$rCols = ['id','no','member','rarity','attribute','jp_name','en_name','zhs_name','series','album','skill','jp_release','cn_release'];
if (!empty($rDiffCardIDs)) {
    $sql = "SELECT * FROM card_v107 WHERE id IN (".implode(',',$rDiffCardIDs).")";
    $dDiffCards = DB::my_query($sql);
    while ($tCard = $dDiffCards->fetch_row()) {
        DB::ltInsert(DB_EIS_CACHED, 'card_v107', array_combine($rCols, $tCard));
    }
}
