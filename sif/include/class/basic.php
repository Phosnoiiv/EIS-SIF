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
        if (isset(self::$banners[$location])) return self::$banners[$location];
        $sql = "SELECT * FROM s_banner WHERE `location`=$location AND time_open<=datetime('now','localtime') AND time_close>=datetime('now','localtime')";
        $columns = [['s','img'],['i','type'],['i','target']];
        self::$banners[$location] = DB::ltSelect('eis.s3db', $sql, $columns, '');
        return self::$banners[$location];
    }

    private static ?array $style = null;
    private static function readStyle(): void {
        if (isset(self::$style)) return;
        $sql = "SELECT * FROM s_style_schedule WHERE ".DB::ltSQLTimeIn('time_open','time_close');
        $col = [['i','theme'],['s','home_subtitle']];
        $dStyles = DB::ltSelect(DB_EIS_MAIN, $sql, $col, '');
        self::$style = empty($dStyles) ? null : $dStyles[0];
    }
    private static function composeStyleThemeCss(array $colors): string {
        return implode('', array_map(fn($color, $shade) => "--eis-primary-$shade:$color;", $colors, [0,1,2,3,5,7,9]));
    }
    public static function getStyleThemeCss(): string {
        self::readStyle();
        if (empty(self::$style[0])) return '';
        $sql = "SELECT * FROM s_theme WHERE id=".self::$style[0];
        $col = [
            ['s','sif0'  ],['s','sif1'  ],['s','sif2'  ],['s','sif3'  ],['s','sif5'  ],['s','sif7'  ],['s','sif9'  ],
            ['s','sifas0'],['s','sifas1'],['s','sifas2'],['s','sifas3'],['s','sifas5'],['s','sifas7'],['s','sifas9'],
        ];
        $dTheme = DB::ltSelect(DB_EIS_MAIN, $sql, $col, '')[0];
        return (empty($dTheme[0]) ? '' : ':root,.eis-theme-sif{'.self::composeStyleThemeCss(array_slice($dTheme,0,7)).'}')
            .  (empty($dTheme[7]) ? '' : '.eis-theme-sifas{'    .self::composeStyleThemeCss(array_slice($dTheme,7,7)).'}');
    }
    public static function getStyleHomeSubtitle(): ?string {
        self::readStyle();
        return self::$style[1] ?? null;
    }
}
