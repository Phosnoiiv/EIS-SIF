<?php
namespace EIS\Lab\SIF;
require_once dirname(__DIR__) . '/core/init.php';

$flow = intval($_REQUEST['f']);
$post = $_POST['p'] ?? '';
$userIdentity = $_SERVER['REMOTE_ADDR'];

if (!empty($post)) {
    DB::myInsert('run_flow_post', [
        'flow' => ['i', $flow],
        'identity' => ['s', $userIdentity],
        'content' => ['s', htmlspecialchars($post)],
        'time_publish' => ['s', date('Y-m-d H:i:s')],
        'status' => ['i', 0],
    ]);
}

$dbPosts = DB::my_query("SELECT * FROM run_flow_post WHERE flow=$flow AND status=0");
while ($dbPost = $dbPosts->fetch_assoc()) {
    $posts[] = [
        intval($dbPost['id']),
        $dbPost['content'],
        SIF::toTimestamp($dbPost['time_publish'], 3),
    ];
}

header('Content-type: application/json');
echo HTML::json('', [
    'posts' => $posts ?? [],
]);
