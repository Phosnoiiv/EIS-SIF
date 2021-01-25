<?php
namespace EIS\Lab\SIF;
require_once dirname(__DIR__) . '/core/init.php';

$limitType = intval($_GET['l'] ?? 0);

$userIdentity = $_SERVER['REMOTE_ADDR'];
try {
    $limit = new Limit($limitType, $userIdentity);
} catch (\Exception $e) {
    Basic::exit($e->getMessage(), 403);
}

header('Content-type: application/json');
echo HTML::json('', [
    'max' => $limit->max,
    'current' => $limit->current,
    'nextRecover' => $limit->nextRecoverTime,
]);
