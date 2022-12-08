<?php
namespace EIS\Lab\SIF;
$pageID = 5;
require_once dirname(__DIR__) . '/core/init.php';
require_once ROOT_SIF_CACHE . '/articles.php';

$id = is_numeric($_SERVER['QUERY_STRING']) ? intval($_SERVER['QUERY_STRING']) : 0;
if (!array_key_exists($id, $cacheArticles) || $id != $_SERVER['QUERY_STRING']) {
    require ROOT_SIF_WEB . '/common-4fcb29e1/404.php';
    exit;
}
$file = ROOT_SIF_CACHE . '/article/' . $id . '.html';
$article = $cacheArticles[$id];

$title = $article[0];
$barContentsAppend = [
    '<i class="fas fa-clock"></i> 本文发布于 ' . date('Y/m/d', $article[1]),
];
if ($article[2]) {
    $barContentsAppend[] = '<i class="fas fa-edit"></i> 更新于 ' . date('Y/m/d', $article[2]);
}
require ROOT_SIF_WEB . '/common-d42c0d8a/head1.php';
echo HTML::css('article');
require ROOT_SIF_WEB . '/common-d42c0d8a/head2.php';
if ($article[5] && $config['article_watermark_doc']) {
?>
<div class="eis-sif-fold">
<h5>【重要】关于文章水印</h5>
<?php
require ROOT_SIF_DOC . '/agreements/watermark.html';
?>
</div>
<?php
}
if ($article[5]) {
?>
<div class="watermark-wrapper">
<?php
}
require $file;
if ($article[5]) {
    echo "</div>\n";
}
if ($flow = $article[4]) {
?>
<section class="eis-sif-section-noborder">
<h4><i class="fas fa-comments fa-lg"></i> 留言板</h4>
<div class="eis-sif-flow" data-flow=<?=$flow?>></div>
</section>
<section class="eis-sif-section">
<h4><i class="fas fa-comment-dots"></i> 留言</h4>
<div class="eis-sif-fold">
<h5>留言须知</h5>
<div>
<?php
require ROOT_SIF_DOC . '/agreements/flow.html';
?>
</div>
</div>
<textarea id="comment" placeholder="可在此输入留言…"></textarea>
<p style="text-align:center"><span class="eis-jq-button" onclick="sendPost(<?=$flow?>,$('#comment').val())"><i class="far fa-comment"></i> 留言</span></p>
</section>
<?php
}
require ROOT_SIF_WEB . '/common-d42c0d8a/foot.php';
