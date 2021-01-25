<?php
if (!defined('EIS_ENV'))
    exit;
?>
<div class="eis-sif-hidden">
<div id="dialog-gallery" title="下载<?=$galleryNameSuffix?>" data-width=500>
<div class="eis-sif-dialog-head">
<span id="dialog-gallery-title" class="eis-sif-dialog-title"></span>
</div>
<img id="dialog-gallery-thumb"/>
<div id="dialog-gallery-panel">
<div id="dialog-gallery-limit"><i class="fas fa-bolt"></i> <span class="limit-capacity-name" data-limit=<?=$limitType?>></span>：<span class="limit-capacity-amount" data-limit=<?=$limitType?>></span></div>
<div id="dialog-gallery-free" class="eis-sif-notice">
<span id="dialog-gallery-flag">剩余 <span class="eis-sif-countdown" data-time=0 data-countdown-short=1></span></span>
<p><i class="fas fa-charging-station"></i> 现在下载此<?=$galleryNameSuffix?>不消耗次数。</p>
</div>
<p class="eis-sif-button-line"><a id="dialog-gallery-link" class="eis-jq-button dialog-gallery-button" target="_blank" onclick="galleryDownloaded()"><i class="fas fa-download"></i> 下载此<?=$galleryNameSuffix?></a></p>
</div>
<div id="dialog-gallery-notes"></div>
</div>
</div>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/foot.php';
