<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

DB::ltAttach('jp/masterdata.db', 'jp/dictionary_ja_m.db', 'm');
DB::ltAttach('gl/masterdata.db', 'gl/dictionary_en_m.db', 'm');
DB::ltAttach('cn/masterdata.db', 'cn/dictionary_zh_m.db', 'm');
DB::ltAttach('jp/masterdata.db', 'cache.s3db', 'c');
DB::ltAttach('gl/masterdata.db', 'cache.s3db', 'c');
DB::ltAttach('cn/masterdata.db', 'cache.s3db', 'c');

$sql = 'SELECT * FROM event';
$columns = [['i','type'],['s','jp_name'],['s','en_name',''],['s','cn_name',''],['t','jp_open',1],['t','jp_end',1],['t','gl_open',1],['t','gl_end',1],['t','cn_open',3],['t','cn_end',3],['s','jp_banner2',''],['s','en_banner2',''],['s','cn_banner2','']];
$clientEvents = DB::mySelect($sql, $columns, 'order');
$eventCount = count($clientEvents);
$clientEvents[0] = null;
ksort($clientEvents);

$sql = 'SELECT * FROM tower_term';
$columns = [['t','jp_open',1],['t','jp_close',1],['t','gl_open',1],['t','gl_close',1],['t','cn_open',3],['t','cn_close',3],['s','jp_banner2',''],['s','en_banner2',''],['s','cn_banner2','']];
$clientTowerTerms = DB::mySelect($sql, $columns, 'id', ['z'=>true]);
$towerTermCount = count($clientTowerTerms) - 1;

$sql = 'SELECT * FROM campaign WHERE id IN (SELECT DISTINCT campaign FROM campaign_mission)';
$columns = [['s','jp_name',''],['s','en_name',''],['s','cn_name',''],['s','jp_banner2',''],['s','en_banner2',''],['s','cn_banner2',''],['s','intro','']];
$clientCampaigns = DB::mySelect($sql, $columns, 'id');
$listCampaigns = array_keys($clientCampaigns);
$campaignCount = count($listCampaigns);

$sql = 'SELECT mission,`order` FROM event_mission LEFT JOIN event ON event=id';
$eventMissions = DB::mySelect($sql, [['i','order']], 'mission', ['s'=>true]);
$sql = 'SELECT * FROM tower_mission';
$towerMissions = DB::mySelect($sql, [['i','term']], 'mission', ['s'=>true]);
$sql = 'SELECT * FROM campaign_mission';
$campaignMissions = DB::mySelect($sql, [['i','campaign']], 'mission', ['s'=>true]);

foreach ([1=>'jp',2=>'gl',3=>'cn'] as $server => $serverKey) {
    $sql = "SELECT * FROM rs_mission WHERE `server`=$server";
    $col = [['i','time_open'],['i','time_close'],['i','type'],['i','count'],['i','param1'],['i','param2']];
    $restoreMissions = DB::ltSelect(DB_EIS_RESTORE, $sql, $col, 'id');

    $sql = "SELECT * FROM rs_mission_reward WHERE `server`=$server";
    $col = [['i','item_type'],['i','item_key'],['i','amount']];
    $restoreRewards = DB::ltSelect(DB_EIS_RESTORE, $sql, $col, 'id', ['m'=>true]);

    $prfSrvEvent = $serverKey.'_event';
    $sql = "SELECT * FROM c.mission WHERE $prfSrvEvent IS NOT NULL";
    $replaceEventIDs = DB::ltSelect($serverKey.'/masterdata.db', $sql, [['i',$prfSrvEvent]], 'id', ['s'=>true]);

    $rewards = $clientMissions = [];
    $sql = 'SELECT * FROM m_mission_reward WHERE mission_id IN (SELECT id FROM m_mission WHERE pickup_type=3)';
    $columns = [['i','mission_id'],['i','content_type'],['i','content_id'],['i','content_amount']];
    $dbRewards = DB::ltSelect($serverKey . '/masterdata.db', $sql, $columns, '');
    foreach ($dbRewards as $dbReward) {
        if (in_array($dbReward[1], [4,10])) $dbReward[2] = 0;
        $rewards[$dbReward[0]][] = array_slice($dbReward, 1);
        $rewardItem = array_slice($dbReward, 1, 2);
        if (!in_array($rewardItem, $rewardItems ?? [])) {
            $rewardItems[] = $rewardItem;
        }
    }

    $dictPeriods = new SIF\Dict;
    $dictTopics = new SIF\Dict;
    $dictRewards = new SIF\Dict;
    $sql = 'SELECT m_mission.*, md.message AS m_desc FROM m_mission
        LEFT JOIN m.m_dictionary AS md ON substr(description,3)=md.id
        WHERE pickup_type=3 AND m_mission.id NOT IN (SELECT id FROM c.mission WHERE ' . $serverKey . '=0)';
    $columns = [
        ['i','term'],['i','mission_clear_condition_type'],['i','mission_clear_condition_count'],['i','mission_clear_condition_param1',0],['i','mission_clear_condition_param2',0],
        ['i','start_at'],['i','end_at'],
        ['s','m_desc'],
    ];
    $dbMissions = DB::ltSelect($serverKey . '/masterdata.db', $sql, $columns, 'id');
    foreach ($dbMissions as $id => $dbMission) {
        if (isset($restoreMissions[$id])) {
            $tRestore = $restoreMissions[$id];
            foreach ([1=>2,2=>3,3=>4,4=>5,5=>0,6=>1] as $f=>$t) if ($tRestore[$t]!==null) $dbMission[$f] = $tRestore[$t];
        }
        preg_match('/^\[(.+)\]/', $dbMission[7], $matches);
        preg_match('/^(.+?):/', $dbMission[7], $matches2);
        $clientMission = array_slice($dbMission, 0, 5);
        if (isset($eventMissions[$id])) {
            $groupID = $eventMissions[$id];
        } else if (isset($towerMissions[$id])) {
            $groupID = $eventCount + $towerMissions[$id];
        } else if (isset($campaignMissions[$id])) {
            $groupID = $eventCount + $towerTermCount + 1 + array_search($campaignMissions[$id], $listCampaigns);
        } else if (isset($replaceEventIDs[$id])) {
            $groupID = $replaceEventIDs[$id];
        } else if ($id < 500000000) {
            try {
            $groupID = min(array_keys(array_filter($clientEvents, function($e) {
                global $server, $dbMission;
                    return !empty($e) && in_array($e[0], [1,2]) && $dbMission[5] < $e[3+2*$server];
            })));
            } catch (\ValueError $ex) {
                SIF\Basic::exit("ValueError (server=$server missionID=$id)");
            }
        } else {
            $groupID = $eventCount + $towerTermCount + $campaignCount + 1 + $dictTopics->set($matches[1] ?? $matches2[1] ?? '');
        }
        array_push($clientMission,
            $dictPeriods->set([$dbMission[5], $dbMission[6]]),
            $groupID,
            $dictRewards->set($restoreRewards[$id] ?? $rewards[$id]),
        );
        $clientMissions[] = $clientMission;
    }
    Cache::writeJson('goals/' . $server . '.json', [
        'goals' => array_values($clientMissions),
        'periods' => $dictPeriods->getAll(),
        'topics' => $dictTopics->getAll(),
        'rewards' => $dictRewards->getAll(),
    ]);
}

