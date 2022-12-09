<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;

class Basic {
    static function exit($message, $code = 500) {
        http_response_code($code);
        exit($message);
    }
    static function checkArg($arg, $accept) {
        if (!in_array($arg, $accept)) {
            self::exit('Argument error', 403);
        }
        return $arg;
    }

    static function getPageURL($pageID) {
        global $pages;
        $page = $pages[$pageID];
        return '/' . ['sif','sif','sifas'][$page['game']] . '/' . (empty($page['path']) ? '' : $page['path'] . '.php');
    }

    private static $maintenances, $inGlobalMaintenance = -1;
    private static function readMaintenances() {
        global $config;
        $codes = array_column($config['maintenances'], 'code');
        $sql = 'SELECT * FROM m_schedule WHERE code IN (' . implode(',', array_map(function($c){return "'".$c."'";}, $codes)) . ')';
        $columns = [['t','plan_from',3],['t','plan_till',3],['i','finished'],['s','title'],['s','intro'],['i','redirect_page'],['i','display_time']];
        self::$maintenances = DB::ltSelect('maintenance.s3db', $sql, $columns, 'code');
    }
    static function getMaintenance($code) {
        if (empty(self::$maintenances)) {
            self::readMaintenances();
        }
        return self::$maintenances[$code] ?? null;
    }
    private static function checkInGlobalMaintenance() {
        global $config;
        $time = time();
        foreach ($config['maintenances'] as $plan) {
            if (empty($plan['all'])) continue;
            $schedule = self::getMaintenance($plan['code']);
            if ($schedule[0] <= $time && ($schedule[1] > $time || !$schedule[2])) return $plan['code'];
        }
        return false;
    }
    static function inMaintenance() {
        if (!empty($_COOKIE['sif_maintenance'])) return false;
        if (self::$inGlobalMaintenance >= 0) return self::$inGlobalMaintenance;
        return self::$inGlobalMaintenance = self::checkInGlobalMaintenance();
    }
    static function inLocalMaintenance($pageID) {
        if (!empty($_COOKIE['sif_maintenance'])) return false;
        global $config;
        $time = time();
        foreach ($config['maintenances'] as $plan) {
            if (!in_array($pageID, $plan['pages'] ?? [])) continue;
            $schedule = self::getMaintenance($plan['code']);
            if ($schedule[0] <= $time && ($schedule[1] > $time || !$schedule[2])) return $plan['code'];
        }
        return false;
    }
    static function inAprilFools() {
        global $config;
        $time = time();
        return $time >= $config['aprilfools_start'] && $time <= $config['aprilfools_end'];
    }
    static function getAvailableMods() {
        global $config;
        return array_column(array_filter($config['mods'], function($mod) {
            $time = time();
            return $time >= $mod['start'] && $time <= $mod['end'];
        }), 'name');
    }

    private static $dynamic = null;
    private static function readDynamic() {
        if (isset(self::$dynamic)) return;
        $sql = "SELECT * FROM s_dynamic WHERE time_from<=datetime('now','localtime') AND (time_till IS NULL OR time_till>=datetime('now','localtime'))";
        self::$dynamic = DB::ltSelect('eis.s3db', $sql, [['s','value']], 'key', ['s'=>true]);
    }
    static function getAllDynamic() {
        self::readDynamic();
        return self::$dynamic;
    }

    private static $banners = [];
    static function getBanners($location) {
        if (isset(self::$banner[$location])) return self::$banner[$location];
        $sql = "SELECT * FROM s_banner WHERE `location`=$location AND time_open<=datetime('now','localtime') AND time_close>=datetime('now','localtime')";
        $columns = [['s','img'],['i','type'],['i','target']];
        self::$banners[$location] = DB::ltSelect('eis.s3db', $sql, $columns, '');
        return self::$banners[$location];
    }
}
