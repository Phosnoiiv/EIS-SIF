var memberDataStorage = {}, categoryDataStorage = {};
var categoryTags = ["", "【NEW】", "【UPDATE】", ""];
function produce() {
    $.each(members, function(memberId, member) {
        $("<div>").addClass("eis-sif-gallery-item eis-sif-bg-2 member-" + member[2]).append(
            member[1],
        ).attr("onclick", "showMember(" + memberId + ")").appendTo("#members");
    });
    var listCategories = [];
    $.each(categories, function(categoryId, category) {
        if (!category[1])
            return;
        listCategories.push($("<div>").addClass("eis-sif-gallery-item" + (category[2] ? " category-highlight" : "")).append(
            categoryTags[category[2]] + category[0],
        ).attr("data-order", category[1]).attr("onclick", "showCategory(" + categoryId + ")"));
    });
    listCategories.sort(function(l1, l2) {
        return $(l1).attr("data-order") - $(l2).attr("data-order");
    });
    $("#categories").append(listCategories);
    $("#voices").tooltip({items:".voice", content:function() {
        if ($(this).attr("data-multi")) {
            var div = $("<div>").append(
                $("<h6>").text("英语文本："),
                $("<h6>").text("简体中文文本："),
                $("<h6>").text("繁体中文文本："),
            );
            for (var i = $(this).attr("data-multi") - 1; i >= 0; i--) {
                div.find("h6:nth-of-type(1)").after($("<p>").html($(this).attr("data-en" + i).replace(/\\n/g, "<br>") || "（数据暂缺）"));
                div.find("h6:nth-of-type(1)").after($("<p>").html($(this).attr("data-cn" + i).replace(/\\n/g, "<br>") || "（数据暂缺）"));
                div.find("h6:nth-of-type(2)").after($("<p>").html($(this).attr("data-zh" + i).replace(/\\n/g, "<br>") || "（数据暂缺）"));
            }
            return div;
        } else {
            return $("<div>").append(
                $("<h6>").text("英语文本："),
                $("<p>").html($(this).attr("data-en").replace(/\\n/g, "<br>") || "（数据暂缺）"),
                $("<h6>").text("简体中文文本："),
                $("<p>").html($(this).attr("data-cn").replace(/\\n/g, "<br>") || "（数据暂缺）"),
                $("<h6>").text("繁体中文文本："),
                $("<p>").html($(this).attr("data-zh").replace(/\\n/g, "<br>") || "（数据暂缺）"),
            );
        }
    }, position:{my:"left top", at:"left+50 bottom"}});
}
function showMember(memberId) {
    if (memberId in memberDataStorage) {
        showMemberFin(memberId, memberDataStorage[memberId]);
        return;
    }
    $.get("/sifas/interface/voices.php", {m:memberId}).done(function(response) {
        memberDataStorage[memberId] = response;
        showMemberFin(memberId, response);
    });
}
function showMemberFin(memberId, data) {
    var member = members[memberId], listVoices = [];
    $("#section-voices>h4").text(member[1] + " 的语音文本");
    $.each(data, function(index, voice) {
        var note = categories[voice[0]][0], order = categories[voice[0]][1];
        if (voice[2] && voice[2].c != undefined) {
            note = "卡片特训解锁：" + (cards[voice[2].c][4] || cards[voice[2].c][5]);
            order = 2000 - cards[voice[2].c][0];
        } else if (voice[2] && voice[2].s != undefined) {
            note = "服装限定：" + suits[voice[2].s][0];
            order = 1000;
        }
        listVoices.push(qVoice(voice, memberId, note, order));
    });
    listVoices.sort(function(l1, l2) {
        return $(l1).attr("data-order") - $(l2).attr("data-order");
    });
    $("#voices").empty().append(listVoices);
    $(".voice").mouseenter(function() {
        $(this).removeClass("eis-sif-bg-0").addClass("eis-sif-bg-1");
    }).mouseleave(function() {
        $(this).removeClass("eis-sif-bg-1").addClass("eis-sif-bg-0");
    });
    $("#section-voices").removeClass("eis-sif-hidden");
    $(window).scrollTop($("#section-voices").position().top);
    var _paq = window._paq || [];
    _paq.push(["setCustomVariable", 1, "Member ID", memberId, "page"]);
    _paq.push(["deleteCustomVariable", 3, "page"]);
    _paq.push(["trackEvent", "Voices", "Switch Member"]);
}
function showCategory(categoryId) {
    if (categoryId in categoryDataStorage) {
        showCategoryFin(categoryId, categoryDataStorage[categoryId]);
        return;
    }
    $.get("/sifas/interface/voices.php", {c:categoryId}).done(function(response) {
        categoryDataStorage[categoryId] = response;
        showCategoryFin(categoryId, response);
    });
}
function showCategoryFin(categoryId, data) {
    var category = categories[categoryId], listVoices = [];
    $("#section-voices>h4").html("各成员的" + category[0] + (category[0].substr(category[0].length - 2, 2) == "语音" ? "" : "语音") + "文本");
    $.each(data, function(index, voice) {
        var member = members[voice[0]];
        listVoices.push(qVoice(voice, voice[0], member[1]));
    });
    $("#voices").empty().append(listVoices);
    $(".voice").mouseenter(function() {
        $(this).removeClass("eis-sif-bg-0").addClass("eis-sif-bg-1");
    }).mouseleave(function() {
        $(this).removeClass("eis-sif-bg-1").addClass("eis-sif-bg-0");
    });
    $("#section-voices").removeClass("eis-sif-hidden");
    $(window).scrollTop($("#section-voices").position().top);
    var _paq = window._paq || [];
    _paq.push(["setCustomVariable", 3, "Voice Category", category[0].replace("【NEW】", ""), "page"]);
    _paq.push(["deleteCustomVariable", 1, "page"]);
    _paq.push(["trackEvent", "Voices", "Switch Category"]);
}
function qVoice(voice, memberId, note, order) {
    var strEmptyJP = "（日语文本暂缺，鼠标悬停或触屏点击查看英语、简体中文和繁体中文文本）";
    var member = members[memberId];
    var div = $("<div>").addClass("voice eis-sif-bg-0 member-" + member[2]).append(
        $("<div>").addClass("voice-note").html(note),
    ).attr("data-order", order || 0);
    if ($.isArray(voice[1][0])) {
        div.attr("data-multi", voice[1].length);
        $("<p>").addClass("eis-sif-note").text("※ 本条为分段语音。").appendTo(div);
        $.each(voice[1], function(voiceOrder, voicePart) {
            div.append(
                voiceOrder ? $("<hr>") : "",
                voicePart[0].replace(/\\n/g, "<br>") || strEmptyJP,
            ).attr("data-en" + voiceOrder, voicePart[1]).attr("data-cn" + voiceOrder, voicePart[2]).attr("data-zh" + voiceOrder, voicePart[3]);
        });
    } else {
        div.append(voice[1][0].replace(/\\n/g, "<br>") || strEmptyJP).attr("data-en", voice[1][1]).attr("data-cn", voice[1][2]).attr("data-zh", voice[1][3]);
    }
    return div;
}

$(document).ready(function() {
    produce();
});
