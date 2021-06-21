<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;

class SIF {
    static $prefixServer = [null, 'jp', 'gl', 'cn'];
    static $prefixLanguage = [null, 'jp', 'en', 'zhs', 'zht'];

    static function getServerNameAShort($server) {
        return [null, 'JP', 'WW', 'CN'][$server];
    }

    const SERVER_TIMEZONE_JP = '+0900';
    const SERVER_TIMEZONE_WW = '+0000';
    const SERVER_TIMEZONE_CN = '+0800';
    static function getServerTimezone($server) {
        return [null, self::SERVER_TIMEZONE_JP, self::SERVER_TIMEZONE_WW, self::SERVER_TIMEZONE_CN][$server];
    }

    static function toTimestamp($time, $server) {
        return !empty($time) ? strtotime($time . self::getServerTimezone($server)) : 0;
    }
    static function toDatestamp($date, $base) {
        return !empty($date) ? strtotime($date . ' 0:00+0000') / 86400 - $base : 0;
    }

    const ROTATION_BEGIN_MASTER_RERUN_JP = 1552834800; // 2019/03/18 00:00+0900
    const ROTATION_COUNT_MASTER_RERUN_JP = 18;

    static function itemCollectionAppend(&$collection, $type, $key, $amount) {
        switch ($type) {
            case 3000: // G
            case 3001: // Loveca
            case 3002: // Friend pt
                $collection[$type] = $amount;
                break;
            case 5100: // Title
            case 5200: // Background
            case 5600: // Stamp
                $collection[$type] = $collection[$type] ?? [];
                $collection[$type][] = $key;
                break;
            default:
                $collection[$type][$key] = $amount;
                break;
        }
    }

    const GUARANTEE_1SR = 1;
    const GUARANTEE_1SSR = 2;
    const GUARANTEE_2SR = 3;
    const GUARANTEE_3SR = 4;
    const GUARANTEE_1UR = 5;
    static function scoutExpect(
        $count = 11,
        $guarantee = self::GUARANTEE_1SR,
        $rate_ur = 0.01,
        $rate_ssr = 0.04,
        $rate_sr = 0.15
    ) {
        $rate_ssr_above = $rate_ur + $rate_ssr;
        $rate_ssr_below = 1 - $rate_ur;
        $rate_sr_above = $rate_ur + $rate_ssr + $rate_sr;
        $rate_sr_below = 1 - $rate_ssr_above;
        $rate_r = 1 - $rate_sr_above;
        $rarities = ['ur', 'ssr', 'sr', 'r'];
        foreach ($rarities as $rarity) {
            $expect[$rarity] = $count * ${'rate_' . $rarity};
        }
        switch ($guarantee) {
            case self::GUARANTEE_1SR:
                $redo = pow($rate_r, $count);
                break;
            case self::GUARANTEE_2SR:
                $redo = pow($rate_r, $count) * 2
                    + pow($rate_r, $count - 1) * $rate_sr_above * $count;
                break;
            case self::GUARANTEE_3SR:
                $redo = pow($rate_r, $count) * 3
                    + pow($rate_r, $count - 1) * $rate_sr_above * $count * 2
                    + pow($rate_r, $count - 2) * pow($rate_sr_above, 2)
                        * $count * ($count - 1) / 2;
                break;
            case self::GUARANTEE_1SSR:
                $redo = pow($rate_sr_below, $count);
                break;
            case self::GUARANTEE_1UR:
                $redo = pow($rate_ssr_below, $count);
                break;
        }
        switch ($guarantee) {
            case self::GUARANTEE_1SR:
            case self::GUARANTEE_2SR:
            case self::GUARANTEE_3SR:
                $expect['ur'] += $redo * $rate_ur / $rate_sr_above;
                $expect['ssr'] += $redo * $rate_ssr / $rate_sr_above;
                $expect['sr'] += $redo * $rate_sr / $rate_sr_above;
                $expect['r'] -= $redo;
                break;
            case self::GUARANTEE_1SSR:
                $expect['ur'] += $redo * $rate_ur / $rate_ssr_above;
                $expect['ssr'] += $redo * $rate_ssr / $rate_ssr_above;
                $expect['sr'] -= $redo * $rate_sr / $rate_sr_below;
                $expect['r'] -= $redo * $rate_r / $rate_sr_below;
                break;
            case self::GUARANTEE_1UR:
                $expect['ur'] += $redo;
                $expect['ssr'] -= $redo * $rate_ssr / $rate_ssr_below;
                $expect['sr'] -= $redo * $rate_sr / $rate_ssr_below;
                $expect['r'] -= $redo * $rate_sr / $rate_ssr_below;
                break;
        }
        return $expect;
    }
    static function scoutDistribution(
        $count = 11, $guarantee = self::GUARANTEE_1SR,
        $rateUR = 0.01, $rateSSR = 0.04, $rateSR = 0.15,
        $rateSpecialUR = 0
    ) {
        $rateSSRAbove = $rateUR + $rateSSR;
        $rateSSRBelow = 1 - $rateUR;
        $rateSRAbove = $rateSSRAbove + $rateSR;
        $rateSRBelow = 1 - $rateSSRAbove;
        $rateR = 1 - $rateSRAbove;
        for ($i = 0, $c = 1; $i <= $count; $i++, $c = $c * ($count + 1 - $i) / $i) {
            $d1[$i] = pow($rateUR, $i) * pow(1 - $rateUR, $count - $i) * $c;
            $d2[$i] = pow($rateUR * $rateSpecialUR, $i) * pow(1 - $rateUR * $rateSpecialUR, $count - $i) * $c;
        }
        $chance = $pile = 0;
        switch ($guarantee) {
            case self::GUARANTEE_3SR:
                $chance += pow($rateR, $count - 2) * pow($rateSRAbove, 2) * $count * ($count - 1) / 2 * (++$pile);
                // No break
            case self::GUARANTEE_2SR:
                $chance += pow($rateR, $count - 1) * $rateSRAbove * $count * (++$pile);
                // No break
            case self::GUARANTEE_1SR:
                $chance += pow($rateR, $count) * (++$pile);
                $chance *= $rateUR / $rateSRAbove;
                break;
            case self::GUARANTEE_1SSR:
                $chance = pow($rateSRBelow, $count) * $rateUR / $rateSSRAbove;
                break;
            case self::GUARANTEE_1UR:
                $chance = pow($rateSSRBelow, $count);
                break;
        }
        $d1[0] -= $chance;
        $d1[1] += $chance;
        $d2[0] -= $chance * $rateSpecialUR;
        $d2[1] += $chance * $rateSpecialUR;
        return [$d1, $d2];
    }

