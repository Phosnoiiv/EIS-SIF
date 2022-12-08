<?php
namespace EIS\Lab\SIF;

$config['mysql_host'] = ini_get('mysqli.default_host');
$config['mysql_username'] = ini_get('mysqli.default_user');
$config['mysql_passwd'] = ini_get('mysqli.default_pw');
$config['mysql_port'] = ini_get('mysqli.default_port');
$config[CONFIG_SHEET_SIF]['mysql_dbname'] = '';

$config['sqlite_running_prefix'] = '';
$config[CONFIG_SHEET_SIF]['sqlite_dir'] = dirname(dirname(ROOT_SIF_SRC)) . '/db/sif';

$config['matomo_host'] = '';
$config['matomo_scheme'] = 'https';
$config['v2_host'] = '';
$config['resource_host_1'] = '';
$config['resource_hosts'] = [];
$config['resource_index_default'] = 0;
$config['resource_index_override'] = [];

$config['matomo_token'] = '';

$config['maintenances'] = [];
$config['pages'] = [];

$config['article_watermark_doc'] = false;

$config['aprilfools_start'] = 1585666800;
$config['aprilfools_end'] = 1585785599;
$config['mourning_cn_start'] = 1585929600;
$config['mourning_cn_end'] = 1586015999;
$config['mods'] = [];

$config['event_live_prev'] = [null, 0, 0, 0, 0, 0, 0];
$config['event_db_prev'] = [null, null, null, null, null, null, null];
$config['SIFAS_live_extend_suyooo'] = '';
