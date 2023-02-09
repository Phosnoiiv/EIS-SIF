<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;

$instantGeneralNotices = [];
$sql = "SELECT * FROM s_notice WHERE time_publish<=datetime('now','localtime') AND (time_expire IS NULL OR time_expire>=datetime('now','localtime'))";
$dbNotices = DB::lt_query('eis.s3db', $sql);
while ($dbNotice = $dbNotices->fetchArray(SQLITE3_ASSOC)) {
    $id = $dbNotice['id'];
    $instantGeneralNotices[$id] = [
        $dbNotice['icon'] ?? '',
        strtotime($dbNotice['time_record'] ?? $dbNotice['time_publish']),
        $dbNotice['title'],
        $dbNotice['content'],
        $dbNotice['pin'],
        $dbNotice['tab'] ?? 0,
    ];
}
$sql = "SELECT * FROM s_notice_fixed WHERE time_publish<+datetime('now','localtime') AND (time_expire IS NULL OR time_expire>=datetime('now','localtime'))";
$dbNotices = DB::lt_query('eis.s3db', $sql);
while ($dbNotice = $dbNotices->fetchArray(SQLITE3_ASSOC)) {
    $id = $dbNotice['id'];
    $instantGeneralNotices[10000+$id] = [
        $dbNotice['icon'] ?? '',
        strtotime($dbNotice['time_publish']),
        $dbNotice['title'],
        '',
    ];
}

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
echo HTML::resourceCSS(RESOURCE_JQUERYUI_CSS);
V2::includeV2FrontCss();
echo HTML::css('common-d42c0d8a');
if ($useSIFStyle ?? false) {
    echo HTML::css('common-9fec4fcf');
}
if (!empty($styleThemeCss = Basic::getStyleThemeCss())) {
    echo '<style>', $styleThemeCss, "</style>\n";
}
?>
<script src="/sif/res/common/jquery/jquery-1.12.4.min.js"></script>
<?php
echo HTML::resourceJS(RESOURCE_JQUERYUI_JS);
echo HTML::resourceJS(RESOURCE_FONTAWESOME);
echo HTML::resourceJS(RESOURCE_JSCOOKIE);
echo HTML::resourceJS(RESOURCE_LAZYLOAD);
echo HTML::resourceJS(RESOURCE_STORE);
echo HTML::resourceJS(RESOURCE_XBBCODEPARSER);
echo HTML::js('common-4fcb29e1');
echo HTML::js('common-d42c0d8a');
echo HTML::js('dict-d42c0d8a');
?>
<script>
<?php
if (!empty($isHome))
echo HTML::json('notices', $instantGeneralNotices);
if (!empty($instantNotices)) {
    echo HTML::json('regionNotices', $instantNotices);
}
echo HTML::json('inAprilFools', Basic::inAprilFools());
echo HTML::json('resourceHost1', $config['resource_host_1']);
echo HTML::json('resourceHosts', $config['resource_hosts']);
echo HTML::json('SD', Basic::getAllDynamic());
?>
</script>
