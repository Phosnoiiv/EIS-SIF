<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;

class DB extends SIF\DBBase {
    use SIF\DBTrait;
    protected static function getConfigSheet() {
        return CONFIG_SHEET_SIFAS;
    }
}
