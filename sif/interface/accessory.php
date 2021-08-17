<?php
namespace EIS\Lab\SIF;
require_once dirname(__DIR__).'/core/init.php';

$accessoryID = intval($_GET['a']??0);
include ROOT_SIF_CACHE.'/accessories.php';

if (!isset($cacheAccessoryLevels[$accessoryID])) {
    Basic::exit('Not found', 400);
}

header('Content-type: application/json');
echo $cacheAccessoryLevels[$accessoryID];
