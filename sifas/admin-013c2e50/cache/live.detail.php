<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
use EIS\Lab\SIF\Util;
use EverISay\SIF\V1\AS\Data\LiveExtendDataManager;
use EverISay\SIF\V1\AS\Data\SuyoooLiveExtendDataProvider;
use EverISay\SIF\V1\AS\Data\V1LiveExtendDataProvider;

require_once dirname(dirname(__DIR__)) . '/core/init.php';

include ROOT_SIFAS_CACHE . '/drops.php';

DB::ltAttach('jp/masterdata.db', 'jp/dictionary_ja_k.db', 'k');
DB::ltAttach('jp/masterdata.db', 'jp/dictionary_ja_m.db', 'm');
DB::ltAttach('jp/masterdata.db', 'gl/dictionary_zh_k.db', 'kz');
DB::ltAttach('jp/masterdata.db', 'cn/dictionary_zh_k.db', 'ks');
DB::ltAttach('jp/masterdata.db', 'cache.s3db', 'c');
DB::ltAttach('jp/masterdata.db', 'restore.s3db', 'r');

$clientSongs = $detailSongs = $dictStrings = [];
$sql = 'SELECT m_live.*,kn.message AS k_name,kzn.message AS kz_name,kc.message AS k_copyright,ks.message AS k_source FROM m_live
    LEFT JOIN k.m_dictionary AS kn ON substr(name,3)=kn.id
    LEFT JOIN kz.m_dictionary AS kzn ON substr(name,3)=kzn.id
    LEFT JOIN k.m_dictionary AS kc ON substr(copyright,3)=kc.id
    LEFT JOIN k.m_dictionary AS ks ON substr(source,3)=ks.id
    WHERE live_id>10000 AND live_id NOT IN (SELECT id FROM c.live WHERE hide IS NOT NULL)
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
    ];
    // Since v1.8, [3-7] is no longer used.
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
$sql = 'SELECT live_song.*,live_track.en_name AS sif_en_name,live_track.cn_name AS sif_cn_name,moe_name FROM live_song LEFT JOIN sif.live_track ON sif=track_id WHERE release_order<1000';
$dbSongs = DB::my_query($sql);
while ($dbSong = $dbSongs->fetch_assoc()) {
    $id = $dbSong['id'];
    $clientSongs[$id][8] = intval($dbSong['release_order']);
    $clientSongs[$id][9] = intval($dbSong['route']);
    $clientSongs[$id][10] = intval($dbSong['route_param']);
    if (empty($clientSongs[$id][12])) $clientSongs[$id][12] = $dbSong['sif_en_name'] ?? '';
    if (empty($clientSongs[$id][13])) $clientSongs[$id][13] = $dbSong['zhs_name'] ?? $dbSong['sif_cn_name'] ?? '';
}

$sql = 'SELECT * FROM live_song_group';
$columns = [['s','caption']];
$clientSongGroups = DB::mySelect($sql, $columns, 'group_id', ['z'=>true,'s'=>true,'k'=>'song']);

$sql = 'SELECT * FROM tag_song WHERE display_order>0';
$columns = [['i','display_order'],['i','display_class'],['s','name'],['s','short_name'],['i','type'],['s','intro']];
$cSongTags = DB::mySelect($sql, $columns, 'id');

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

$sql = "SELECT tc.*,default_attribute FROM m_tower_composition tc LEFT JOIN m_live_difficulty ld USING (live_difficulty_id)";
$columns = [['i','tower_id'],['i','floor_no'],['i','live_difficulty_id'],['i','tower_clear_reward_id'],['i','tower_progress_reward_id'],['i','default_attribute']];
$towerFloors = DB::ltSelect('jp/masterdata.db', $sql, $columns, '');
$towerFloorsByMap = array_column($towerFloors, 2);
foreach ($towerFloors as $towerFloor) {
    $towerID = $towerFloor[0];
    if (!empty($mapID = $towerFloor[2])) {
        $liveFloorNumber = count($towerLiveFloors[$towerID] ?? []) + 1;
        $towerLiveFloors[$towerID][$liveFloorNumber] = [floor($towerFloor[2]/1000)%10000, $towerFloor[5]];
        $towerLiveFloorsByMap[$mapID] = [$towerID, $liveFloorNumber];
    }
    if (!empty($towerFloor[4])) {
        $towerProgressFloors[$towerID][] = $liveFloorNumber;
    }
}

