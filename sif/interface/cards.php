<?php
namespace EIS\Lab\SIF;
require_once dirname(__DIR__).'/core/init.php';

if (preg_match('/^\d{1,4}(,\d{1,4})*$/', $_REQUEST['c']??'') != 1) {
    Basic::exit('Invalid argument', 403);
}

$sql = "SELECT * FROM card_v107 WHERE id IN (".$_REQUEST['c'].")";
$col = [['i','no'],['i','member'],['i','rarity'],['i','attribute'],['s','jp_name'],['s','en_name'],['s','zhs_name'],['i','series'],['i','skill']];
const COL_CARD_SKILL = 8;
$dCards = DB::ltSelect(DB_EIS_CACHED, $sql, $col, 'id');

header('Content-type: application/json');
echo Util::toJSON([
    'cards' => $dCards,
]);
