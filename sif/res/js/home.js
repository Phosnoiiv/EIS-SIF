var noticeIDs = [];
var preInited = false, articleHotValues = [];
function preInit() {
    if (preInited) return;
    $.each(articleHots, function(articleID, hot) {
        articleHotValues.push(hot);
    });
    articleHotValues.sort(function(h1,h2){return h2-h1;});
    preInited = true;
}
function init() {
    $.each(reminders, function(reminderIndex, reminder) {
        $("<li>").addClass("reminder").append(
            $("<span>").addClass("eis-sif-tag server-" + ((reminder[1] - 1) % 3 + 1)).text((reminder[1] > 3 ? "AS-" : "") + serverNameAShort[(reminder[1] - 1) % 3 + 1]),
            $("<span>").addClass("eis-sif-countdown").attr("data-time", reminder[3]).attr("data-countdown-short", 1),
            $("<div>").addClass("reminder-text").append(
                reminder[0] == 1 ? "距 " : "",
                $("<span>").addClass("reminder-name").text(reminder[2]),
                reminder[0] == 2 ? " 正在进行中！" : "",
            ),
        ).appendTo("#reminders");
    });
    $.each(notices, function(noticeID, notice) {
        noticeIDs.push(noticeID);
    });
    for (var i=0; i<banners.length; i++) {
        $('<div class="home-banner-dot">').appendTo("#home-banner-dots");
    }
    $(".home-banner-dot:first-child").addClass("active");
    enableCountdown();
}
function listArticles(panelID) {
    preInit();
    $("#articles").empty();
    var faHot = function(value) {
        return $('<i class="fas fa-fire" title="热度值 '+value+'">');
    }
    $.each(articles[panelID], function(articleIndex, article) {
        $("<li>").addClass("article-item").append(
            article[4] ? '<i class="fas fa-thumbtack" data-fa-transform="rotate--30" title="置顶文章"></i>' : "",
            $("<span>").addClass("eis-sif-tag article-" + article[3]).text(articleTagNames[article[3]]),
            $("<span>").addClass("article-date").append(
                new Date(article[2].fromDatestamp(0)).getUTCDateMedium(),
                articleHots[article[0]]>=articleHotValues[4] ? faHot(articleHots[article[0]]) : null,
            ),
            $("<a>").addClass("article-link").attr("href", "article/?" + article[0]).attr("target", "_blank").text(article[1]),
        ).appendTo("#articles");
    });
    refreshPageBar('[data-control="#articles"]');
}
function refreshNotices() {
    var FIXED = 10000;
    var readNoticeIDs = (Cookies.get("readNotices") || "").split("s");
    noticeIDs.sort(function (id1, id2) {
        var notice1 = notices[id1], notice2 = notices[id2];
        return (id1 > FIXED ? 0 : 1) - (id2 > FIXED ? 0 : 1) || notice2[4] - notice1[4] || (readNoticeIDs.indexOf(id1) < 0 ? 0 : 1) - (readNoticeIDs.indexOf(id2) < 0 ? 0 : 1) || notice2[1] - notice1[1] || id1 - id2;
    });
    $(".notices").empty();
    for (var i = 0; i < noticeIDs.length; i++) {
        var noticeID = noticeIDs[i], notice = notices[noticeID], isFixed = noticeID > FIXED;
        var $li = $("<li>").addClass("notice" + (isFixed ? " fixed" : readNoticeIDs.indexOf(noticeID) < 0 ? " new" : "")).append(
            $("<span>").addClass("fa-li").append('<i class="fas fa-' + (notice[0] || "bullhorn") + '"></i>'),
            isFixed ? "" : $("<span>").addClass("notice-date").text(serverDate(notice[1], 3).getUTCDateShort()),
            $("<div>").addClass(isFixed ? "" : "notice-clickable").text(notice[2]).attr("onclick", isFixed ? null : "readNotice(" + noticeID + ");refreshNotices()"),
        );
        if (typeof hookHomeNoticeItem === 'function') {
            $li = hookHomeNoticeItem(noticeID, $li);
        }
        $(".notices[data-notice-tab='"+notice[5]+"']").append($li);
    }
    refreshPageBar(null, true);
}
function switchBanner() {
    var iLink = function(target, type, link, notice) {
        switch (type) {
            case 1:
                $(target).wrap($('<a href="'+link+'" target="_blank">'));
                break;
            case 2:
                $(target).attr("onclick", "readNotice("+notice+");refreshNotices()");
                break;
        }
    };
    var index = $(".home-banner-dot.active").index();
    var banner = banners[index], buttons = banner[1], decoration = banner[2];
    $("#home-banner").empty().append($('<img src="/sif/res/img/u/banner/'+banner[0]+'.jpg">'));
    if (buttons.length) {
        iLink("#home-banner>img", buttons[0][0], buttons[0][1], buttons[0][2]);
    }
    switch (decoration[0]) {
        case 1:
            $('<span id="home-banner-decoration-count-large" class="eis-sif-countup" data-countup-day-ceil="1">')
                .attr("data-time", decoration[1]).text("--").attr("style", "color:" + decoration[5])
                .appendTo("#home-banner");
            break;
    }
    $("#home-banner-links").empty();
    $.each(buttons, function(buttonIndex, button) {
        var $link = $("<span>").append(
            '<i class="fas fa-'+(button[3]||"bullhorn")+'"></i> ',
            $('<span class="home-banner-link-text">').text(button[4]),
        );
        iLink($link.children(".home-banner-link-text"), button[0], button[1], button[2]);
        $("#home-banner-links").append($link);
    });
}
function switchBannerDelta(delta) {
    var index = $(".home-banner-dot.active").index() + delta;
    if (index<0) index = banners.length-1;
    else if (index>=banners.length) index = 0;
    $(".home-banner-dot").removeClass("active").filter(":nth-child("+(index+1)+")").addClass("active");
    switchBanner();
}

$(document).ready(function() {
    init();
    refreshNotices();
    switchBanner();
});
