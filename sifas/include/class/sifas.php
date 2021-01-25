<?php
namespace EIS\Lab\SIFAS;
if (!defined('EIS_ENV'))
    exit;

class SIFAS {
    const SERVER_TIMEZONE_JP = '+0900';
    const SERVER_TIMEZONE_WW = '+0900'; // Different from SIF
    static function getServerTimezone($server) {
        return [null, self::SERVER_TIMEZONE_JP, self::SERVER_TIMEZONE_WW][$server];
    }

    static function itemCollectionAppend(&$collection, $type, $key, $amount) {
        switch ($type) {
            case 1: // Loveca Star
            case 4: // Card EXP
            case 10: // Gold
                $collection[$type] = $amount;
                break;
            case 7: // Suit
            case 8: // Voice
            case 15: // Emblem
            case 19: // Side story
            case 20: // Member story
            case 26: // Background
                $collection[$type] = $collection[$type] ?? [];
                $collection[$type][] = $key;
                break;
            default:
                $collection[$type][$key] = $amount;
                break;
        }
    }
}
