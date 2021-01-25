<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;

if (!empty($galleryCache[0])) {
    $latestFile = ROOT_SIF_CACHE . '/' . $galleryCache[0];
}

$instantGroups = $instantJPGs = [];
$dictGroups = new Dict(true);
$sql = 'SELECT * FROM s_jpg_schedule WHERE time_open<=datetime("now","localtime") AND time_close>=datetime("now","localtime")';
$dbGroups = DB::lt_query('eis.s3db', $sql);
while ($dbGroup = $dbGroups->fetchArray(SQLITE3_ASSOC)) {
    $id = $dictGroups->set($dbGroup['group']);
    $instantGroups[$id] = [
        strtotime($dbGroup['time_close'] . '+0800'),
        $dbGroup['pass'],
    ];
    $sql = 'SELECT * FROM s_jpg WHERE category IN (' . implode(',', $galleryCategories) . ') AND id IN (SELECT jpg FROM s_jpg_group WHERE [group]=' . $dbGroup['group'] . ')';
    $dbJPGs = DB::lt_query('eis.s3db', $sql);
    while ($dbJPG = $dbJPGs->fetchArray(SQLITE3_ASSOC)) {
        $key = $dbJPG['key'];
        $instantJPGs[$key] = [
            $id,
            $dbJPG['code'],
        ];
    }
}

require ROOT_SIF_WEB . '/common-d42c0d8a/head1.php';
echo HTML::css('gallery-d42c0d8a');
foreach ($galleryCSS ?? [] as $css) {
    echo HTML::css($css);
}
echo HTML::js('gallery-d42c0d8a');
foreach ($galleryJS ?? [] as $js) {
    echo HTML::js($js);
}
?>
<script>
<?php
echo HTML::json('availableJPGGroups', $instantGroups);
echo HTML::json('availableJPGs', $instantJPGs);
foreach ($galleryCache ?? [] as $file) {
    echo Cache::read($file);
}
?>
</script>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/head2.php';
