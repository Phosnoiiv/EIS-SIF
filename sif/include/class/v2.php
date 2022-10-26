<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV')) exit;

class V2 {
    static bool $isDevServer = false;
    static bool $isDevBundle = false;
    static array $dataPacks;

    static function load(): void {
        require ROOT_SIF_CACHE.'/v2/server.php';
        if ($v2NextRefresh<=time()) require ROOT_SIF_ADMIN.'/v2/cache/server.php';
        self::$dataBundles = $v2DataBundles;
        self::$dataBundleName = $v2DataBundleName;
        self::$dataBundlePatchName = $v2DataBundlePatchName;
        require ROOT_SIF_CACHE.'/v2/data.php';
        self::$dataFiles = $v2DataFiles;
    }
    static private array $dataBundles;
    static string $dataBundleName;
    static string $dataBundlePatchName;
    static function getDataBundleName(): string {
        return self::$dataBundleName.(!empty(self::$dataBundlePatchName)?'-'.self::$dataBundlePatchName:'');
    }
    static private array $dataFiles;
    static private function findData(int $unitFullId): array {
        $packId = floor($unitFullId/100);
        $pack = self::$dataPacks[$packId];
        $unitId = $unitFullId%100;
        $unit = $pack['l'][$unitId];
        $unitFile = $unit['f'];
        if (!isset(self::$dataFiles[$unitFullId])) {
            foreach (self::$dataBundles as $bundleId => $bundle) {
                if (is_file(ROOT_SIF_CACHE.'/v2/data/'.$bundle[2].'/'.$unitFile.'.json')) {
                    self::$dataFiles[$unitFullId] = $bundleId;
                    break;
                }
            }
            Cache::writePhp('v2/data.php', [
                'v2DataFiles' => self::$dataFiles,
            ]);
        }
        $bundle = self::$dataBundles[self::$dataFiles[$unitFullId]];
        return [
            'bundle' => $bundle,
            'unitFile' => $unitFile,
        ];
    }
    static function getData(int $unitFullId): string {
        $find = self::findData($unitFullId);
        return file_get_contents(ROOT_SIF_CACHE.'/v2/data/'.$find['bundle'][2].'/'.$find['unitFile'].'.json');
    }
    static function getDataTime(int $unitFullId): int {
        return self::findData($unitFullId)['bundle'][3];
    }

    static bool $useV2Front = false;
    static function includeV2FrontCss(): void {
        if (!self::$useV2Front) return;
        echo HTML::css('v2.4154467');
    }
    static function includeV2FrontJs(): void {
        if (!self::$useV2Front) return;
        echo HTML::js(self::$isDevBundle ? 'v2.dev' : 'v2.4154467');
    }
}
