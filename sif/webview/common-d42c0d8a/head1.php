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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/Phosnoiiv/EIS-SIF-CDN@2aee66f6b75fb2b58dc2b767e6fae62e96a2b19f/jqueryui/1.12.1/jquery-ui.structure.min.css" integrity="sha256-rxais37anKUnpL5QzSYte+JnIsmkGmLG+ZhKSkZkwVM=" crossorigin="anonymous" />
<?php
echo HTML::css('common-d42c0d8a');
if ($useSIFStyle ?? false) {
    echo HTML::css('common-9fec4fcf');
}
?>
<script src="/sif/res/common/jquery/jquery-1.12.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/Phosnoiiv/EIS-SIF-CDN@2aee66f6b75fb2b58dc2b767e6fae62e96a2b19f/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha256-KM512VNnjElC30ehFwehXjx1YCHPiQkOPmqnrWtpccM=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.14.0/js/all.min.js" integrity="sha256-uNYoXefWRqv+PsIF/OflNmwtKM4lStn9yrz2gVl6ymo=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/store2@2.12.0/dist/store2.min.js" integrity="sha256-wHWwnHXFMh1IdY5kZN2T9YUDEU9ZJ4S70hQVk8Goeac=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/lazyload@2.0.0-rc.2/lazyload.min.js" integrity="sha256-WzuqEKxV9O7ODH5mbq3dUYcrjOknNnFia8zOyPhurXg=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/js-cookie@2.2.1/src/js.cookie.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xbbcode-parser@0.1.2/xbbcode.min.js"></script>
<?php
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
echo HTML::json('inMourningCN', Basic::inMourningCN());
echo HTML::json('resourceHost1', $config['resource_host_1']);
echo HTML::json('resourceHosts', $config['resource_hosts']);
echo HTML::json('SD', Basic::getAllDynamic());
?>
</script>
