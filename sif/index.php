<?php
namespace EIS\Lab\SIF;
require_once __DIR__ . '/core/init.php';
if (!defined('ROOT_SIFAS_CACHE'))
    define('ROOT_SIFAS_CACHE', dirname(ROOT_SIF_CACHE) . '/sifas');
V2::load();

$master_rerun_group_jp = floor((time() - SIF::ROTATION_BEGIN_MASTER_RERUN_JP) / 604800) % SIF::ROTATION_COUNT_MASTER_RERUN_JP + 1;
$master_rerun_groups = [
    0 => [
        '本周' => $master_rerun_group_jp,
        '下周' => $master_rerun_group_jp % SIF::ROTATION_COUNT_MASTER_RERUN_JP + 1,
    ],
];
include ROOT_SIF_CACHE . '/reminders.php';
include ROOT_SIF_CACHE . '/master.rerun.php';
require_once ROOT_SIF_CONFIG . '/home.buttons.php';

function createButton($pageID, $servers = [], $note = '', $file = '', $options = []) {
    global $config, $pages;
    if (($available = $config['pages'][$pageID]['available'] ?? 0) > time()) {
        return '<a href="' . Basic::getPageURL($pageID) . '" target="_blank"><div class="title"><i class="fas fa-lock"></i> ???</div><div class="note">' . date('m/d H:i', $available) . ' 公开预定</div></a>' . "\n";
    }
    $page = $pages[$pageID];
    $isAS = $page['game'] == 2;
    $button = '<a href="' . Basic::getPageURL($pageID) . '" target="_blank">';
    if (!empty($file)) {
        if (is_array($file)) {
            $fileTime = max(array_map(fn($x)=>V2::getDataTime($x),$file));
        } else
        $fileTime = filemtime(($isAS ? ROOT_SIFAS_CACHE : ROOT_SIF_CACHE) . '/' . $file);
        $button .= '<div class="update ' . checkUpdateStatus($fileTime) . '"><span class="eis-sif-countup" data-time=' . $fileTime . '></span>前更新</div>';
    }
    $button .= '<div class="title">' . ($options['title'] ?? $page['title']) . '</div>';
    if (!empty($servers) || !empty($note)) {
        $button .= '<div class="note">';
        if (!empty($servers)) {
            $button .= '<span class="server">';
            foreach ($servers as $server) {
                $button .= HTML::serverIcon(($server - 1) % 3 + 1);
            }
            $button .= '</span>';
        }
        $button .= $note . '</div>';
    }
    $button .= "</a>\n";
    return $button;
}
function loadButtons(int $regionId): void {
    global $homeButtons, $homeButtonRegions;
    foreach ($homeButtonRegions[$regionId] as $buttonId) {
        $buttonConfig = $homeButtons[$buttonId];
        echo createButton(
            $buttonConfig['page'],
            $buttonConfig['servers'] ?? [],
            $buttonConfig['desc'] ?? '',
            $buttonConfig['cache'] ?? '',
            [
                'title' => $buttonConfig['name'] ?? null,
            ],
        );
    }
}
function checkUpdateStatus($updateTime) {
    $time = time();
    if ($updateTime > $time - 604800) return 'new';
    if ($updateTime < $time - 8640000) return 'old';
    return '';
}

$title = 'EIS-SIF';
$isHome = true;
require ROOT_SIF_WEB . '/common-d42c0d8a/head1.php';
echo HTML::css('home');
echo HTML::js('home');
?>
<script>
<?=HTML::json('reminders', array_reduce($cacheReminders, function($carry, $reminder) {
    if ($reminder[3] >= ($time = time()) && $reminder[4] <= time()) {
        $carry[] = array_slice($reminder, 0, 4);
    }
    return $carry;
}, []))?>
<?=Cache::read('articles.js')?>
<?=Cache::read('matomo/articles.js')?>
<?php
$sql = "SELECT * FROM s_banner_home WHERE ".DB::ltSQLTimeIn('time_open','time_close')." ORDER BY `priority` DESC,id ASC";
$col = [['s','img'],['i','buttons',0],['i','decoration',0]];
$cBanners = DB::ltSelect(DB_EIS_MAIN, $sql, $col, '');
$rButtonGroupIDs = array_column($cBanners, 1);
$sql = 'SELECT * FROM s_banner_home_button WHERE buttons IN (' . implode(',',$rButtonGroupIDs?:[0]) . ')';
$col = [['i','type'],['s','link'],['i','notice'],['s','icon'],['s','name']];
$cBannerButtons = DB::ltSelect(DB_EIS_MAIN, $sql, $col, 'buttons', ['m'=>true]);
$sql = "SELECT * FROM s_banner_home_decoration";
$col = [['i','type'],['t','time1',3],['t','time2',3],['i','int1'],['i','int2'],['s','str1'],['s','str2']];
$cBannerDecorations = DB::ltSelect(DB_EIS_MAIN, $sql, $col, 'id');
array_walk($cBanners, function(&$a) use ($cBannerButtons, $cBannerDecorations) {
    $a[1] = $cBannerButtons[$a[1]]??[];
    $a[2] = $cBannerDecorations[$a[2]]??[];
});
echo HTML::json('banners', $cBanners);