$sql = 'SELECT s.id,effect_type
    FROM m_skill s
    LEFT JOIN m_skill_effect se ON skill_effect_master_id1=se.id
    WHERE s.id IN (SELECT DISTINCT skill_master_id FROM m_live_difficulty_gimmick)';
$columns = [['i','effect_type']];
$rSkills = DB::ltSelect(DB_GAME_JP_MASTER, $sql, $columns, 'id');

$sql = 'SELECT * FROM word_effect';
$columns = [['i','is_buff'],['i','is_base']];
$rEffects = DB::mySelect($sql, $columns, 'type');
$clientEffects = array_merge([null], array_map(fn($a)=>array_splice($a,0,1), $rEffects));

$dbTargets = DB::my_query('SELECT * FROM word_target WHERE icons IS NOT NULL');
while ($dbTarget = $dbTargets->fetch_assoc()) {
    $id = $dbTarget['id'];
    $clientTargets[$id] = [
        $dbTarget['icons_reverse'] ?? '',
        $dbTarget['icons'] ?? '',
    ];
}
$sql = "SELECT * FROM skill_target_v109";
$col = [['s','icons',''],['s','icons_reverse','']];
$cTargets = DB::mySelect($sql, $col, 'id');

$sql = "SELECT ldg.*,k1.message k1m,k2.message k2m,ks1.message ks1m,ks2.message ks2m
        FROM (SELECT * FROM m_live_difficulty_gimmick UNION SELECT * FROM r.m_live_difficulty_gimmick) ldg
        LEFT JOIN (SELECT * FROM k.m_dictionary UNION SELECT * FROM r.m_dictionary_k) k1 ON substr(ldg.name,3)=k1.id
        LEFT JOIN (SELECT * FROM k.m_dictionary UNION SELECT * FROM r.m_dictionary_k) k2 ON substr(ldg.description,3)=k2.id
        LEFT JOIN ks.m_dictionary ks1 ON substr(ldg.name,3)=ks1.id
        LEFT JOIN ks.m_dictionary ks2 ON substr(ldg.description,3)=ks2.id";
$col = [['i','skill_master_id'],['s','k1m',''],['s','k2m',''],['s','ks1m',''],['s','ks2m','']];
$dLiveGimmicks = DB::ltSelect(DB_GAME_JP_MASTER, $sql, $col, 'live_difficulty_master_id', ['m'=>true]);

$extendDataManager = new LiveExtendDataManager([
    new V1LiveExtendDataProvider,
    new SuyoooLiveExtendDataProvider($config['SIFAS_live_extend_suyooo']),
]);

$refMaps = [];
$dictCommonRewards = new SIF\Dict;
$sql = 'SELECT rld.*,m_live_difficulty_const.*,live_difficulty.*
    FROM (SELECT * FROM m_live_difficulty UNION SELECT * FROM r.m_live_difficulty) AS rld
    LEFT JOIN m_live_difficulty_const ON difficulty_const_master_id=m_live_difficulty_const.id
    LEFT JOIN c.live_difficulty ON live_difficulty_id=live_difficulty.id
    WHERE live_id>10000 AND live_difficulty_id<60000000 AND hide IS NULL
