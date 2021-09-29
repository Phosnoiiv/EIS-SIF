<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)).'/core/init.php';

// Buff icons by effect type
$sql = "SELECT * FROM icon WHERE category='buff'";
$rBuffIcons = DB::mySelect($sql, [['s','path']], 'key', ['s'=>true]);
$sql = "SELECT * FROM m_skill_effect_type_setting";
$cBuffIcons = DB::ltSelect(DB_GAME_JP_MASTER, $sql, [['i','buff_icon_id']], 'effect_type', ['s'=>true,'z'=>true]);
array_walk($cBuffIcons, function(&$v) use($rBuffIcons) {
    if (isset($rBuffIcons[$v])) $v=$rBuffIcons[$v];
    if (is_numeric($v)) $v=intval($v);
});
Cache::writeJson('common/buffs.json', $cBuffIcons, true);
