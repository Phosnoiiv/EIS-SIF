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
    $("#notices").empty();
    for (var i = 0; i < noticeIDs.length; i++) {
        var noticeID = noticeIDs[i], notice = notices[noticeID], isFixed = noticeID > FIXED;
        $("<li>").addClass("notice" + (isFixed ? " fixed" : readNoticeIDs.indexOf(noticeID) < 0 ? " new" : "")).append(
            $("<span>").addClass("fa-li").append('<i class="fas fa-' + (notice[0] || "bullhorn") + '"></i>'),
            isFixed ? "" : $("<span>").addClass("notice-date").text(serverDate(notice[1], 3).getUTCDateShort()),
            $("<div>").addClass(isFixed ? "" : "notice-clickable").text(notice[2]).attr("onclick", isFixed ? null : "readNotice(" + noticeID + ");refreshNotices()"),
        ).appendTo("#notices");
    }
    refreshPageBar(null, true);
}

$(document).ready(function() {
    init();
    refreshNotices();
});
