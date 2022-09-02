<?php
namespace EIS\Lab\SIF;
$pageID = 31;
require_once __DIR__.'/../core/init.php';
require ROOT_SIF_WEB.'/common-d42c0d8a/head1.php';
require ROOT_SIF_WEB.'/common-d42c0d8a/head2.php';
?>
<iframe id="eis-sif-migrate-iframe" src="<?=$config['v2_host']?>sif/data/live-arena/"></iframe>
<?php
require ROOT_SIF_WEB.'/common-d42c0d8a/foot.php';
