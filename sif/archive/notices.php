<?php
namespace EIS\Lab\SIF;
$pageID = 24;
require_once dirname(__DIR__) . '/core/init.php';

DB::ltAttach('eis.s3db', 'archive.s3db', 'a');
$sql = "SELECT *,ifnull(time_record,time_publish) time_display FROM s_notice WHERE time_expire<datetime('now','localtime') AND archive IS NOT NULL
    UNION SELECT *,ifnull(time_record,time_publish) time_display FROM a_notice
    ORDER BY time_display DESC, id ASC";
$columns = [['s','icon'],['t','time_display',3],['s','title'],['s','content']];
$archiveNotices = DB::ltSelect('eis.s3db', $sql, $columns, 'id');

require ROOT_SIF_WEB . '/common-d42c0d8a/head1.php';
echo HTML::css('article');
require ROOT_SIF_WEB . '/common-d42c0d8a/head2.php';
?>
<article>
<div class="eis-jq-accordion" data-immediate=1>
<?php
foreach ($archiveNotices as $notice) {
    echo '<h2><i class="fas fa-' . ($notice[0] ?? 'bullhorn') . '"></i> ' . $notice[2] . '</h2><div>';
    echo '<h3><i class="fas fa-' . ($notice[0] ?? 'bullhorn') . '"></i> ' . $notice[2] . '</h3>';
    echo '<p class="note">' . date('Y/m/d H:i', $notice[1]) . '</p>';
    echo '<div class="eis-sif-notice-content">';
    foreach (explode('\\n', $notice[3]) as $paragraph) {
        echo '<p' . (mb_substr($paragraph, 0, 1) == '※' ? ' class="eis-sif-note"' : '') . '>' . $paragraph . '</p>';
    }
    echo "</div></div>\n";
}
?>
</div>
</article>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/foot.php';
