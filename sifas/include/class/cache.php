<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;

class Cache extends SIF\CacheBase {
    protected static function getCacheRoot() {
        return ROOT_SIFAS_CACHE;
    }
    static function read($filename) {
        return self::_read(ROOT_SIFAS_CACHE, $filename);
    }
    static function writeMultiJson($filename, $arrays) {
        self::_writeMultiJson(ROOT_SIFAS_CACHE, $filename, $arrays);
    }
    static function writePhp($filename, $arrays) {
        self::_writePhp(ROOT_SIFAS_CACHE, $filename, $arrays);
    }
}
