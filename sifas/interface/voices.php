<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
require_once dirname(__DIR__) . '/core/init.php';

$member = $_GET['m'] ?? 0;
$category = $_GET['c'] ?? 0;

$count = 0;
foreach ([$member, $category] as $arg)
    if ($arg)
        $count++;
if ($count > 1)
    SIF\Basic::exit('Argument error', 403);

if ($member) {
    $file = ROOT_SIFAS_CACHE . '/voices/m' . $member . '.json';
}
if ($category) {
    $file = ROOT_SIFAS_CACHE . '/voices/c' . $category . '.json';
}
if (!file_exists($file))
    SIF\Basic::exit('Argument error', 403);
header('Content-type: application/json');
readfile($file);
