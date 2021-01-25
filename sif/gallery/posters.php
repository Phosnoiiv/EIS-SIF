<?php
namespace EIS\Lab\SIF;
$pageID = 19;
require_once dirname(__DIR__) . '/core/init.php';

$limitType = Limit::TYPE_GALLERY;
$galleryCategories = [2];
$galleryNameSuffix = '画像';
$galleryJS = ['posters'];
$galleryCache = ['posters.js'];

require ROOT_SIF_WEB . '/common-d42c0d8a/gallery-head.php';
?>
<div class="eis-sif-content">
<p class="eis-sif-note">※ 本页面提供的招募画像已经过一定压缩处理。</p>
<div class="eis-sif-pagebar" data-control="#posters" data-size=24></div>
<div id="posters" class="eis-sif-gallery"></div>
<p class="eis-sif-note">※ 每页显示最多 24 张画像，翻页按钮在顶部。</p>
</div>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/gallery-foot.php';
