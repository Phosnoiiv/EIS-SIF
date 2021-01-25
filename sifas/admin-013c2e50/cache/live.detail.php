<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

include ROOT_SIFAS_CACHE . '/drops.php';

DB::ltAttach('jp/masterdata.db', 'jp/dictionary_ja_k.db', 'k');
DB::ltAttach('jp/masterdata.db', 'jp/dictionary_ja_m.db', 'm');
DB::ltAttach('jp/masterdata.db', 'gl/dictionary_zh_k.db', 'kz');
DB::ltAttach('jp/masterdata.db', 'cache.s3db', 'c');
DB::ltAttach('jp/masterdata.db', 'restore.s3db', 'r');

$clientSongs = $detailSongs = $dictStrings = [];
$sql = 'SELECT m_live.*,kn.message AS k_name,kzn.message AS kz_name,kc.message AS k_copyright,ks.message AS k_source FROM m_live
    LEFT JOIN k.m_dictionary AS kn ON substr(name,3)=kn.id
    LEFT JOIN kz.m_dictionary AS kzn ON substr(name,3)=kzn.id
    LEFT JOIN k.m_dictionary AS kc ON substr(copyright,3)=kc.id
    LEFT JOIN k.m_dictionary AS ks ON substr(source,3)=ks.id
    WHERE live_id>10000
';
$dbSongs = DB::lt_query('jp/masterdata.db', $sql);
while ($dbSong = $dbSongs->fetchArray(SQLITE3_ASSOC)) {
    $id = $dbSong['live_id'] % 10000;
    if (isset($clientSongs[$id])) continue;
    $clientSongs[$id] = [
        $dbSong['is_2d_live'],
        /* 1 */ [],
        $dbSong['display_order'],
        /* 3 */ 0,null,null,null,null,
        /* 8 */ 999,0,0,
        /* 11 */ $dbSong['k_name'],
        $dbSong['kz_name'] ?? '',
        '','',
        /* 15 */ 0,null,
    ];
    $detailSongs[$id]['writers'] = $dbSong['k_copyright'];
    $detailSongs[$id]['source'] = $dbSong['k_source'];
    $detailSongs[$id]['maps'] = [null, [], [], [], [], []];
    if (!isset($dictHints[$id])) {
        $dictStrings[$id] = new SIF\Dict;
    }
    if (!isset($dictDrops[$id])) {
        $dictDrops[$id] = new SIF\Dict;
    }
    $allSongs[$id] = [[null, 0, 0, 0, 0, 0, 0, null, null, 0]];
}
$sql = 'SELECT live_song.*,live_track.en_name AS sif_en_name,live_track.cn_name AS sif_cn_name,moe_name FROM live_song LEFT JOIN sif.live_track ON sif=track_id';
$dbSongs = DB::my_query($sql);
while ($dbSong = $dbSongs->fetch_assoc()) {
    $id = $dbSong['id'];
    $clientSongs[$id][8] = intval($dbSong['release_order']);
    $clientSongs[$id][9] = intval($dbSong['route']);
    $clientSongs[$id][10] = intval($dbSong['route_param']);
    if (empty($clientSongs[$id][12])) $clientSongs[$id][12] = $dbSong['sif_en_name'] ?? '';
    if (empty($clientSongs[$id][13])) $clientSongs[$id][13] = $dbSong['sif_cn_name'] ?? '';
}

$sql = 'SELECT * FROM m_tower_clear_reward';
$columns = [['i','tower_clear_reward_id'],['i','content_type'],['i','content_id'],['i','content_amount']];
$dbRewards = DB::ltSelect('jp/masterdata.db', $sql, $columns, '');
foreach ($dbRewards as $dbReward) {
    $towerClearRewards[$dbReward[0]][] = array_slice($dbReward, 1);
    $item = array_slice($dbReward, 1, 2);
    if (!in_array($item, $listItems ?? [])) {
        $listItems[] = $item;
    }
}
$sql = 'SELECT * FROM m_tower_progress_reward';
$columns = [['i','tower_progress_reward_id'],['i','content_type'],['i','content_id'],['i','content_amount']];
$dbRewards = DB::ltSelect('jp/masterdata.db', $sql, $columns, '');
foreach ($dbRewards as $dbReward) {
    $towerProgressRewards[$dbReward[0]][] = array_slice($dbReward, 1);
}

