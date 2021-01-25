<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$stories = [];
$sql = 'SELECT chapter_id, jp_name, en_name, cn_name FROM story_chapter WHERE story_type=2';
$dbChapters = DB::my_query($sql);
while ($dbChapter = $dbChapters->fetch_assoc()) {
    $id = $dbChapter['chapter_id'];
    $stories[$id] = [
        $dbChapter['jp_name'] ?? "",
        $dbChapter['en_name'] ?? "",
        $dbChapter['cn_name'] ?? "",
        [],
    ];
}
$ref = [];
$sql = 'SELECT story_id, story_chapter, jp_name, en_name, cn_name FROM story';
$dbStories = DB::my_query($sql);
while ($dbStory = $dbStories->fetch_assoc()) {
    $id = $dbStory['story_id'];
    $chapter = $dbStory['story_chapter'];
    $ref[$id] = [$chapter, count($stories[$chapter][3])];
    $stories[$chapter][3][] = [
        $dbStory['jp_name'] ?? '',
        $dbStory['en_name'] ?? '',
        $dbStory['cn_name'] ?? '',
        [],
        [],
        [],
    ];
}
$sql = 'SELECT * FROM story_get';
$dbGets = DB::my_query($sql);
while ($dbGet = $dbGets->fetch_assoc()) {
    $id = $dbGet['story_id'];
    $server = $dbGet['server'];
    $timezone = SIF::getServerTimezone($server);
    $stories[$ref[$id][0]][3][$ref[$id][1]][2 + $server][] = [
        intval($dbGet['method']),
        strtotime($dbGet['time_open'] . $timezone),
        empty($dbGet['time_close']) ? 0 : strtotime($dbGet['time_close'] . $timezone),
        $dbGet['explanation'] ?? '',
    ];
}

Cache::writeMultiJson('stories.js', [
    'stories' => $stories,
]);
