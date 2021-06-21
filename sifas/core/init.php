<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;

if (!defined('EIS_ENV'))
    define('EIS_ENV', 'SIFAS');
if (!defined('CONFIG_SHEET_SIFAS'))
    define('CONFIG_SHEET_SIFAS', 'sifas');
if (!defined('ROOT_SIFAS_CORE'))
    define('ROOT_SIFAS_CORE', __DIR__);
if (!defined('ROOT_SIFAS_SRC'))
    define('ROOT_SIFAS_SRC', dirname(ROOT_SIFAS_CORE));
if (!defined('ROOT_SIFAS_INC'))
    define('ROOT_SIFAS_INC', ROOT_SIFAS_SRC . '/include');
if (!defined('ROOT_SIFAS_RES'))
    define('ROOT_SIFAS_RES', ROOT_SIFAS_SRC . '/res');
if (!defined('ROOT_SIFAS_WEB'))
    define('ROOT_SIFAS_WEB', ROOT_SIFAS_SRC . '/webview');
if (!defined('ROOT_SIFAS_VIO'))
    define('ROOT_SIFAS_VIO', dirname(dirname(ROOT_SIFAS_SRC)) . '/vio/sifas');
if (!defined('ROOT_SIFAS_CACHE'))
    define('ROOT_SIFAS_CACHE', dirname(dirname(ROOT_SIFAS_SRC)) . '/cache/sifas');
if (!defined('ROOT_SIFAS_ASSET'))
    define('ROOT_SIFAS_ASSET', dirname(dirname(ROOT_SIFAS_SRC)) . '/asset/sifas');

if (!defined('ROOT_SIFAS_CONFIG')) {
    $dir = dir(ROOT_SIFAS_SRC);
    while (($name = $dir->read()) !== false) {
        if (strncmp($name, 'config-', 7) != 0)
            continue;
        define('ROOT_SIFAS_CONFIG', ROOT_SIFAS_SRC . '/' . $name);
        break;
    }
    $dir->close();
}

require_once dirname(ROOT_SIFAS_SRC) . '/sif/core/init.php';

spl_autoload_register(function($name) {
    SIF\_autoload(__NAMESPACE__, ROOT_SIFAS_INC, $name);
});

require_once ROOT_SIFAS_CONFIG . '/config.php';

const DB_EIS_MAIN = 'eis.s3db';
const DB_GAME_JP_MASTER = 'jp/masterdata.db';