$sql = 'SELECT m_mission.*,md.message AS m_desc FROM m_mission
    LEFT JOIN m.m_dictionary AS md ON substr(description,3)=md.id
    WHERE (mission_clear_condition_type IN (13,14) AND mission_clear_condition_param1 IS NOT NULL OR mission_clear_condition_type IN (15))
    AND m_mission.id NOT IN (SELECT id FROM c.mission WHERE jp=0)
';
$dbMissions = DB::lt_query('jp/masterdata.db', $sql);
while ($dbMission = $dbMissions->fetchArray(SQLITE3_ASSOC)) {
    $isType2 = in_array($dbMission['mission_clear_condition_type'], [15]);
    $songID = $dbMission['mission_clear_condition_param' . ($isType2 ? '2' : '1')] % 10000;
    preg_match('/^\[(.+)\]/', $dbMission['m_desc'], $matches);
    $detailSongs[$songID]['missions'][] = [
        $dbMission['term'],
        $dbMission['start_at'],
        $dbMission['end_at'] ?? 0,
        $dictStrings[$songID]->set($matches[1] ?? ''),
        $dbMission['mission_clear_condition_type'],
        $dbMission['mission_clear_condition_count'],
        [],
        $isType2 ? $dbMission['mission_clear_condition_param1'] : 0,
    ];
    $refMissions[$dbMission['id']] = [$songID, count($detailSongs[$songID]['missions']) - 1];
}
$sql = 'SELECT * FROM m_mission_reward';
$dbRewards = DB::lt_query('jp/masterdata.db', $sql);
while ($dbReward = $dbRewards->fetchArray(SQLITE3_ASSOC)) {
    $id = $dbReward['mission_id'];
    if (empty($ref = $refMissions[$id] ?? null))
        continue;
    $type = $dbReward['content_type'];
    $key = in_array($type, [4, 10]) ? 0 : $dbReward['content_id'];
    $detailSongs[$ref[0]]['missions'][$ref[1]][6][] = [
        $type,
        $key,
        $dbReward['content_amount'],
    ];
    if (!in_array([$type, $key], $listItems)) {
        $listItems[] = [$type, $key];
    }
}

$allStories = [];
$sql = 'SELECT m_story_main_cell.*,m_story_main_cell_transformation.live_difficulty_id AS tr_live FROM m_story_main_cell
    LEFT JOIN m_story_main_cell_transformation ON id=cell_id
';
$dbStories = DB::lt_query('jp/masterdata.db', $sql);
$currentChapter = $currentEpisode = 0;
while ($dbStory = $dbStories->fetchArray(SQLITE3_ASSOC)) {
    $chapter = $dbStory['chapter_id'];
    $episode = $dbStory['number'];
    if ($chapter != $currentChapter) {
        $currentChapter = $chapter;
        $currentEpisode = 1;
    } else if (!empty($episode)) {
        $currentEpisode = $episode;
    }
    if (!empty($live = $dbStory['tr_live'] ?? $dbStory['live_difficulty_id'])) {
        $allStories[$live] = [$currentChapter, $currentEpisode, 1];
    }
    if (!empty($dbStory['hard_live_difficulty_id'])) {
        $allStories[$dbStory['hard_live_difficulty_id']] = [$currentChapter, $currentEpisode, 2];
    }
}

$sql = 'SELECT * FROM tower';
$columns = [['s','display_name'],['s','short_name']];
$clientTowers = DB::mySelect($sql, $columns, 'id');

$sql = 'SELECT * FROM m_tower_composition';
$columns = [['i','tower_id'],['i','floor_no'],['i','live_difficulty_id'],['i','tower_clear_reward_id'],['i','tower_progress_reward_id']];
$towerFloors = DB::ltSelect('jp/masterdata.db', $sql, $columns, '');
$towerFloorsByMap = array_column($towerFloors, 2);
foreach ($towerFloors as $towerFloor) {
    $towerID = $towerFloor[0];
    if (!empty($mapID = $towerFloor[2])) {
        $liveFloorNumber = count($towerLiveFloors[$towerID] ?? []) + 1;
        $towerLiveFloors[$towerID][$liveFloorNumber] = $towerFloor[2];
        $towerLiveFloorsByMap[$mapID] = [$towerID, $liveFloorNumber];
    }
    if (!empty($towerFloor[4])) {
        $towerProgressFloors[$towerID][] = $liveFloorNumber;
    }
}

