<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;

class HTMLBase {
    protected static function _css($root, $prefix, $filename) {
        $link = $prefix . '/' . $filename;
        $path = $root . '/' . $filename;
        if (file_exists($file = $path . '.min.css')) {
            $link .= '.min.css?s=' . substr(sha1_file($file), 0, 7);
        } else if (file_exists($file = $path . '.css')) {
            $link .= '.css?s=' . substr(sha1_file($file), 0, 7);
        }
        $code = '<link rel="stylesheet" type="text/css" href="' . $link . '"/>';
        return $code . "\n";
    }
    protected static function _js($root, $prefix, $filename) {
        $link = $prefix . '/' . $filename;
        $path = $root . '/' . $filename;
        if (file_exists($file = $path . '.min.js')) {
            $link .= '.min.js?s=' . substr(sha1_file($file), 0, 7);
        } else if (file_exists($file = $path . '.js')) {
            $link .= '.js?s=' . substr(sha1_file($file), 0, 7);
        }
        $code = '<script src="' . $link . '"></script>';
        return $code . "\n";
    }
    private static function getResource(int $resourceId): array {
        global $config;
        $index = $config['resource_index_override'][$resourceId] ?? $config['resource_index_default'];
        $resource = RESOURCES[$resourceId][$index];
        return [
            (isset($resource[0]) ? $config['resource_hosts'][$resource[0]] : '') . $resource[1],
            !empty($resource[2]) ? ' integrity="'.$resource[2].'" crossorigin="anonymous"' : '',
        ];
    }
    static function resourceCSS(int $resourceId): string {
        $resource = self::getResource($resourceId);
        return '<link rel="stylesheet" type="text/css" href="'.$resource[0].'"'.$resource[1].'/>'."\n";
    }
    static function resourceJS(int $resourceId): string {
        $resource = self::getResource($resourceId);
        return '<script src="'.$resource[0].'"'.$resource[1].'></script>'."\n";
    }

    static function dict($dictName, $vocName, $tagName = 'span', $attr = ''): string {
        return '<'.$tagName.(empty($attr)?'':' '.$attr).' class="eis-sif-dict" data-dict="'.$dictName.'" data-voc="'.$vocName.'"></'.$tagName.'>';
    }

    static function paragraphs($text) {
        return implode("\n", array_map(function($p){return '<p>'.$p.'</p>';}, explode('\\n', $text)));
    }
}

class HTML extends HTMLBase {
    static function css($filename, $folder = 'css') {
        return self::_css(ROOT_SIF_RES . '/' . $folder, '/sif/res/' . $folder, $filename);
    }
    static function js($filename, $folder = 'js') {
        return self::_js(ROOT_SIF_RES . '/' . $folder, '/sif/res/' . $folder, $filename);
    }
    static function json($varname, $array) {
        $json = json_encode($array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return (empty($varname) ? $json : 'var ' . $varname . '=' . $json . ";\n");
    }
    static function json2(string $json, string $name): string {
        return 'var '.$name.'='.$json.";\n";
    }

    const SERVER_ICON_JP = '<span class="fa-stack"><i class="fas fa-circle fa-stack-2x" style="color:#fff"></i><i class="fas fa-circle fa-stack-1x" style="color:#f00"></i></span>';
    const SERVER_ICON_WW = '<span class="fa-stack"><i class="fas fa-globe-asia fa-stack-2x" style="color:#098"></i></span>';
    const SERVER_ICON_CN = '<span style="font-size:6px"><span class="fa-stack"><i class="fas fa-circle fa-stack-2x" style="color:#f00"></i><i class="fas fa-star fa-stack-1x" data-fa-transform="left-5" style="color:#ff0"></i><i class="fas fa-star fa-stack-1x" data-fa-transform="shrink-8 up-9 right-6" style="color:#ff0"></i><i class="fas fa-star fa-stack-1x" data-fa-transform="shrink-8 up-3 right-8" style="color:#ff0"></i><i class="fas fa-star fa-stack-1x" data-fa-transform="shrink-8 down-3 right-8" style="color:#ff0"></i><i class="fas fa-star fa-stack-1x" data-fa-transform="shrink-8 down-9 right-6" style="color:#ff0"></i></span></span>';
    static function serverIcon($server) {
        return [null, self::SERVER_ICON_JP, self::SERVER_ICON_WW, self::SERVER_ICON_CN][$server];
    }

    public static function printBanners(int $location): void {
        echo '<div class="eis-sif-banner-container">', "\n";
        foreach (Basic::getBanners($location) as $banner) {
            switch ($banner[1]) {
                case 0:
                    echo '<img class="eis-sif-banner" src="/sif/res/img/u/banner/', $banner[0], '"/>', "\n";
                    break;
                case 1:
                    echo '<a href="', Basic::getPageURL($banner[2]), '" target="_blank"><img class="eis-sif-banner" src="/sif/res/img/u/banner/', $banner[0], '"/></a>', "\n";
                    break;
                case 2:
                    echo '<img class="eis-sif-banner" src="/sif/res/img/u/banner/', $banner[0], '" onclick="readNotice(', $banner[2], ');refreshNotices()"/>', "\n";
                    break;
            }
        }
        echo "</div>\n";
    }
}
