<?php
namespace EIS\Lab\SIFAS;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

http_response_code(404);
$title = '未找到页面';
$trackEnabled = false;
require ROOT_SIFAS_WEB . '/common-3d95886d/head1.php';
require ROOT_SIFAS_WEB . '/common-3d95886d/head2.php';
require ROOT_SIF_WEB . '/common-4fcb29e1/foot.php';