$clientEffects = [null];
$dbEffects = DB::my_query('SELECT * FROM word_effect');
while ($dbEffect = $dbEffects->fetch_assoc()) {
    $type = $dbEffect['type'];
    $clientEffects[$type] = [
        intval($dbEffect['buff']),
    ];
}

$dbTargets = DB::my_query('SELECT * FROM word_target WHERE icons IS NOT NULL');
while ($dbTarget = $dbTargets->fetch_assoc()) {
    $id = $dbTarget['id'];
    $clientTargets[$id] = [
        $dbTarget['icons_reverse'] ?? '',
        $dbTarget['icons'] ?? '',
    ];
}

$refMaps = [];
$dictCommonRewards = new SIF\Dict;
$sql = 'SELECT rld.*,m_live_difficulty_const.*,live_difficulty.*,
    kg.message AS k_gimmick,kzg.message AS kz_gimmick,kh.message AS k_hint,kzh.message AS kz_hint
    FROM (SELECT * FROM m_live_difficulty UNION SELECT * FROM r.m_live_difficulty) AS rld
    LEFT JOIN m_live_difficulty_const ON difficulty_const_master_id=m_live_difficulty_const.id
    LEFT JOIN (SELECT * FROM m_live_difficulty_gimmick UNION SELECT * FROM r.m_live_difficulty_gimmick) AS rldg ON live_difficulty_id=live_difficulty_master_id
    LEFT JOIN k.m_dictionary AS kg ON substr(name,3)=kg.id
    LEFT JOIN kz.m_dictionary AS kzg ON substr(name,3)=kzg.id
    LEFT JOIN (SELECT * FROM k.m_dictionary UNION SELECT * FROM r.m_dictionary_k) AS kh ON substr(description,3)=kh.id
    LEFT JOIN kz.m_dictionary AS kzh ON substr(description,3)=kzh.id
    LEFT JOIN c.live_difficulty ON live_difficulty_id=live_difficulty.id
    WHERE live_id>10000
