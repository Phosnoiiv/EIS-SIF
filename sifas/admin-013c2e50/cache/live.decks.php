<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF\Util;
use EverISay\SIF\V1\AS\Archive\SazReader;

require_once dirname(dirname(__DIR__)) . '/core/init.php';

if (file_exists(ROOT_SIFAS_CACHE.'/live-decks.php')) {
    include ROOT_SIFAS_CACHE.'/live-decks.php';
} else {
    $cacheDeckSazProcessed = 0;
}

$sql = "SELECT * FROM saz_voltage_deck WHERE id>$cacheDeckSazProcessed";
$col = [['s','filename']];
$dFiles = DB::ltSelect(DB_EIS_CACHE, $sql, $col, 'id');
const PATH_1 = 'voltageRanking/getVoltageRanking', PATH_2 = 'voltageRanking/getVoltageRankingDeck';
foreach ($dFiles as $fileId => $dFile) {
    $reader = new SazReader($dFile[0], [PATH_1, PATH_2]);
    foreach ($reader->get(PATH_1) as $ranking) {
        $difficId = $ranking->decodeJsonRequest()[0]['live_difficulty_id'];
        $rankingResponse = $ranking->decodeJsonResponse();
        $cells = $rankingResponse[3]['voltage_ranking_cells'];
        if (file_exists(ROOT_SIFAS_CACHE."/decks/$difficId.json")) {
            $sData = Util::fromJSONFile(ROOT_SIFAS_CACHE."/decks/$difficId.json");
        } else {
            $sData = [];
        }
        foreach ($reader->get(PATH_2) as $deck) {
            $request = $deck->decodeJsonRequest()[0];
            if ($request['live_difficulty_id'] != $difficId) continue;
            $response = $deck->decodeJsonResponse()[3];
            foreach ($response['deck_detail']['deck']['cards'] as $card) {
                $cardId = $card['card_master_id'];
                $tBatchUserData[$request['user_id']]['cards'][] = $cardId;
            }
        }
        $report = ['cardCounts' => [], 'userCount' => 0];
        foreach ($cells as $cell) {
            $userId = $cell['voltage_ranking_user']['user_id'];
            if (isset($tBatchUserData[$userId])) {
                $sData['userLastSeen'][$userId] = $tBatchUserData[$userId] + ['voltage' => $cell['voltage_point']];
            } else if (($sData['userLastSeen'][$userId]['voltage'] ?? 0) != $cell['voltage_point']) continue;
            foreach ($sData['userLastSeen'][$userId]['cards'] as $cardId) {
                Util::arrayIncrement($report['cardCounts'], $cardId);
            }
            $report['userCount']++;
        }
        arsort($report['cardCounts']);
        $sData['reports'][floor($rankingResponse[0] / 1000)] = $report;
        Cache::writeJson("decks/$difficId.json", $sData);
        unset($tBatchUserData);
    }
}

Cache::writePhp('live-decks.php', [
    'cacheDeckSazProcessed' => $fileId,
]);
