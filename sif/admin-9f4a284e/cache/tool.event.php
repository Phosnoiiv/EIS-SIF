<?php
namespace EIS\Lab\SIF;
require_once __DIR__.'/../../core/init.php';

$cCheerValues = Util::readConfig('cache', 'event_cheer_values', isJson:true);

Cache::writeMultiJson('tool-event.js', [
    'yellChoices' => $cCheerValues,
]);
