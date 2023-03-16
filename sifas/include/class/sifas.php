<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF\Util;
if (!defined('EIS_ENV'))
    exit;

class SIFAS {
    const SERVER_TIMEZONE_JP = '+0900';
    const SERVER_TIMEZONE_WW = '+0900'; // Different from SIF
    static function getServerTimezone($server) {
        return [null, self::SERVER_TIMEZONE_JP, self::SERVER_TIMEZONE_WW][$server];
    }

    const MAP_DIF_SHORT_CODE = array(10=>1, 20=>2, 30=>3, 35=>4, 37=>5);

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

    static function extractWaveMission(string $text): array {
        if (preg_match('/^合計([\d,]+)ボルテージを獲得する$/', $text, $matches)) {$type=1;$value=$matches[1];}
        else if (preg_match('/^NICE以上の判定を(\d+)回出す$/', $text, $matches)) {$type=2;$value=$matches[1];}
        else if (preg_match('/^GREAT以上の判定を(\d+)回出す$/', $text, $matches)) {$type=3;$value=$matches[1];}
        else if (preg_match('/^1回で([\d,]+)ボルテージを獲得する$/', $text, $matches)) {$type=5;$value=$matches[1];}
        else if (preg_match('/^SP特技で合計([\d,]+)ボルテージを獲得する$/', $text, $matches)) {$type=6;$value=$matches[1];}
        else if (preg_match('/^(\d+)人のスクールアイドルでアピールする$/', $text, $matches)) {$type=7;$value=$matches[1];}
        else if (preg_match('/^クリティカル判定を([\d,]+)回出す$/', $text, $matches)) {$type=8;$value=$matches[1];}
        else if (preg_match('/^特技を([\d,]+)回発動する$/', $text, $matches)) {$type=9;$value=$matches[1];}
        else if (preg_match('/^スタミナを([\d,]+)%以上維持する$/', $text, $matches)) {$type=16;$value=$matches[1];}
        return [$type, Util::removeIntComma($value)];
    }
    static function isWaveMissionSkill(string $text): bool {
        if (strpos($text, '発動') !== false) return true;
        return false;
    }
    static function isWaveMissionCritical(string $text): bool {
        if (strpos($text, 'クリティカル') !== false) return true;
        return false;
    }
}
