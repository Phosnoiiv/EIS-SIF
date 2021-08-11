<?php
namespace EIS\Lab\SIF;
require_once dirname(__DIR__).'/core/init.php';

$code = intval($_GET['c']??0);
include ROOT_SIF_CACHE.'/gacha.php';

if (isset($cacheGachas[$code])) {
    $return['gacha'] = $cacheGachas[$code];
}
if (isset($cacheBoxes[$code])) {
    $return['box'] = $cacheBoxes[$code];
}
$tFile = ROOT_SIF_CACHE.'/gacha/'.$code.'.json';
if (file_exists($tFile)) {
    $return = array_merge($return, json_decode(file_get_contents($tFile), true));
}

if (empty($return)) {
    Basic::exit('Not found', 400);
}
header('Content-type: application/json');
echo Util::toJSON($return);
