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
<?php
if ($trackEnabled) {
    echo '<script>var _paq=window._paq||[];';
    echo '_paq.push(["trackPageView"]),_paq.push(["enableLinkTracking"]),function(){var e="//' . $config['matomo_host'] . '/matomo/";_paq.push(["setTrackerUrl",e+"matomo.php"]),_paq.push(["setSiteId","1"]);var a=document,t=a.createElement("script"),r=a.getElementsByTagName("script")[0];t.type="text/javascript",t.async=!0,t.defer=!0,t.src=e+"matomo.js",r.parentNode.insertBefore(t,r)}();</script>' . "\n";
}
?>
</body>
</html>