$sql = 'SELECT DISTINCT mission_clear_condition_param1 % 10000 AS songID FROM m_mission WHERE pickup_type=3 AND mission_clear_condition_type IN (13,14) AND mission_clear_condition_param1 IS NOT NULL';
$listSongs = DB::ltSelect('jp/masterdata.db', $sql, [['i','songID']], '', ['s'=>true]);
$sql = 'SELECT * FROM live_song WHERE id IN (' . implode(',', $listSongs) . ')';
$columns = [['s','jp_name']];
$clientSongs = DB::mySelect($sql, $columns, 'id');

$sql = "SELECT sifas_id,IFNULL(zhs_name,jp_name) `name` FROM v_member_v107 WHERE sifas_id IS NOT NULL";
$clientMembers = DB::mySelect($sql, [['s','name']], 'sifas_id');

$clientWords = [null];
$dbWords = DB::my_query('SELECT * FROM word_mission');
while ($dbWord = $dbWords->fetch_assoc()) {
    $type = $dbWord['type'];
    $id = $dbWord['id'];
    $clientWords[$type][$id] = [
        intval($dbWord['disable_skip']),
        $dbWord['display_free'] ?? $dbWord['cn_free'] ?? $dbWord['cn_free_append'],
        $dbWord['display_event'] ?? $dbWord['cn_event'] ?? $dbWord['cn_event_append'],
    ];
}

$dictItemStrings = new SIF\Dict;
$sql = 'SELECT * FROM item_general';
$columns = [['i','item_type'],['i','item_key'],['s','jp_name',''],['s','en_name',''],['s','cn_name',''],['s','jp_image1'],['s','jp_desc',''],['s','en_desc',''],['s','cn_desc',''],['s','intro','']];
$dbItems = DB::mySelect($sql, $columns, '');
foreach ($dbItems as $dbItem) {
    $type = $dbItem[0]; $key = $dbItem[1];
    if (!in_array([$type, $key], $rewardItems)) continue;
    $clientItems[$type][$key] = array_merge(
        array_slice($dbItem, 2, 4),
        array_map(function($s) {
            global $dictItemStrings;
            return $dictItemStrings->set($s);
        }, array_slice($dbItem, 6)),
    );
}

$sql = 'SELECT * FROM item_emblem WHERE id IN (' . implode(',', array_column(array_filter($rewardItems, function($r){return $r[0]==15;}), 1)) . ')';
$columns = [['s','jp_name',''],['s','en_name',''],['s','cn_name',''],['s','jp_image',''],['s','en_image',''],['s','cn_image',''],['s','jp_desc',''],['s','en_desc',''],['s','cn_desc',''],['s','intro','']];
$clientEmblems = DB::mySelect($sql, $columns, 'id');

Cache::writeMultiJson('goals.js', [
    'events' => $clientEvents,
    'towerTerms' => $clientTowerTerms,
    'campaigns' => array_values($clientCampaigns),
    'songs' => $clientSongs,
    'members' => $clientMembers,
    'words' => $clientWords,
    'items' => $clientItems,
    'itemStrings' => $dictItemStrings->getAll(),
    'emblems' => $clientEmblems,
]);
