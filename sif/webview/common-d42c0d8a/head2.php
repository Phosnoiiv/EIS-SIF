<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;

foreach (Basic::getAvailableMods() as $mod) {
    echo HTML::css('mods/' . $mod);
    echo HTML::js('mods/' . $mod);
}
?>
</head>
<body data-id="<?=$pageID ?? ''?>"<?=V2::$useV2Front ? ' class="v2-body"' : ''?>>
<?php
V2::includeV2FrontJs();
?>
<div id="eis-sif-header">
<span id="eis-sif-name">EIS-SIF</span>
<?php
if (empty($isHome) && !Basic::inMaintenance()) {
    echo '<a id="eis-sif-nav-home" href="/sif/">返回主页</a>' . "\n";
}
if (isset($relatedPage)) {
    echo '<a id="eis-sif-nav-related" href="/sifas/' . $relatedPage['href'] . '">' . $relatedPage['name'] . '</a>';
}
?>
<div id="eis-sif-header-buttons">
<span class="eis-sif-header-button" onclick="openSettings()" title="设置"><i class="fas fa-cog"></i></span>
</div>
</div>
<div id="eis-sif-container">
<?php
if (empty($hideTitle)) {
    echo '<h1>' . ($title ?? $pages[$pageID]['title']) . "</h1>\n";
}
foreach ($instantPageNotices as $notice) {
    echo '<div class="eis-sif-notice"><p><i class="fas fa-exclamation-circle"></i> ' . $notice . "</p></div>\n";
}
$barContents = [];
if (!empty($migV2UnitFullIds)) {
    $barContents[] = '<i class="fas fa-cube"></i> 当前数据包 '.V2::getDataBundleName();
    $barContents[] = '<i class="fas fa-clock"></i> 本页面数据更新于 ' . date('Y/m/d H:i', max(array_map(fn($x)=>V2::getDataTime($x),$migV2UnitFullIds)));
}
if (isset($latestFile)) {
    $barContents[] = '<i class="fas fa-clock"></i> 本页面数据更新于 ' . date('Y/m/d H:i', filemtime($latestFile));
}
if (!empty($barContentsAppend)) {
    $barContents = array_merge($barContents, $barContentsAppend);
}
if (isset($limitType)) {
    $barContents[] = '<i class="fas fa-bolt"></i> <span id="limit-capacity-name-' . $limitType . '">' . Limit::$CapacityNames[$limitType] . '</span>：<span class="limit-capacity-amount" data-limit=' . $limitType . '></span>';
}
if (!empty($helpArticle)) {
    $barContents[] = '<i class="fas fa-question-circle"></i> <a href="/sif/article/?' . $helpArticle . '" target="_blank">本页面帮助</a>';
}
if (!empty($barContents)) {
    echo '<div class="eis-sif-bar">';
    foreach ($barContents as $barContent) {
        echo '<span>' . $barContent . '</span>';
    }
    echo "</div>\n";
}