';
$dbMaps = DB::lt_query('jp/masterdata.db', $sql);
while ($dbMap = $dbMaps->fetchArray(SQLITE3_ASSOC)) {
    $songID = $dbMap['live_id'] % 10000;
    $mapID = $dbMap['live_difficulty_id'];
    $mapType = intdiv($dbMap['live_difficulty_id'], 10000000);
    $attribute = $dbMap['default_attribute'];
    $liveExtendData = $extendDataManager->get($mapID);
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
            $tProgressFloors = array_filter($towerProgressFloors[$towerFloor[0]], fn($x)=>$x>$mapReference[1]);
            if (!empty($tProgressFloors)) {
                $extendData['o'] = min($tProgressFloors);
            } else {
                $extendData['f'] = true;
            }
        }
        for ($i = 1; $i <= 1; $i++) {
            if (!isset($towerLiveFloors[$towerID][$liveFloorNumber-$i])) break;
            $extendData['p'][] = $towerLiveFloors[$towerID][$liveFloorNumber-$i];
        }
        for ($i = 1; $i <= 5; $i++) {
            if (!isset($towerLiveFloors[$towerID][$liveFloorNumber+$i])) break;
            $extendData['n'][] = $towerLiveFloors[$towerID][$liveFloorNumber+$i];
        }
        }
    }
    if (($mapType == 4 && $dbMap['live_difficulty_type'] >= 30) || $mapType == 5) {
        $extendData['d'] = SIFAS::MAP_DIF_SHORT_CODE[$dbMap['live_difficulty_type']];
    }
    if (isset($dbMap['coop_power'])) {
        $extendData['w'] = $dbMap['coop_power'];
    }
    if (isset($liveExtendData) && $liveExtendData->provider != V1LiveExtendDataProvider::class) {
        $extendData['x'] = 's';
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
        array_map(fn($x)=>$dictStrings[$songID]->set($x), array_column($dLiveGimmicks[$mapID],1)),
        array_map(fn($x)=>$dictStrings[$songID]->set($x), array_column($dLiveGimmicks[$mapID],3)),
        array_map(fn($x)=>$dictStrings[$songID]->set($x), array_column($dLiveGimmicks[$mapID],2)),
        array_map(fn($x)=>$dictStrings[$songID]->set($x), array_column($dLiveGimmicks[$mapID],4)),
        [],
        [],
        $liveExtendData?->noteCount ?? 0,
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
    if ($mapType==1 && in_array($difficulty,Constants::EIS_SONG_DIF_NOTEWORTHY) && !($difficulty==4&&$dbMap['note_voltage_upper_limit']<=50000)) {
        $clientSongs[$songID][1][$difficulty] = [$dbMap['default_attribute'], $dbMap['evaluation_s_score'], $dbMap['recommended_score'], $dbMap['recommended_stamina'], $dbMap['note_stamina_reduce'], []];
        foreach ($dLiveGimmicks[$mapID] as $dLiveGimmick) {
            $tEffect = $rEffects[$rSkills[$dLiveGimmick[0]][0]];
        if (!$tEffect[0] && !$tEffect[1]) {
            $clientSongs[$songID][1][$difficulty][5][] = Constants::EIS_SONG_TAG_3_NEGATE;
        }
        }
        ${"mMap$difficulty"}[$mapID] = $songID;
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
    WHERE live_difficulty_id NOT IN (SELECT id FROM c.live_difficulty WHERE hide IS NOT NULL)
';
$dbNotes = DB::lt_query('jp/masterdata.db', $sql);
while ($dbNote = $dbNotes->fetchArray(SQLITE3_ASSOC)) {
    $mapID = $dbNote['live_difficulty_id'];
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
        foreach (Constants::EIS_SONG_DIF_NOTEWORTHY as $d) {
            if (!array_key_exists($mapID, ${"mMap$d"})) continue;
            $songID = ${"mMap$d"}[$mapID];
            $tTags = &$clientSongs[$songID][1][$d][5];
            if ($dbNote['finish1'] == 8) {
                if ($rEffects[$dbNote['type1']][0]) {
                    Util::arrayPushUnique($tTags, constant(__NAMESPACE__.'\Constants::EIS_SONG_TAG_3_STRATEGY_NOSWITCH'));
                } else {
                    Util::arrayPushUnique($tTags, constant(__NAMESPACE__.'\Constants::EIS_SONG_TAG_3_STRATEGY_SWITCH'));
                }
            }
            unset($tTags);
        }
    }
}
$sql = 'SELECT t.live_difficulty_id,t.wave_id,state,skill_target_master_id1,
    effect1.effect_type AS type1,effect1.effect_value AS value1,effect1.finish_type AS finish1,effect1.finish_value AS finishv1,
    kn.message AS k_name,kzn.message AS kz_name,kd.message AS k_desc,kzd.message AS kz_desc
    FROM (SELECT * FROM m_live_note_wave_gimmick_group UNION SELECT * FROM r.m_live_note_wave_gimmick_group) AS t
    LEFT JOIN m_skill ON skill_id=m_skill.id
    LEFT JOIN m_skill_effect AS effect1 ON skill_effect_master_id1=effect1.id
    LEFT JOIN (SELECT * FROM k.m_dictionary UNION SELECT * FROM r.m_dictionary_k) AS kn ON substr(name,3)=kn.id
    LEFT JOIN kz.m_dictionary AS kzn ON substr(name,3)=kzn.id
    LEFT JOIN (SELECT * FROM k.m_dictionary UNION SELECT * FROM r.m_dictionary_k) AS kd ON substr(description,3)=kd.id
    LEFT JOIN kz.m_dictionary AS kzd ON substr(description,3)=kzd.id
    WHERE live_difficulty_id NOT IN (SELECT id FROM c.live_difficulty WHERE hide IS NOT NULL)
';
$dbWaves = DB::lt_query('jp/masterdata.db', $sql);
while ($dbWave = $dbWaves->fetchArray(SQLITE3_ASSOC)) {
    $mapID = $dbWave['live_difficulty_id'];
    $ref = $refMaps[$dbWave['live_difficulty_id']];
    $tExtract = SIFAS::extractWaveMission($dbWave['k_name']);
    $extendData = $extendDataManager->get($mapID)?->waves[$dbWave['wave_id'] - 1] ?? [];
    $detailSongs[$ref[0]]['maps'][$ref[1]][$ref[2]][25][] = [
        $dictStrings[$ref[0]]->set($dbWave['k_name']),
        $dbWave['state'],
        $dictStrings[$ref[0]]->set($dbWave['k_desc']),
        $dbWave['skill_target_master_id1'],
        $extendData['start'] ?? 0,
        $extendData['finish'] ?? 0,
        $extendData['voltage'] ?? 0,
        $extendData['damage'] ?? 0,
        $dbWave['type1'],
        $dbWave['value1'],
        $dbWave['finish1'],
        $dbWave['finishv1'],
        $tExtract[0],
        $tExtract[1],
    ];
    foreach (Constants::EIS_SONG_DIF_NOTEWORTHY as $d) {
        if (!array_key_exists($mapID, ${"mMap$d"})) continue;
        $songID = ${"mMap$d"}[$mapID];
        $tTags = &$clientSongs[$songID][1][$d][5];
        if (SIFAS::isWaveMissionSkill($dbWave['k_name'])) {
            Util::arrayPushUnique($tTags, constant(__NAMESPACE__.'\Constants::EIS_SONG_TAG_3_AC_SKILL'));
        }
        if (SIFAS::isWaveMissionCritical($dbWave['k_name'])) {
            Util::arrayPushUnique($tTags, constant(__NAMESPACE__.'\Constants::EIS_SONG_TAG_3_AC_CRITICAL'));
        }
        unset($tTags);
    }
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

$sql = "SELECT * FROM live_song_flag WHERE time_till_utc8 IS NULL OR time_till_utc8>NOW()";
$col = [['i','song'],['i','type'],['i','server'],['s','time_show'],['s','time_till']];
$sFlags = DB::mySelect($sql, $col, 'id');

Cache::writeMultiJson('live-detail.js', [
    'songs' => $clientSongs,
    'songGroups' => $clientSongGroups,
    'songTags' => $cSongTags,
    'effects' => $clientEffects,
    'targets' => $clientTargets,
    'targets83' => $cTargets,
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
Cache::writePhp('live-detail.php', [
    'cacheSongFlags' => $sFlags,
]);
