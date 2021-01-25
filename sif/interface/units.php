<?php
namespace EIS\Lab\SIF;
require_once dirname(__DIR__) . '/core/init.php';

$ids = explode('a', $_REQUEST['u'] ?? '');
include ROOT_SIF_CACHE . '/boxes-units.php';

$units = $skills = [];
foreach ($ids as $id) {
    $id = intval($id);
    if (!isset($cacheBoxUnits[$id]))
        continue;
    $unit = $units[$id] = $cacheBoxUnits[$id];
    if (!empty($skillID = $unit[6])) {
        $skills[$skillID] = $cacheBoxSkills[$skillID];
    }
}
header('Content-type: application/json');
echo HTML::json('', [
    'units' => $units,
    'skills' => $skills,
]);