$flt = DB::ltSQLTimeIn('time_open','time_close');
$sql = "SELECT * FROM s_notice_auto
        WHERE id IN (SELECT notice FROM s_banner_home_button WHERE buttons IN (SELECT buttons FROM s_banner_home WHERE $flt))
        OR id IN (SELECT `target` FROM s_banner WHERE `type`=2 AND $flt)";
$columns = [['s','icon'],['t','time_record',3],['s','title'],['s','content']];
$cAutoNotices = DB::ltSelect(DB_EIS_MAIN, $sql, $columns, 'id');
echo HTML::json('autoNotices', $cAutoNotices);
?>
</script>
<?php
foreach (Basic::getAvailableMods() as $mod) {
    echo HTML::css('mods/' . $mod);
    echo HTML::js('mods/' . $mod);
}
?>
</head>
<body data-id="home">
<div id="top">
<h1>EIS-SIF</h1>
<h2><?=Basic::getStyleHomeSubtitle()??'一些资料和实验性页面'?></h2>
</div>
<div id="eis-sif-container">
<div id="sections-main">
<section class="eis-sif-section section-paged">
<h4><i class="fas fa-heart"></i> 大感谢祭</h4>
<ul class="notices fa-ul" data-notice-tab="1"></ul>
<div class="eis-sif-pagebar" data-control=".notices[data-notice-tab='1']" data-size=5></div>
</section>
<section class="eis-sif-section section-paged">
<h4><i class="fas fa-bullhorn"></i> 告知</h4>
<div class="section-toolbar"><a href="<?=Basic::getPageURL(24)?>" target="_blank"><i class="fas fa-history"></i> 过去的告知</a></div>
<ul class="notices fa-ul" data-notice-tab="0"></ul>
<div class="eis-sif-pagebar" data-control=".notices[data-notice-tab='0']" data-size=5></div>
</section>
<section class="eis-sif-section section-paged">
<h4><i class="fas fa-file"></i> 文章</h4>
<div class="section-toolbar">
<div class="eis-sif-button-group tiny"><span data-default onclick="listArticles(0)">A</span><span onclick="listArticles(1)">B</span></div>
</div>
<ul id="articles"></ul>
<div class="eis-sif-pagebar" data-control="#articles" data-size=5></div>
</section>
<section class="eis-sif-section disabled">
<h4><i class="fas fa-newspaper"></i> 动态</h4>
<p>此版块暂无内容</p>
<p class="eis-sif-note">※ 原先位于此处的页面移至下方 SIF 分区内</p>
</section>
<section class="eis-sif-section disabled">
<h4><i class="fas fa-tools"></i> 工具</h4>
<p>此版块暂无内容</p>
<p class="eis-sif-note">※ 原先位于此处的页面移至下方 SIF 分区内</p>
</section>
<section class="section-main-noborder">
<div id="home-banner-container">
<span id="home-banner-arrow-left" class="eis-jq-button" onclick="switchBannerDelta(-1)"><i class="fas fa-angle-left"></i></span>
<span id="home-banner-arrow-right" class="eis-jq-button" onclick="switchBannerDelta(1)"><i class="fas fa-angle-right"></i></span>
<div id="home-banner"></div>
</div>
<div id="home-banner-links"></div>
<div id="home-banner-dots"></div>
</section>
</div>
<section class="eis-theme-sifas eis-sif-section-noborder buttons">
<h4><i class="fas fa-gamepad fa-lg"></i> SIFAS</h4>
<div class="buttons-container buttons-container-outside">
<?php loadButtons(3); ?>
</div>
<div class="eis-sif-fold buttons-container-fold">
<h6>其它 SIFAS 相关页面</h6>
<div>
<p class="eis-sif-note">※ 以下页面可能已断更。</p>
<div class="buttons-container buttons-container-inside">
<?php loadButtons(4); ?>
</div>
</div>
</div>
</section>
<section class="eis-theme-prilo eis-sif-section-noborder">
<h4><i class="fas fa-clinic-medical fa-lg"></i> eis Project P（仮）</h4>
<p class="eis-sif-note">※ 规划中版块，暂无内容</p>
<?php HTML::printBanners(2); ?>
</section>
<section class="eis-sif-section-noborder buttons">
<h4><i class="fas fa-gamepad fa-lg"></i> SIF</h4>
<div class="buttons-container buttons-container-outside">
<?php loadButtons(1); ?>
</div>
<div class="eis-sif-fold buttons-container-fold">
<h6>其它 SIF 相关页面</h6>
<div>
<p class="eis-sif-note">※ 以下页面可能已断更。</p>
<div class="buttons-container buttons-container-inside">
<?php loadButtons(2); ?>
</div>
</div>
</div>
</section>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/foot.php';
