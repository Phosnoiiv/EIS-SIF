<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;

abstract class CacheBase {
    protected abstract static function getCacheRoot();

    protected static function _read($root, $filename) {
        return file_get_contents($root . '/' . $filename);
    }

    static function write($filename, $content) {
        file_put_contents(static::getCacheRoot() . '/' . $filename, $content);
    }
    static function writeJson($filename, $array, $onChange = false) {
        $json = json_encode($array, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $fileCache = static::getCacheRoot() . '/' . $filename;
        if ($onChange && file_exists($fileCache) && sha1_file($fileCache) == sha1($json))
            return;
        self::write($filename, $json);
    }
    protected static function _writeMultiJson($root, $filename, $arrays) {
        $handle = fopen($root . '/' . $filename, 'w');
        foreach ($arrays as $varname => $array) {
            fwrite($handle, 'var ' . $varname . '=' . json_encode(
                $array,
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ) . ";\n");
        }
        fclose($handle);
    }
    protected static function _writePhp($root, $filename, $arrays) {
        $handle = fopen($root . '/' . $filename, 'w');
        fwrite($handle, "<?php\n\n");
        foreach ($arrays as $varname => $array) {
            $export = var_export($array, true);
            fwrite($handle, '$' . $varname . '=' . $export . ";\n");
        }
        fclose($handle);
    }
}

class Cache extends CacheBase {
    protected static function getCacheRoot() {
        return ROOT_SIF_CACHE;
    }
    static function read($filename) {
        return self::_read(ROOT_SIF_CACHE, $filename);
    }
    static function readJson($file, $varname) {
        return 'var ' . $varname . '=' . self::read($file) . ";\n";
    }

    static function writeMultiJson($filename, $arrays) {
        self::_writeMultiJson(ROOT_SIF_CACHE, $filename, $arrays);
    }
    static function writePhp($filename, $arrays) {
        self::_writePhp(ROOT_SIF_CACHE, $filename, $arrays);
    }
}