';
$dbMaps = DB::lt_query('jp/masterdata.db', $sql);
while ($dbMap = $dbMaps->fetchArray(SQLITE3_ASSOC)) {
    $songID = $dbMap['live_id'] % 10000;
    $mapID = $dbMap['live_difficulty_id'];
    $mapType = intdiv($dbMap['live_difficulty_id'], 10000000);
    $attribute = $dbMap['default_attribute'];
    $extendData = [];
    if ($mapType == 5) {
        $towerFloor = $towerFloors[array_search($mapID, $towerFloorsByMap)];
        if (!isset($towerLiveFloorsByMap[$mapID])) {
            $mapReference = [$dbMap['tower'], 0, $dbMap['tower_text']];
        } else {
        $mapReference = $towerLiveFloorsByMap[$mapID];
        $towerID = $mapReference[0]; $liveFloorNumber = $mapReference[1];
        $extendData['c'] = $dictCommonRewards->set($towerClearRewards[$towerFloor[3]]);
        if (!empty($towerFloor[4])) {
            $extendData['r'] = $dictCommonRewards->set($towerProgressRewards[$towerFloor[4]]);
        } else {
            $extendData['o'] = min(array_filter($towerProgressFloors[$towerFloor[0]], function($a) { global $mapReference; return $a > $mapReference[1]; }));
        }
        for ($i = 1; $i <= 1; $i++) {
            if (!isset($towerLiveFloors[$towerID][$liveFloorNumber-$i])) break;
            $extendData['p'][] = floor($towerLiveFloors[$towerID][$liveFloorNumber-$i] / 1000) % 10000;
        }
        for ($i = 1; $i <= 5; $i++) {
            if (!isset($towerLiveFloors[$towerID][$liveFloorNumber+$i])) break;
            $extendData['n'][] = floor($towerLiveFloors[$towerID][$liveFloorNumber+$i] / 1000) % 10000;
        }
        }
    }
    if (($mapType == 4 && $dbMap['live_difficulty_type'] == 30) || $mapType == 5) {
        $extendData['d'] = $dbMap['live_difficulty_type'] / 10;
    }
    $detailSongs[$songID]['maps'][$mapType][] = [
        $mapType == 3 ? $allStories[$dbMap['live_difficulty_id']] ?? [$dbMap['old_story_chapter'], $dbMap['old_story_episode'], 0] : ($mapType == 5 ? $mapReference : ($difficulty = intdiv($dbMap['live_difficulty_id'] % 1000, 100))),
        $dbMap['default_attribute'],
        $dbMap['recommended_score'],
        $dbMap['recommended_stamina'],
        $dbMap['insufficient_rate'] / 100,
        $dbMap['consumed_lp'],
        $dbMap['reward_user_exp'],
        $dbMap['evaluation_s_score'],
        $dbMap['evaluation_a_score'],
        $dbMap['evaluation_b_score'],
        $dbMap['evaluation_c_score'],
        $dbMap['judge_id'],
        $dbMap['stamina_voltage_group_id'],
        $dbMap['combo_voltage_group_id'],
        $dbMap['sp_gauge_length'],
        $dbMap['note_stamina_reduce'],
        $dbMap['note_voltage_upper_limit'],
        $dbMap['collabo_voltage_upper_limit'],
        $dbMap['skill_voltage_upper_limit'],
        $dbMap['squad_change_voltage_upper_limit'],
        $dictStrings[$songID]->set($dbMap['k_gimmick']),
        $dictStrings[$songID]->set($dbMap['kz_gimmick'] ?? ''),
        $dictStrings[$songID]->set($dbMap['k_hint']),
        $dictStrings[$songID]->set($dbMap['kz_hint'] ?? ''),
        [],
        [],
        $dbMap['note_count'] ?? 0,
        /* 27 */ $dictDrops[$songID]->set($dbMap['note_drop_group_id']),
        $dictDrops[$songID]->set($dbMap['drop_content_group_id']),
        $dictDrops[$songID]->set($dbMap['rare_drop_content_group_id']),
        $dictDrops[$songID]->set($dbMap['additional_drop_content_group_id']),
        $dictDrops[$songID]->set($dbMap['additional_rare_drop_content_group_id']),
        $dbMap['rare_drop_rate'] / 100,
        $dbMap['bottom_technique'],
        $dbMap['additional_drop_decay_technique'],
        $dbMap['additional_drop_max_count'],
        /* 36 */ SIF\SIF::toDatestamp($dbMap['date_new'], 18000),
        SIF\SIF::toDatestamp($dbMap['date_finish'], 18000),
        /* 38 */ $extendData,
    ];
    $refMaps[$dbMap['live_difficulty_id']] = [$songID, $mapType, count($detailSongs[$songID]['maps'][$mapType]) - 1];
    $allSongs[$songID][0][$attribute]++;
    if ($mapType == 1 && $difficulty == 3) {
        $clientSongs[$songID][4] = $dbMap['evaluation_s_score'];
        $clientSongs[$songID][5] = $dbMap['recommended_score'];
        $clientSongs[$songID][6] = $dbMap['recommended_stamina'];
        $clientSongs[$songID][7] = $dbMap['note_stamina_reduce'];
    }
    if ($mapType == 1 && $difficulty == 4 && $dbMap['note_voltage_upper_limit'] > 50000) {
        $clientSongs[$songID][15] = $dbMap['default_attribute'];
        $clientSongs[$songID][16] = $dbMap['evaluation_s_score'];
    }
}
$sql = 'SELECT live_difficulty_id,note_gimmick_type,note_gimmick_icon_type,note_id,name,skill_target_master_id1,
    effect1.effect_type AS type1,effect1.effect_value AS value1,effect1.finish_type AS finish1,effect1.finish_value AS finishv1,
    kn.message AS k_name,kzn.message AS kz_name,kd.message AS k_desc,kzd.message AS kz_desc
    FROM (SELECT * FROM m_live_difficulty_note_gimmick UNION SELECT * FROM r.m_live_difficulty_note_gimmick) AS rldng
    LEFT JOIN m_skill ON skill_master_id=m_skill.id
    LEFT JOIN m_skill_effect AS effect1 ON skill_effect_master_id1=effect1.id
    LEFT JOIN k.m_dictionary AS kn ON substr(name,3)=kn.id
    LEFT JOIN kz.m_dictionary AS kzn ON substr(name,3)=kzn.id
    LEFT JOIN k.m_dictionary AS kd ON substr(description,3)=kd.id
    LEFT JOIN kz.m_dictionary AS kzd ON substr(description,3)=kzd.id
