<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
require_once dirname(dirname(__DIR__)) . '/core/init.php';

$clientEffects = [null];
$dbEffects = DB::my_query('SELECT * FROM word_effect');
while ($dbEffect = $dbEffects->fetch_assoc()) {
    $id = $dbEffect['type'];
    $clientEffects[$id] = [
        $dbEffect['jp_name'],
        $dbEffect['en_name'] ?? $dbEffect['en_name_append'],
        $dbEffect['cn_name'] ?? $dbEffect['cn_name_append'],
        $dbEffect['tw_name'] ?? $dbEffect['tw_name_append'],
        $dbEffect['jp_note'],
        $dbEffect['en_note'] ?? $dbEffect['en_note_append'],
        $dbEffect['cn_note'] ?? $dbEffect['cn_note_append'],
        $dbEffect['tw_note'] ?? $dbEffect['tw_note_append'],
        $dbEffect['jp_wave'],
        $dbEffect['en_wave'] ?? $dbEffect['en_wave_append'],
        $dbEffect['cn_wave'] ?? $dbEffect['cn_wave_append'],
        $dbEffect['tw_wave'] ?? $dbEffect['tw_wave_append'],
    ];
}
$dbEffects = DB::my_query('SELECT * FROM word_effect_extra');
while ($dbEffect = $dbEffects->fetch_assoc()) {
    $id = $dbEffect['type'];
    $category = $dbEffect['category'];
    $value = $dbEffect['value'];
    $clientEffectsExtra[$id][$value][$category-1] = [
        $dbEffect['jp_word'],
        $dbEffect['en_word'] ?? $dbEffect['en_append'],
        $dbEffect['cn_word'] ?? $dbEffect['cn_append'],
        $dbEffect['tw_word'] ?? $dbEffect['tw_append'],
    ];
}

$clientTargets = [null];
$dbTargets = DB::my_query('SELECT * FROM word_target');
while ($dbTarget = $dbTargets->fetch_assoc()) {
    $id = $dbTarget['id'];
    $clientTargets[$id] = [
        $dbTarget['jp_word'],
        $dbTarget['en_word'] ?? $dbTarget['en_append'],
        $dbTarget['cn_word'] ?? $dbTarget['cn_append'],
        $dbTarget['tw_word'] ?? $dbTarget['tw_append'],
    ];
}

$clientFinishes = [null];
$dbFinishes = DB::my_query('SELECT * FROM word_finish');
while ($dbFinish = $dbFinishes->fetch_assoc()) {
    $type = $dbFinish['type'];
    $state = $dbFinish['state'];
    $clientFinishes[$type][$state] = [
        $dbFinish['jp_word'],
        $dbFinish['en_word'] ?? $dbFinish['en_append'],
        $dbFinish['cn_word'] ?? $dbFinish['cn_append'],
        $dbFinish['tw_word'] ?? $dbFinish['tw_append'],
    ];
}

$clientNotes = [null];
$dbNotes = DB::my_query('SELECT * FROM word_note');
while ($dbNote = $dbNotes->fetch_assoc()) {
    $id = $dbNote['type'];
    $clientNotes[$id] = [
        $dbNote['jp_word'],
        $dbNote['en_word'] ?? $dbNote['en_append'],
        $dbNote['cn_word'] ?? $dbNote['cn_append'],
        $dbNote['tw_word'] ?? $dbNote['tw_append'],
    ];
}

Cache::writeMultiJson('words.js', [
    'wordEffects' => $clientEffects,
    'wordEffectsExtra' => $clientEffectsExtra,
    'wordTargets' => $clientTargets,
    'wordFinishes' => $clientFinishes,
    'wordNotes' => $clientNotes,
]);
