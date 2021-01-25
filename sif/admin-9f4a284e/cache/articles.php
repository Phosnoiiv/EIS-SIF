<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$sql = 'SELECT *, IFNULL(date_update,date_publish) AS date_record FROM article WHERE display>0 ORDER BY pin DESC, date_record DESC, id ASC';//status NOT IN (1)
$columns = [['i','id'],['s','title'],['T','date_record'],['i','tag'],['i','pin']];
$clientArticles = DB::mySelect($sql, $columns, 'panel', ['m'=>true]);
$sql = 'SELECT * FROM article WHERE status NOT IN (1)';
$columns = [['s','title'],['t','date_publish',3],['t','date_update',3],['i','tag'],['i','flow'],['i','watermark']];
$serverArticles = DB::mySelect($sql, $columns, 'id');

Cache::writeMultiJson('articles.js', [
    'articles' => $clientArticles,
]);
Cache::writePhp('articles.php', [
    'cacheArticles' => $serverArticles,
]);
