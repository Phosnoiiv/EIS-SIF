<?php
namespace EIS\Lab\SIF;
if (!defined('EIS_ENV'))
    exit;
?>
</head>
<body>
<div id="eis-sif-header">
<h1><?=($title ?? $pages[$pageID]['title'])?></h1>
<?php
if (!($is_home ?? false)) {
    echo '<a id="eis-sif-nav-home" href="/sif/">返回主页</a>';
}
if (isset($related_sifas)) {
    echo '<a id="eis-sif-nav-sifas" href="/sifas/' . $related_sifas['href'] . '">' . $related_sifas['name'] . '</a>';
}
?>
</div>
<div id="eis-sif-container">
<?php
if (isset($latest_file)) {
    echo '<p>数据最后更新于 ' . date('Y/m/d H:i', filemtime($latest_file)) . '</p>' . "\n";
}
