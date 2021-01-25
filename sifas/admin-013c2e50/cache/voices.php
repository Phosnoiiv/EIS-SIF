<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$clientMembers = [];
$sql = 'SELECT * FROM member WHERE sifas_member_id IS NOT NULL';
$dbMembers = SIF\DB::my_query($sql);
while ($dbMember = $dbMembers->fetch_assoc()) {
    $id = $dbMember['sifas_member_id'];
    $clientMembers[$id] = [
        intval($dbMember['sifas_category'] ?? $dbMember['category']),
        $dbMember['jp_name'],
        intval($dbMember['display_class']),
    ];
}

$clientCards = [];
$dictCards = new SIF\Dict(true);
$sql = 'SELECT * FROM card WHERE id IN (SELECT DISTINCT card FROM voice_navi)';
$dbCards = DB::my_query($sql);
while ($dbCard = $dbCards->fetch_assoc()) {
    $id = $dictCards->set($dbCard['id']);
    $clientCards[$id] = [
        intval($dbCard['card_no']),
        intval($dbCard['rarity']),
        intval($dbCard['attribute']),
        intval($dbCard['role']),
        $dbCard['display_name'] ?? '',
        $dbCard['jp_name1'],
    ];
}

$clientSuits = [];
$dictSuits = new SIF\Dict(true);
$sql = 'SELECT * FROM suit WHERE id IN (SELECT DISTINCT suit FROM voice_navi)';
$dbSuits = DB::my_query($sql);
while ($dbSuit = $dbSuits->fetch_assoc()) {
    $id = $dictSuits->set($dbSuit['id']);
    $clientSuits[$id] = [
        $dbSuit['jp_name'],
    ];
}

$clientCategories = [['',0],['',0]];
$dictCategories = new SIF\Dict();
$dictCategories->set('卡片特训解锁语音');
$dictCategories->set('服装限定语音');
$refCategories = [];
$sql = 'SELECT * FROM voice_navi_category';
$dbCategories = DB::my_query($sql);
while ($dbCategory = $dbCategories->fetch_assoc()) {
    $index = $dictCategories->set($dbCategory['display_name'] ?? '');
    $id = $dbCategory['id'];
    $refCategories[$id] = $index;
    $clientCategories[$index] = [
        $dbCategory['display_name'] ?? '',
        intval($dbCategory['display_order']),
        intval($dbCategory['highlight']),
    ];
}

$clientVoices = $clientMemberVoices = $clientCategoryVoices = [];
$dictVoices = new SIF\Dict(true);
$sql = 'SELECT * FROM voice_navi';
$dbVoices = DB::my_query($sql);
while ($dbVoice = $dbVoices->fetch_assoc()) {
    $originalID = $dbVoice['id'];
    $id = $dictVoices->set($originalID);
    $order = $dbVoice['order'];
    $member = intval($dbVoice['member']);
    $category = $originalID > 100000000 ? $refCategories[$originalID % 1000] : 0;
    $voice = [
        $dbVoice['jp_text'] ?? '',
        $dbVoice['en_text'] ?? '',
        $dbVoice['cn_text'] ?? '',
        $dbVoice['tw_text'] ?? '',
    ];
    if ($order == 1) {
        $clientMemberVoices[$member][$id] = [
            $category,
            $voice,
        ];
        $clientCategoryVoices[$category][$id] = [
            $member,
            $voice,
        ];
        if ($card = intval($dbVoice['card'])) {
            $clientMemberVoices[$member][$id][] = ['c' => $dictCards->set($card)];
        } else if ($suit = intval($dbVoice['suit'])) {
            $clientMemberVoices[$member][$id][] = ['s' => $dictSuits->set($suit)];
        }
    } else {
        if ($order == 2) {
            $clientMemberVoices[$member][$id][1] = [$clientMemberVoices[$member][$id][1]];
            $clientCategoryVoices[$category][$id][1] = [$clientCategoryVoices[$category][$id][1]];
        }
        $clientMemberVoices[$member][$id][1][] = $voice;
        $clientCategoryVoices[$category][$id][1][] = $voice;
    }
}

foreach (array_keys($clientMembers) as $member) {
    Cache::writeJson('voices/m' . $member . '.json', array_values($clientMemberVoices[$member]), true);
}
foreach ($clientCategories as $id => $category) {
    if (!$category[0])
        continue;
    Cache::writeJson('voices/c' . $id . '.json', array_values($clientCategoryVoices[$id]), true);
}

Cache::writeMultiJson('voices.js', [
    'members' => $clientMembers,
    'cards' => $clientCards,
    'suits' => $clientSuits,
    'categories' => $clientCategories,
]);
