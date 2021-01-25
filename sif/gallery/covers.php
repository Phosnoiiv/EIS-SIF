<?php
namespace EIS\Lab\SIF;
$pageID = 16;
require_once dirname(__DIR__) . '/core/init.php';

$limitType = Limit::TYPE_GALLERY;
$galleryCategories = [1];
$galleryNameSuffix = '背景';
$galleryJS = ['covers'];
$galleryCache = ['covers.js'];

require ROOT_SIF_WEB . '/common-d42c0d8a/gallery-head.php';
?>
<div class="eis-sif-content">
<p class="eis-sif-note">※ 6.7 版本以后的封面背景比 6.6 版本以前的更宽一些，下列封面背景的尺寸不一致为正常现象。若同一背景在 6.7 版本更新前后都有使用的，本页面只收录宽版。</p>
<p class="eis-sif-note">※ 本页面提供的封面背景已经过一定压缩处理。</p>
<div class="eis-sif-pagebar" data-control="#covers" data-size=24></div>
<div id="covers" class="eis-sif-gallery"></div>
<p class="eis-sif-note">※ 每页显示最多 24 张背景，翻页按钮在顶部。</p>
</div>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/gallery-foot.php';
