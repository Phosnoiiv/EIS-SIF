<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV')) exit;
?>
</div>
<div id="fullscreen-footer">
<p>EIS-SIF 版本 <?=$config['version']?> Page by 2021 RS/EIS/PNI.</p>
</div>
<div id="eis-sif-loading" class="eis-sif-hidden">
<div class="ui-widget-overlay"></div>
<div id="eis-sif-loading-tip"><span>NOW LOADING...</span></div>
</div>
<div class="eis-sif-hidden">
<div id="settings-dialog" title="设置" data-width=700 data-full=1>
<div id="settings-tabs" class="eis-jq-tabs" data-scroll=1>
<ul>
<li id="settings-card-page"><a href="#settings-page">本页面设置</a></li>
<li id="settings-card-global"><a href="#settings-global">全站设置</a></li>
</ul>
<div id="settings-page">
<div id="settings-list-page"></div>
</div>
<div id="settings-global">
<div id="settings-list-global"></div>
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
