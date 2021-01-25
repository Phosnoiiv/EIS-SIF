<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
require_once dirname(__DIR__) . '/core/init.php';

$songID = intval($_GET['s'] ?? 0);

if ($songID) {
    $file = ROOT_SIFAS_CACHE . '/lives/' . $songID . '.json';
}
if (!file_exists($file ?? ''))
    SIF\Basic::exit('Argument error', 403);
header('Content-type: application/json');
readfile($file);