    const VALUE_STRENGTH_UR_SCORE = 245.989;
    const VALUE_STRENGTH_UR_NORMAL = 81.9963;
    const VALUE_STRENGTH_SSR = 12.2087;
    const VALUE_STRENGTH_SR = 3.09764;
    const VALUE_STRENGTH_R = 0.206509;
    const VALUE_STRENGTH_COUPON = 6.19528;
    const VALUE_STRENGTH_EXP_SKILL = 0.0455535;
    static function scoutValueStrength(
        $expect,
        $count = 11,
        $ur_score = 1.0 / 3,
        $coupon = true
    ) {
        return $expect['ur'] * $ur_score * self::VALUE_STRENGTH_UR_SCORE
            + $expect['ur'] * (1 - $ur_score) * self::VALUE_STRENGTH_UR_NORMAL
            + $expect['ssr'] * self::VALUE_STRENGTH_SSR
            + $expect['sr'] * self::VALUE_STRENGTH_SR
            + $expect['r'] * self::VALUE_STRENGTH_R
            + ($coupon ? self::VALUE_STRENGTH_COUPON * $count / 10 : 0);
    }

    const VALUE_ADJUSTED_UR_SCORE = 204.724;
    const VALUE_ADJUSTED_UR_NORMAL = 68.2412;
    const VALUE_ADJUSTED_SSR = 5.49362;
    const VALUE_ADJUSTED_SR = 2.57800;
    const VALUE_ADJUSTED_R = 0.171867;
    const VALUE_ADJUSTED_COUPON = 5.15600;
    const VALUE_ADJUSTED_EXP_SKILL = 0.0379118;
    static function scoutValueAdjusted(
        $expect,
        $count = 11,
        $ur_score = 1.0 / 3,
        $coupon = true
    ) {
        return $expect['ur'] * $ur_score * self::VALUE_ADJUSTED_UR_SCORE
            + $expect['ur'] * (1 - $ur_score) * self::VALUE_ADJUSTED_UR_NORMAL
            + $expect['ssr'] * self::VALUE_ADJUSTED_SSR
            + $expect['sr'] * self::VALUE_ADJUSTED_SR
            + $expect['r'] * self::VALUE_ADJUSTED_R
            + ($coupon ? self::VALUE_ADJUSTED_COUPON * $count / 10 : 0);
    }
}
