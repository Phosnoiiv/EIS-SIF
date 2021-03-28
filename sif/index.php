<?php
namespace EIS\Lab\SIF;
require_once __DIR__ . '/core/init.php';
if (!defined('ROOT_SIFAS_CACHE'))
    define('ROOT_SIFAS_CACHE', dirname(ROOT_SIF_CACHE) . '/sifas');

$master_rerun_group_jp = (time() - SIF::ROTATION_BEGIN_MASTER_RERUN_JP) / 604800 % SIF::ROTATION_COUNT_MASTER_RERUN_JP + 1;
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
<h4><i class="fas fa-bell"></i> 备忘录</h4>
<ul id="reminders"></ul>
<div class="eis-sif-pagebar" data-control="#reminders" data-size=5></div>
</section>
<section class="eis-sif-section section-paged">
<h4><i class="fas fa-bullhorn"></i> 告知</h4>
<div class="section-toolbar"><a href="<?=Basic::getPageURL(24)?>" target="_blank"><i class="fas fa-history"></i> 过去的告知</a></div>
<ul id="notices" class="fa-ul"></ul>
<div class="eis-sif-pagebar" data-control="#notices" data-size=5></div>
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
<?=createButton(18, [1], '活动加成社员等', 'latest-event.js', ['title'=>'最新活动资讯'])?>
<?=createButton(22, [1], '活动应援社员', 'latest-arena.js')?>
<?=createButton(14, [1,3], '仅限查卡器无文字的课题', 'goals.js')?>
</section>
<section class="eis-sif-section buttons">
<h4><i class="fas fa-gamepad"></i> 玩法</h4>
<?=createButton(17, [], '', '', ['title'=>'招募模拟'])?>
<?php
$banners = Basic::getBanners(1);
if (!empty($banners)) {
    foreach ($banners as $banner) {
        switch ($banner[1]) {
            case 1:
                echo '<a class="banner" href="'.Basic::getPageURL($banner[2]).'" target="_blank"><img src="/sif/res/img/u/banner/'.$banner[0].'"/></a>'."\n";
                break;
        }
    }
}
?>
</section>
<section class="eis-sif-section buttons">
<h4><i class="fas fa-tools"></i> 工具</h4>
<?=createButton(21, [], 'Icon Collection、SM、MF、CF、友情大合战', '', ['title'=>'活动 pt 计算器（含控分计算）'])?>
</section>
</div>
<section class="eis-sif-section-noborder buttons">
<h4><i class="fas fa-database fa-lg"></i> 资料</h4>
<div class="buttons-container">
<?=createButton(2, [1,3,2], '2018 年 9 月至今', 'login.js')?>
<?=createButton(7, [4,5], '附背景', 'login.js')?>
<?=createButton(20, [4,5], '', 'live-detail.js')?>
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
<section class="eis-sif-section">
<h3>日语版 μ's 歌曲的 MASTER 谱面再登场日程</h3>
<?php
foreach ($master_rerun_groups as $groups) {
    foreach ($groups as $name => $group) {
        echo '<div class="live-container">' . "\n";
        echo '<h4>' . $name . '</h4>' . "\n";
        foreach ($master_rerun_schedule[$group] ?? [] as $master) {
            echo '<div class="live attribute-' . $master['attribute'];
            echo $master['is_swing'] ? ' swing' : '';
            echo '">' . "\n";
            echo '<div class="track-name">' . $master['jp_name'] . '</div>' . "\n";
            echo '<div class="note">' . $master['level'] . '★' . $master['note_total'] . '</div>' . "\n";
            echo '</div>' . "\n";
        }
        echo '</div>' . "\n";
    }
}
?>
<p class="eis-sif-note">※ 每周日 23:00 自动更新。每日轮换的 MASTER 谱面不在此处列出；Aqours 歌曲的 MASTER 谱面不在此处列出。</p>
</section>
<?php
require ROOT_SIF_WEB . '/common-d42c0d8a/foot.php';
