<?php
namespace EIS\Lab\SIF;

if (!defined('EIS_ENV'))
    define('EIS_ENV', 'SIF');
if (!defined('CONFIG_SHEET_SIF'))
    define('CONFIG_SHEET_SIF', 'sif');
if (!defined('ROOT_SIF_CORE'))
    define('ROOT_SIF_CORE', __DIR__);
if (!defined('ROOT_SIF_SRC'))
    define('ROOT_SIF_SRC', dirname(ROOT_SIF_CORE));
if (!defined('ROOT_SIF_INC'))
    define('ROOT_SIF_INC', ROOT_SIF_SRC . '/include');
if (!defined('ROOT_SIF_RES'))
    define('ROOT_SIF_RES', ROOT_SIF_SRC . '/res');
if (!defined('ROOT_SIF_WEB'))
    define('ROOT_SIF_WEB', ROOT_SIF_SRC . '/webview');
if (!defined('ROOT_SIF_VIO'))
    define('ROOT_SIF_VIO', dirname(dirname(ROOT_SIF_SRC)) . '/vio/sif');
if (!defined('ROOT_SIF_CACHE'))
    define('ROOT_SIF_CACHE', dirname(dirname(ROOT_SIF_SRC)) . '/cache/sif');
if (!defined('ROOT_SIF_ASSET'))
    define('ROOT_SIF_ASSET', dirname(dirname(ROOT_SIF_SRC)) . '/asset/sif');
if (!defined('ROOT_SIF_DOC'))
    define('ROOT_SIF_DOC', dirname(dirname(ROOT_SIF_SRC)) . '/doc/sif');

function defineRoot(string $define, string $target): void {
    $dir = dir(ROOT_SIF_SRC);
    while (($name = $dir->read()) !== false) {
        if (strncmp($name, $target.'-', strlen($target)+1) != 0)
            continue;
        define($define, ROOT_SIF_SRC . '/' . $name);
        break;
    }
    $dir->close();
}
defineRoot('ROOT_SIF_ADMIN', 'admin');
defineRoot('ROOT_SIF_CONFIG', 'config');

require __DIR__.'/../vendor/autoload.php';

require_once ROOT_SIF_CONFIG . '/resources.php';
require_once ROOT_SIF_CONFIG . '/config.php';
require_once ROOT_SIF_CONFIG . '/version.php';
require_once ROOT_SIF_CONFIG . '/pages.php';
require_once ROOT_SIF_CONFIG . '/datapacks.php';

const DB_EIS_MAIN = 'eis.s3db';
const DB_EIS_CACHED = 'cached.s3db';

if (empty($isMaintenancePage) && ($code = Basic::inMaintenance())) {
    header('Location: /sif/maintenance.php?c=' . $code, true, 302);
    exit();
}
if ($code = Basic::inLocalMaintenance($pageID ?? 0)) {
    header('Location: /sif/maintenance.php?c=' . $code, true, 302);
    exit();
}
$trackEnabled = empty($_COOKIE['sif_notrack']) && !isset($_GET['sif_notrack']);
if (isset($_GET['sif_notrack'])) {
    setcookie('sif_notrack', 1, 0, '/');
}
