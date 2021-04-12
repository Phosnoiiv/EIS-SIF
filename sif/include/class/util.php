<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;

class Util {
    static function readMultiConfig($table, $key) {
        $sql = "SELECT `index`,`value` FROM c_$table WHERE `key`='$key'";
        return DB::ltSelect('eis.s3db', $sql, [['s','value']], 'index', ['s'=>true]);
    }
    static function readConfig($table, $key) {
        return self::readMultiConfig($table, $key)[''];
    }
}
