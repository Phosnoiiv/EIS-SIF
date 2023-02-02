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

function createButton($pageID, $servers = [], $note = '', $file = '', $options = []) {
    global $config, $pages;
    if (($available = $config['pages'][$pageID]['available'] ?? 0) > time()) {
        return '<a href="' . Basic::getPageURL($pageID) . '" target="_blank"><div class="title"><i class="fas fa-lock"></i> ???</div><div class="note">' . date('m/d H:i', $available) . ' 公开预定</div></a>' . "\n";
    }
    $page = $pages[$pageID];
    $isAS = $page['game'] == 2;
    $button = '<a' . ($isAS ? ' class="sifas"' : '') . ' href="' . Basic::getPageURL($pageID) . '" target="_blank">';
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

$sql = "SELECT * FROM s_notice_auto WHERE id IN (SELECT notice FROM s_banner_home_button WHERE buttons IN (SELECT buttons FROM s_banner_home WHERE ".DB::ltSQLTimeIn('time_open','time_close')."))";
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
<h2>一些资料和实验性页面</h2>
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
<section class="eis-sif-section buttons">
<h4><i class="fas fa-newspaper"></i> 动态</h4>
<?=createButton(32, [1], '庆典活动（SM、MF 等，2022/04 至今）<br>Live ♪ Arena（2020/11 至今）', 'event-yell.json')?>
<?=createButton(14, [1,3], '仅限查卡器无文字的课题', 'goals.js')?>
</section>
<section class="eis-sif-section buttons">
<h4><i class="fas fa-tools"></i> 工具</h4>
<?=createButton(21, [], 'Icon Collection、SM、MF、CF、友情大合战', '', ['title'=>'活动 pt 计算器（含控分计算）'])?>
<?=createButton(27, [], '', '', ['title'=>'通常饰品制作概率计算器'])?>
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
<section class="eis-sif-section-noborder buttons">
<h4><i class="fas fa-database fa-lg"></i> 资料</h4>
<div class="buttons-container">
<?=createButton(2, [1,3,2], '2018 年 9 月至今', 'login.js')?>
<?=createButton(7, [4,5], '附背景', 'login.js')?>
<?=createButton(29, [4,6], '', [290101])?>
<?=createButton(20, [4,5], '', 'live-detail.js')?>
<?=createButton(31, [1,2,3])?>
<?=createButton(13, [1,2,3], '', 'event.rc.js')?>
<?=createButton(23, [4,5,6], '', 'goals.js')?>
<?=createButton(10, [1,3,2], '2020 年 1 月至今', 'trades.js')?>
<?=createButton(11, [4], '', 'trades.js')?>
<?=createButton(6, [2], '以第 20 次为例', 'event.mf.mission.js', ['title'=>'Medley Festival 任务'])?>
<?=createButton(15, [4,5,6], '不含音频', 'voices.js')?>
<?=createButton(9, [1,2,3], '2018/08～2020/02', 'box.stepups.js', ['title'=>'阶梯招募列表'])?>
<?=createButton(3, [1,2,3], '2018/08～2020/02', 'box.stepup.js', ['title'=>'阶梯招募单级'])?>
<?=createButton(8, [1,2,3], '', 'stories.js', ['title'=>'特别剧情开放情况'])?>
</div>
</section>
<section class="eis-sif-section-noborder buttons">
<h4><i class="fas fa-images fa-lg"></i> 图库</h4>
<div class="buttons-container">
<?=createButton(16, [1,3,2], '2019 年 2 月至今', 'covers.js', ['title'=>'封面背景'])?>
<?=createButton(19, [1], '6.11 版本以后', 'posters.js', ['title'=>'招募画像'])?>
</div>
<p class="eis-sif-note">※ 一部分图库位于资料页面中，参见“SIFAS 特殊登录奖励”。</p>
</section>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/foot.php';
