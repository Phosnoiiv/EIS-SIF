<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;

$instantNotices = $instantPageNotices = [];
if (!empty($pageID)) {
    $pageKey = $pages[$pageID]['key'] ?? $pageID;
    $sql = "SELECT * FROM s_notice_region WHERE region LIKE '$pageKey%' AND time_publish<=datetime('now','localtime') AND time_expire>=datetime('now','localtime')";
    $dbNotices = DB::lt_query('eis.s3db', $sql);
    while ($dbNotice = $dbNotices->fetchArray(SQLITE3_ASSOC)) {
        $region = substr($dbNotice['region'], strlen($pageKey) + 1);
        if (empty($region)) {
            $instantPageNotices[] = $dbNotice['content'];
        } else {
            $instantNotices[$region][] = $dbNotice['content'];
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="referrer" content="strict-origin-when-cross-origin"/>
<meta name="viewport" content="width=device-width,user-scalable=no"/>
<title><?=($title ?? $pages[$pageID]['title']) . (empty($isHome) ? ' - EIS-SIF' : '')?></title>
<?php
echo SIF\HTML::resourceCSS(SIF\RESOURCE_JQUERYUI_CSS);
echo SIF\HTML::css('common-d42c0d8a');
echo HTML::css('common-b63adcdf');
?>
<script src="/sif/res/common/jquery/jquery-1.12.4.min.js"></script>
<?php
echo HTML::resourceJS(SIF\RESOURCE_JQUERYUI_JS);
echo HTML::resourceJS(SIF\RESOURCE_FONTAWESOME);
echo HTML::resourceJS(SIF\RESOURCE_LAZYLOAD);
echo HTML::resourceJS(SIF\RESOURCE_STORE);
echo SIF\HTML::js('common-4fcb29e1');
echo SIF\HTML::js('common-d42c0d8a');
echo SIF\HTML::js('dict-d42c0d8a');
echo HTML::js('common-b63adcdf');
?>
<script>
<?php
if (!empty($instantNotices)) {
    echo SIF\HTML::json('regionNotices', $instantNotices);
}
echo SIF\HTML::json('inAprilFools', SIF\Basic::inAprilFools());
echo SIF\HTML::json('SD', SIF\Basic::getAllDynamic());
?>
</script>
