<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;

class Util {
    static function readMultiConfig(string $table, string $key, bool $isJson = false) {
        $sql = "SELECT `index`,`value` FROM c_$table WHERE `key`='$key'";
        $configs = DB::ltSelect(DB_EIS_MAIN, $sql, [['s','value']], 'index', ['s'=>true]);
        if ($isJson) {
            array_walk($configs, fn(&$a)=>$a=json_decode($a));
        }
        return $configs;
    }
    static function readConfig(string $table, string $key, bool $isJson = false) {
        return self::readMultiConfig($table, $key, $isJson)[''];
    }

    static function arrayPushUnique(array &$array, ...$values): void {
        foreach ($values as $value) {
            if (in_array($value, $array)) continue;
            $array[] = $value;
        }
    }

    static function toJSON(array $array): string {
        return json_encode($array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
    static function removeIntComma(string $num): int {
        return intval(str_replace(',', '', $num));
    }
}
