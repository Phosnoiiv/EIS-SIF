<?php
namespace EIS\Lab\SIF;
$pageID = 12;
$isMaintenancePage = true;
require_once __DIR__ . '/core/init.php';

$code = $_GET['c'];
if (empty($schedule = Basic::getMaintenance($code))) {
    Basic::exit('Invalid argument', 403);
}
if (time() > $schedule[1] && $schedule[2]) {
    header('Location: ' . Basic::getPageURL($schedule[5]), false, 302);
    exit();
}

http_response_code(503);
$simpleIcon = '<span class="fa-layers"><i class="fas fa-play" data-fa-transform="rotate-270"></i><i class="fas fa-tools fa-inverse" data-fa-transform="shrink-8 down-2"></i></span>';
$simpleTitle = $schedule[3];
$simpleTime = $schedule[6] ? '维护时间：' . date('m/d H:i', $schedule[0]) . '～' . date('m/d H:i', $schedule[1]) : '';
$simpleText = HTML::paragraphs($schedule[4]);
if (time() > $schedule[1]) {
    $simpleNotices[] = '由于未能按预定时间完成维护计划，本次维护将延迟结束，敬请谅解。';
}
require ROOT_SIF_WEB . '/common-d42c0d8a/simple.php';
