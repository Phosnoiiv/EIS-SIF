<?php
namespace EIS\Lab\SIFAS;
use EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;

foreach (SIF\Basic::getAvailableMods() as $mod) {
    echo SIF\HTML::css('mods/' . $mod);
    echo SIF\HTML::js('mods/' . $mod);
}
?>
</head>
<body class="eis-sifas" data-id="<?=$pageID ?? ''?>">
<div id="eis-sif-header">
<span id="eis-sif-name">EIS-SIF<small>(AS)</small></span>
<?php
if (!($isHome ?? false)) {
    echo '<a id="eis-sif-nav-home" href="/sif/">返回主页</a>' . "\n";
}
if (isset($relatedPage)) {
    echo '<a id="eis-sif-nav-related" href="/sif/' . $relatedPage['href'] . '">' . $relatedPage['name'] . '</a>';
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
if (isset($latestFile)) {
    $barContents[] = '<i class="fas fa-clock"></i> 本页面数据更新于 ' . date('m/d H:i', filemtime($latestFile));
}
if (!empty($barContentsAppend)) {
    $barContents = array_merge($barContents, $barContentsAppend);
}
if (!empty($limitType)) {
    $barContents[] = '<i class="fas fa-bolt"></i> <span id="limit-capacity-name-' . $limitType . '">' . SIF\Limit::$CapacityNames[$limitType] . '</span>：<span class="limit-capacity-amount" data-limit=' . $limitType . '></span>';
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