';
$dbNotes = DB::lt_query('jp/masterdata.db', $sql);
while ($dbNote = $dbNotes->fetchArray(SQLITE3_ASSOC)) {
    $ref = $refMaps[$dbNote['live_difficulty_id']];
    $location = &$detailSongs[$ref[0]]['maps'][$ref[1]][$ref[2]][24];
    if (($index = array_search($dbNote['name'], $allNotes[$dbNote['live_difficulty_id']] ?? [])) !== false) {
        $location[$index][1][] = $dbNote['note_id'];
    } else {
        $location[] = [
        $dbNote['note_gimmick_icon_type'],
            [$dbNote['note_id']],
        $dictStrings[$ref[0]]->set($dbNote['k_name']),
            $dbNote['note_gimmick_type'],
        $dictStrings[$ref[0]]->set($dbNote['k_desc']),
            $dbNote['skill_target_master_id1'],
            $dbNote['type1'],
            $dbNote['value1'],
            $dbNote['finish1'],
            $dbNote['finishv1'],
    ];
        $allNotes[$dbNote['live_difficulty_id']][] = $dbNote['name'];
    }
}
$sql = 'SELECT t.live_difficulty_id,state,live_wave.*,skill_target_master_id1,
    effect1.effect_type AS type1,effect1.effect_value AS value1,effect1.finish_type AS finish1,effect1.finish_value AS finishv1,
    kn.message AS k_name,kzn.message AS kz_name,kd.message AS k_desc,kzd.message AS kz_desc
    FROM (SELECT * FROM m_live_note_wave_gimmick_group UNION SELECT * FROM r.m_live_note_wave_gimmick_group) AS t
    LEFT JOIN m_skill ON skill_id=m_skill.id
    LEFT JOIN m_skill_effect AS effect1 ON skill_effect_master_id1=effect1.id
    LEFT JOIN (SELECT * FROM k.m_dictionary UNION SELECT * FROM r.m_dictionary_k) AS kn ON substr(name,3)=kn.id
    LEFT JOIN kz.m_dictionary AS kzn ON substr(name,3)=kzn.id
    LEFT JOIN (SELECT * FROM k.m_dictionary UNION SELECT * FROM r.m_dictionary_k) AS kd ON substr(description,3)=kd.id
    LEFT JOIN kz.m_dictionary AS kzd ON substr(description,3)=kzd.id
    LEFT JOIN c.live_wave ON t.live_difficulty_id=live_wave.live_difficulty AND t.wave_id=wave
';
$dbWaves = DB::lt_query('jp/masterdata.db', $sql);
while ($dbWave = $dbWaves->fetchArray(SQLITE3_ASSOC)) {
    $ref = $refMaps[$dbWave['live_difficulty_id']];
    $detailSongs[$ref[0]]['maps'][$ref[1]][$ref[2]][25][] = [
        $dictStrings[$ref[0]]->set($dbWave['k_name']),
        $dbWave['state'],
        $dictStrings[$ref[0]]->set($dbWave['k_desc']),
        $dbWave['skill_target_master_id1'],
        $dbWave['start'] ?? 0,
        $dbWave['finish'] ?? 0,
        $dbWave['voltage'],
        $dbWave['damage'],
        $dbWave['type1'],
        $dbWave['value1'],
        $dbWave['finish1'],
        $dbWave['finishv1'],
    ];
}

$clientGimmicks = [];
$sql = 'SELECT * FROM icon WHERE category="gimmick"';
$dbGimmicks = DB::my_query($sql);
while ($dbGimmick = $dbGimmicks->fetch_assoc()) {
    $id = $dbGimmick['key'];
    $clientGimmicks[$id] = $dbGimmick['path'];
}

