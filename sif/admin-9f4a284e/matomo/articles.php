<?php
namespace EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$url = $config['matomo_scheme'].'://'.$config['matomo_host'].'/matomo/?module=API&method=Actions.getPageUrls&idSite=1&period=day&date=last7&format=json&flat=1&filter_pattern=/sif/article/\?\d+&showColumns=nb_visits&token_auth='.$config['matomo_token'];
$data = json_decode(file_get_contents($url), true);

$coefficients = [1,2,3,5,8,12,12];
$currentDay = 0;
foreach ($data as $date => $dailyData) {
    foreach ($dailyData as $pageData) {
        $articleID = intval(substr($pageData['label'], 14));
        $weights[$articleID] = ($weights[$articleID] ?? 0) + $pageData['nb_visits'] * $coefficients[$currentDay];
    }
    $currentDay++;
}
arsort($weights);
Cache::writeMultiJson('matomo/articles.js', [
    'articleHots' => $weights,
]);
