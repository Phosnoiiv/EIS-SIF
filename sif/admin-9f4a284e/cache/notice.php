<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$notices = [];
$db_notices = DB::my_query('SELECT * FROM site_notice
    WHERE time_expire>NOW()
    ORDER BY time_publish DESC, notice_id ASC
');
while ($notice = $db_notices->fetch_assoc()) {
    $id = intval($notice['notice_id']);
    $notices[$id] = [
        'title' => $notice['title'],
        'content' => $notice['content'],
        'time_publish' => strtotime($notice['time_publish']),
        'time_expire' => strtotime($notice['time_expire']),
    ];
}
Cache::writePhp('notice.php', [
    'notices' => $notices,
]);
