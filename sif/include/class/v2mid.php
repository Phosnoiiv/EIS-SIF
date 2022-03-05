<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV')) exit;

class V2Mid {
    private static int $bundleRelease = 0;
    static function setBundleRelease(int $time): void {
        self::$bundleRelease = $time;
    }
    static function getBundleReleaseStr(): string {
        return date('Y-m-d H:i:s', self::$bundleRelease);
    }
}
