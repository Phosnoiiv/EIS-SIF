<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV')) exit;
?>
</head>
<body data-id="<?=$pageID?>">
<div id="eis-sif-header">
<span id="eis-sif-name">EIS-SIF</span>
<a id="eis-sif-nav-home" href="/sif/">返回主页</a>
<div id="eis-sif-header-buttons">
<span class="eis-sif-header-button" onclick="openSettings()" title="设置"><i class="fas fa-cog"></i></span>
</div>
</div>
<div id="eis-sif-container">
<?php
if (empty($hideTitle)) {
    echo '<h1>' . ($title ?? $pages[$pageID]['title']) . "</h1>\n";
}
