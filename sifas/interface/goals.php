<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
require_once dirname(__DIR__) . '/core/init.php';

$server = SIF\Basic::checkArg($_GET['s'] ?? false, range(1, 3));

$file = ROOT_SIFAS_CACHE . '/goals/' . $server . '.json';
header('Content-type: application/json');
readfile($file);
