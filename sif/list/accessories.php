<?php
namespace EIS\Lab\SIF;
$pageID = 29;
require_once dirname(__DIR__).'/core/init.php';

$useSIFStyle = true;
$latestFile = ROOT_SIF_CACHE.'/accessories.js';

require ROOT_SIF_WEB.'/common-d42c0d8a/head1.php';
echo HTML::css('accessories');
echo HTML::js('accessories');
?>
<script>
<?=Cache::read('accessories.js')?>
</script>
<?php
require ROOT_SIF_WEB.'/common-d42c0d8a/head2.php';
?>
<div id="g-main" data-gallery-config=2901>
<div class="eis-sif-bar">
<span>类别：<span class="eis-sif-button-group tiny" data-click="changeGalleryFilter('#g-main',1,$)" data-gallery-role=filter data-gallery-filter=1>
    <span data-click-arg=1>专用饰品</span>
    <span data-click-arg=0>通常饰品</span>
</span></span>
<span>视图：<span class="eis-sif-button-group tiny" data-click="changeGalleryView('#g-main',$)" data-gallery-role=views>
    <span data-click-arg=1><i class="fas fa-th"></i></span>
    <span data-click-arg=2><i class="fas fa-th-large"></i></span>
    <span data-click-arg=3><i class="fas fa-th-list"></i></span>
</span></span>
<span>数据：<span class="eis-sif-button-group tiny" data-click="changeGalleryOption('#g-main',1,$)" data-gallery-role=option data-gallery-option=1>
    <span data-click-arg=1>Lv.1</span>
    <span data-click-arg=4>Lv.4</span>
    <span data-click-arg=8>Lv.8</span>
</span></span>
</div>
<div class="eis-sif-gallery" data-gallery-role=gallery></div>
</div>
<div class="eis-sif-hidden">
<div id="dialog-accessory" title="饰品详情" data-full=1>
<div id="dialog-accessory-title"></div>
<div id="accessory-levels"></div>
</div>
</div>
<?php
require ROOT_SIF_WEB.'/common-d42c0d8a/foot.php';