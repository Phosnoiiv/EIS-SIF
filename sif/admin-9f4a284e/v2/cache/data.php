<?php
namespace EIS\Lab\SIF;
require_once __DIR__.'/../../../core/init.php';

$packId = intval($_GET['p']??0);
$pack = V2::$dataPacks[$packId] ?? Basic::exit('Invalid pack ID', 403);

$bundleId = intval($_GET['b']??0);
$sql = "SELECT * FROM sv2_data WHERE id=$bundleId";
$col = [['s','name'],['s','patch'],['s','path'],['t','time_release',3]];
$bundle = DB::ltSelect(DB_EIS_MAIN, $sql, $col, '')[0] ?? Basic::exit('Invalid bundle ID', 403);
$bundleCachePath = 'v2/data/'.$bundle[2];
if (!is_dir(ROOT_SIF_CACHE.'/'.$bundleCachePath)) {
    mkdir(ROOT_SIF_CACHE.'/'.$bundleCachePath, recursive:true);
}

V2Mid::setBundleRelease($bundle[3]);
require ROOT_SIF_ADMIN.'/v2/cache/data/'.$pack['f'].'.php';
foreach ($v2DefData as $unitId => $def) {
    $unit = $pack['l'][$unitId];
    $data = $def['d']();
    Cache::write($bundleCachePath.'/'.$unit['f'].'.json', Util::toJSON($data));
}
