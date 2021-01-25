<?php
namespace EIS\Lab\SIF;
require_once dirname(__DIR__) . '/core/init.php';

$server = Basic::checkArg($_GET['s'] ?? false, range(1, 3));
$box = intval($_GET['b'] ?? 0);
$run = intval($_GET['r'] ?? 0);
$module = $_GET['m'] ?? '';
include ROOT_SIF_CACHE . '/box.stepups.php';
include ROOT_SIF_CACHE . '/boxes-sim.php';

$return = [];
if ($module == 's') {
    $file = ROOT_SIF_CACHE . '/boxes/' . $box . '-' . $server . '-' . $run . '.json';
}
if (isset($cacheStepups[$box][$server][$run])) {
    $stepup = $cacheStepups[$box][$server][$run];
    $setting = $cacheStepupSettings[$stepup[2]];
    $return['stepup'] = [
        $stepup[0],
        $stepup[1],
        $stepup[3],
        $stepup[4],
        $setting,
    ];
}
if (empty($return) && !file_exists($file ?? '')) {
    Basic::exit('Not found', 400);
}
header('Content-type: application/json');
if (isset($file)) {
    readfile($file);
    exit;
}
echo json_encode($return, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
