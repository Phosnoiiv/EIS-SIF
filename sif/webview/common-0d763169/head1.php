<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV')) exit;
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="referrer" content="strict-origin-when-cross-origin"/>
<meta name="viewport" content="width=device-width,user-scalable=no"/>
<title><?=($title ?? $pages[$pageID]['title']) . (empty($isHome) ? ' - EIS-SIF' : '')?></title>
<?php
echo HTML::resourceCSS(RESOURCE_JQUERYUI_CSS);
echo HTML::css('common-d42c0d8a');
echo HTML::css('common-9fec4fcf');
echo HTML::css('common-0d763169');
?>
<script src="/sif/res/common/jquery/jquery-1.12.4.min.js"></script>
<?php
echo HTML::resourceJS(RESOURCE_JQUERYUI_JS);
echo HTML::resourceJS(RESOURCE_FONTAWESOME);
echo HTML::resourceJS(RESOURCE_LAZYLOAD);
echo HTML::resourceJS(RESOURCE_STORE);
echo HTML::resourceJS(RESOURCE_XBBCODEPARSER);
echo HTML::js('common-4fcb29e1');
echo HTML::js('common-d42c0d8a');
echo HTML::js('dict-d42c0d8a');
?>
<script>
<?php
echo HTML::json('resourceHosts', $config['resource_hosts']);
?>
</script>