$clientEvents = [null];
$sql = 'SELECT * FROM event ORDER BY `order` ASC';
$dbEvents = DB::my_query($sql);
while ($dbEvent = $dbEvents->fetch_assoc()) {
    $order = intval($dbEvent['order']);
    $clientEvents[$order] = [
        intval($dbEvent['type']),
        $dbEvent['jp_name'],
        $dbEvent['en_name'] ?? '',
        $dbEvent['zh_name'] ?? '',
        SIF\SIF::toTimestamp($dbEvent['jp_open'], 1),
        empty($dbEvent['gl_open']) ? 0 : SIF\SIF::toTimestamp($dbEvent['gl_open'], 1),
        empty($dbEvent['cn_open']) ? 0 : SIF\SIF::toTimestamp($dbEvent['cn_open'], 3),
        $dbEvent['jp_logo'] ?? '',
        $dbEvent['en_logo'] ?? '',
        $dbEvent['zh_logo'] ?? '',
    ];
    if (($end = SIF\SIF::toTimestamp($dbEvent['jp_end'], 1)) > time()) {
        $clientCurrentEvents[] = [$order, 1, $end];
        $listCurrentEvents[] = $order;
    }
    if (($end = SIF\SIF::toTimestamp($dbEvent['gl_end'], 1)) > time()) {
        $clientCurrentEvents[] = [$order, 2, $end];
        $listCurrentEvents[] = $order;
    }
}

$sql = 'SELECT event_song.*, event.order FROM event_song LEFT JOIN event ON event_song.event=event.id';
$dbEventSongs = DB::my_query($sql);
while ($dbEventSong = $dbEventSongs->fetch_assoc()) {
    $songID = intval($dbEventSong['song']);
    $detailSongs[$songID]['events'][] = [
        $order = intval($dbEventSong['order']),
        $type = intval($dbEventSong['type']),
        $dbEventSong['jp_emblem'] ?? '',
        $dbEventSong['en_emblem'] ?? '',
    ];
    if (in_array($order, $listCurrentEvents ?? [])) {
        $clientEvents[$order][10][] = [$type, $songID];
    }
}

$listItems = array_merge($listItems, $cacheDropItems);
$clientItems = [];
$sql = 'SELECT * FROM item_general';
$dbItems = DB::my_query($sql);
while ($dbItem = $dbItems->fetch_assoc()) {
    $type = intval($dbItem['item_type']);
    $key = intval($dbItem['item_key']);
    if (!in_array([$type, $key], $listItems))
        continue;
    $clientItems[$type][$key] = [
        $dbItem['jp_image1'],
        $dbItem['jp_name'] ?? '',
        $dbItem['en_name'] ?? '',
        $dbItem['zh_name'] ?? '',
    ];
}

$clientEmblems = [];
$sql = 'SELECT * FROM item_emblem';
$dbEmblems = DB::my_query($sql);
while ($dbEmblem = $dbEmblems->fetch_assoc()) {
    $id = intval($dbEmblem['id']);
    if (!in_array([15, $id], $listItems))
        continue;
    $clientEmblems[$id] = [
        $dbEmblem['jp_image'],
        $dbEmblem['jp_name'] ?? '',
    ];
}

foreach (array_keys($clientSongs) as $songID) {
    $clientSongs[$songID][3] = array_search(max($allSongs[$songID][0]), $allSongs[$songID][0]);
}
Cache::writeMultiJson('live-detail.js', [
    'songs' => $clientSongs,
    'effects' => $clientEffects,
    'targets' => $clientTargets,
    'gimmicks' => $clientGimmicks,
    'events' => $clientEvents,
    'currentEvents' => $clientCurrentEvents ?? [],
    'towers' => $clientTowers,
    'items' => $clientItems,
    'emblems' => $clientEmblems,
    'rewards' => $dictCommonRewards->getAll(),
]);
foreach ($detailSongs as $id => $detail) {
    $detail['strings'] = $dictStrings[$id]->getAll();
    $detail['drops'] = array_map(function($id) {
        if (empty($id))
            return [];
        global $cacheDropGroups;
        return json_decode($cacheDropGroups[$id]);
    }, $dictDrops[$id]->getAll());
    usort($detail['maps'][3], function($m1, $m2) {
        if (($r = ($m1[0][2] == 0) - ($m2[0][2] == 0)) != 0) return $r;
        if ($m1[0][0] != $m2[0][0])
            return $m1[0][0] - $m2[0][0];
        return $m1[0][1] - $m2[0][1];
    });
    Cache::writeJson('lives/' . $id . '.json', $detail, true);
}
