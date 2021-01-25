<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;

class HTML extends SIF\HTMLBase {
    static function css($filename) {
        return self::_css(ROOT_SIFAS_RES . '/css', '/sifas/res/css', $filename);
    }
    static function js($filename) {
        return self::_js(ROOT_SIFAS_RES . '/js', '/sifas/res/js', $filename);
    }
}
