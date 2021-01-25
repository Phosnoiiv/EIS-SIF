<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

http_response_code(404);
$title = '未找到页面';
$trackEnabled = false;
require ROOT_SIF_WEB . '/common-4fcb29e1/head1.php';
require ROOT_SIF_WEB . '/common-4fcb29e1/head2.php';
require ROOT_SIF_WEB . '/common-4fcb29e1/foot.php';
