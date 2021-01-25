<?php
if (!defined('EIS_ENV'))
    exit;
?>
</head>
<body>
<div id="eis-sif-header">
<h1><?=$title?></h1>
<?php
if (!($is_home ?? false)) {
    echo '<a class="eis-sif-nav" href="/sif/">返回主页</a>';
}
if (isset($related_sif)) {
    echo '<a class="eis-sif-nav" href="/sif/' . $related_sif['href'] . '">' . $related_sif['name'] . '</a>';
}
?>
</div>
<div id="eis-sif-container">
<?php
if (isset($latest_file)) {
    echo '<p>数据最后更新于 ' . date('Y/m/d H:i', filemtime($latest_file)) . '</p>' . "\n";
}
