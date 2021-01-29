<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;
?>
</div>
<div id="eis-sif-footer">
<p>EIS-SIF 版本 <?=$config['version']?> <?php if(!Basic::inMaintenance()) echo '<a href="/sif/history.php" target="_blank">查看更新记录</a>';?></p>
<p>Page by 2020 RS/EIS/PNI.</p>
</div>
<div id="eis-sif-loading" class="eis-sif-hidden">
<div class="ui-widget-overlay"></div>
<div id="eis-sif-loading-tip"><span>NOW LOADING...</span></div>
</div>
<div id="eis-sif-dialog-notice" class="eis-sif-hidden" title="告知" data-width=500>
<div class="eis-sif-dialog-head">
<span id="eis-sif-dialog-notice-title" class="eis-sif-dialog-title"></span>
</div>
<div class="eis-sif-dialog-info">
<p><i class="fas fa-calendar-day"></i> <span id="eis-sif-dialog-notice-date"></span></p>
</div>
<div id="eis-sif-dialog-notice-contents" class="eis-sif-notice-content"></div>
</div>
<div class="eis-sif-hidden">
<div id="settings-dialog" title="设置" data-width=700 data-full=1>
<div id="settings-tabs" class="eis-jq-tabs" data-scroll=1>
<ul>
<li id="settings-card-page"><a href="#settings-page">本页面设置</a></li>
</ul>
<div id="settings-page">
<div id="settings-list-page"></div>
</div>
</div>
</div>
</div>
<?php
if ($trackEnabled) {
    echo '<script>var _paq=window._paq||[];';
    echo '_paq.push(["trackPageView"]),_paq.push(["enableLinkTracking"]),function(){var e="//' . $config['matomo_host'] . '/matomo/";_paq.push(["setTrackerUrl",e+"matomo.php"]),_paq.push(["setSiteId","1"]);var a=document,t=a.createElement("script"),r=a.getElementsByTagName("script")[0];t.type="text/javascript",t.async=!0,t.defer=!0,t.src=e+"matomo.js",r.parentNode.insertBefore(t,r)}();</script>' . "\n";
}
?>
</body>
</html>
