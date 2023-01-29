var data, dataStorage = {};
var server, year, month;
var calendarArg = {
    "classDefault":"empty",
    "classBlank":"blank",
    "dateIdPrefix":"day-",
    "dateClass":".date",
    "components":["date","star","item1"],
};
var countResults = {};
const COL_LOGIN_GIFT1 = 14;
function init() {
    if (inAprilFools) {
        items[1][0][1] = "../sif/item/l"; items[1][0][2] = "../sif/item/ls";
        items[9][9000][1] = "../sif/item/t"; items[9][9000][2] = "../sif/item/ts";
        items[9][9015][1] = "../sif/item/93"; items[9][9015][2] = "../sif/item/93s";
        items[17][1300][1] = "../sif/recovery/1"; items[17][1300][2] = "../sif/recovery/1s";
    }
    $.each(countItems, function(index, itemRef) {
        if (!itemRef)
            return;
        var type = itemRef[0], key = itemRef[1], item = items[type][key];
        $("<div>").addClass("count-item").append(
            qASImg(item[1]),
            $("<div>").addClass("count-amount"),
        ).attr("data-type", type).attr("data-key", key).appendTo("#counts");
        countResults[type + "-" + key] = 0;
    });
    $("#calendar, #counts").tooltip({items:".calendar-item, .count-item", content:function() {
        var type = $(this).attr("data-type"), key = $(this).attr("data-key");
        return qItemIntro(type, key);
    }});
}
function produce(ignoreRecord) {
    server = parseInt($("#server").val());
    year = $("#month").val().substr(0, 4);
    month = $("#month").val().substr(5, 2);
    if (server < 1 || server > 3 || year < 2019 || year > 2023 || month < 1 || month > 12)
        return;
    calendar("#calendar>tbody", year, parseInt(month), calendarArg);
    $("#bonuses").empty();
    var key = server + "-" + year + "-" + month;
    if (key in dataStorage) {
        data = dataStorage[key];
        produceFinal();
        return;
    }
    $.get(
        "/sifas/interface/login.php",
        {s:server, y:year, m:month}
    ).done(function(response) {
        data = dataStorage[key] = response;
        produceFinal();
    }).fail(function() {
        alert("获取数据时出错。");
    });
    var _paq = window._paq || [];
    if (!ignoreRecord) {
        _paq.push(["setCustomVariable", 1, "Data Month", year + "-" + month, "page"]);
        _paq.push(["setCustomVariable", 2, "Data Server", server, "page"]);
        _paq.push(["trackEvent", "Calendar Login (SIFAS)", "Switch Calendar"]);
    }
}
function produceFinal() {
    $.each(countResults, function(pointer, amount) {
        countResults[pointer] = 0;
    });
    $.each(data.bonuses, function(id, bonus) {
        var dateOpen = (bonus[1+2*server]||bonus[3]).toServerDate(2,server);
        var dateLast = ((bonus[1+2*server]||bonus[3])+(bonus[COL_LOGIN_GIFT1].length-1)*86400).toServerDate(2,server);
        if (dateOpen.getUTCMonth() == month - 1 || dateLast.getUTCMonth() == month - 1) {
            $("<li>").append(
                $("<span>").addClass("bonus-date-open").text(dateOpen.getUTCDateMedium()),
                $("<span>").addClass("bonus-clickable eis-sif-text category-" + bonus[9]).text(bonus[-1+server]).attr("onclick", "popupBonus(" + id + ")"),
            ).appendTo("#bonuses");
        }
        for (var i = 0; i < bonus[COL_LOGIN_GIFT1].length; i++) {
            var giftID = bonus[COL_LOGIN_GIFT1][i];
            var dateThis = ((bonus[1+2*server]||bonus[3])+i*86400).toServerDate(2,server);
            var isThisMonth = dateThis.getUTCMonth() == month - 1;
            var date = dateThis.getUTCDate();
            var isFirstItem = true;
            $.each(gifts[bonus[COL_LOGIN_GIFT1][i]], function(type, content) {
                if ($.isNumeric(content)) {
                    addItem(id, giftID, isFirstItem ? i + 1 : 0, isThisMonth, date, type, 0, content);
                } else if ($.isArray(content)) {
                    $.each(content, function(_, key) {
                        addItem(id, giftID, isFirstItem ? i + 1 : 0, isThisMonth, date, type, key, 1);
                        isFirstItem = false;
                    });
                } else if ($.isPlainObject(content)) {
                    $.each(content, function(key, amount) {
                        addItem(id, giftID, isFirstItem ? i + 1 : 0, isThisMonth, date, type, key, amount);
                        isFirstItem = false;
                    });
                }
                isFirstItem = false;
            });
        }
    });
    var caption = year + " 年 " + month + " 月 SIFAS " + $("#server :selected").text();
    $("#caption").text(caption + "特殊登录奖励");
    $(".name").click(function() {
        if ($(this).hasClass("active"))
            return;
        $(".name").removeClass("active");
        $(".detail").slideUp();
        $(this).addClass("active");
        $(this).next().slideDown();
    });
    $(".bonus:first-child .name").addClass("active");
    $(".bonus:first-child .detail").show();
    $(".count-item").each(function() {
        var count = countResults[$(this).attr("data-type") + "-" + $(this).attr("data-key")];
        $(this).find(".count-amount").text(count);
        if (count) {
            $(this).removeClass("zero");
        } else {
            $(this).addClass("zero");
        }
    });
}
function popupBonus(id) {
    var bonus = data.bonuses[id];
    var dateOpen = (bonus[1+2*server]||bonus[3]).toServerDate(2,server);
    var dateClose = (bonus[2+2*server]||bonus[4]).toServerDate(2,server);
    var dialog = $("#dialog-bonus");
    $("#dialog-bonus .eis-sif-dialog-title").empty().append(
        $("<span>").addClass("eis-sif-tag server-" + server).text(serverNameAShort[server]),
        $("<span>").addClass("eis-sif-text category-" + bonus[9]).text(bonus[-1+server]),
    );
    $("#dialog-bonus .eis-sif-dialog-info").empty().append(
        $("<p>").append(
            '<i class="fas fa-calendar-week"></i> ',
            dateOpen.getUTCDateTimeFull() + "～" + dateClose.getUTCDateTimeFull(),
        ),
    );
    var table = $(".bonus-table").empty();
    for (var i = 0; i < bonus[COL_LOGIN_GIFT1].length; i++) {
        var isFirstItem = true;
        $.each(gifts[bonus[COL_LOGIN_GIFT1][i]], function(type, content) {
            if ($.isNumeric(content)) {
                qItemTable(isFirstItem ? i + 1 : 0, type, 0, content).appendTo(table);
            } else if ($.isArray(content)) {
                $.each(content, function(_, key) {
                    qItemTable(isFirstItem ? i + 1 : 0, type, key, 1).appendTo(table);
                    isFirstItem = false;
                });
            } else if ($.isPlainObject(content)) {
                $.each(content, function(key, amount) {
                    qItemTable(isFirstItem ? i + 1 : 0, type, key, amount).appendTo(table);
                    isFirstItem = false;
                });
            }
            isFirstItem = false;
        });
    }
    if (bonus[10+server]) {
        $(".bonus-bg-thumb").attr("src", getImgFullPath("sifas/thumb/" + bonus[10+server] + ".jpg"));
        if (availablePNGs[bonus[10+server]]) {
            var png = availablePNGs[bonus[4]];
            var group = availablePNGGroups[png[0]];
            var link = "/sifas/interface/png.php?p=" + group[1] + "&c=" + png[1];
            $("#dialog-gallery-free").show();
            $("#dialog-gallery-flag .eis-sif-countdown").attr("data-time", group[0]);
            $("#dialog-gallery-link").attr("href", "/sifas/interface/png.php?p=" + group[1] + "&c=" + png[1]);
        } else {
            $("#dialog-gallery-free").hide();
            $("#dialog-gallery-link").attr("href", "/sifas/interface/png.php?k=" + bonus[10+server]);
        }
        $(".bonus-bg-thumb, #dialog-gallery-panel").show();
        $("#bonus-bg-none").hide();
    } else {
        $(".bonus-bg-thumb, #dialog-gallery-panel").hide();
        $("#bonus-bg-none").show();
    }
    $(dialog).dialog({modal:true, position:{of:window}, resizable:false, draggable:false, closeText:"关闭", classes:{
        "ui-dialog":"dialog-bonus category-" + bonus[9],
    }, close:function(event, ui) {
        $(this).dialog("destroy");
    }}).tooltip({items:".bonus-table td:nth-child(5)", content:function() {
        var type = $(this).attr("data-type"), key = $(this).attr("data-key");
        return qItemIntro(type, key);
    }, position:{my:"right top", at:"right bottom"}});
    $(window).resize();
    enableCountdown();
}
function addItem(id, giftID, dayNum, isThisMonth, date, type, key, amount) {
    var item = (items[type] && items[type][key]) ? items[type][key] : data.items[type][key];
    if (!isThisMonth)
        return;
    $("#" + calendarArg.dateIdPrefix + date).removeClass(calendarArg.classDefault);
    if (largeGiftIDs.indexOf(giftID)<0) {
    var container = $("<span>");
    qASImg(item[item[0]]).appendTo(container);
    $("<span>").text(amount).appendTo(container);
    container.addClass("calendar-item").attr("data-type", type).attr("data-key", key).appendTo($("#" + calendarArg.dateIdPrefix + date + ">div")[item[3]]);
    } else if (dayNum) {
        $("<span>").append("icon/d0".toJQImg(1,2)).appendTo("#"+calendarArg.dateIdPrefix+date+">div:nth-child(3)");
    }
    var pointer = type + "-" + key;
    if (countResults[pointer] != undefined) {
        countResults[pointer] += amount;
    }
}
function galleryDownloaded() {
    setTimeout(function() {
        refreshLimit($("#dialog-gallery-limit .limit-capacity-amount").attr("data-limit"));
    }, 2000);
}
function qItemTable(dayNum, type, key, amount) {
    var item = (items[type] && items[type][key]) ? items[type][key] : data.items[type][key];
    return $("<tr>").append(
        $("<td>").text(dayNum ? "第 " + dayNum + " 天：" : ""),
        $("<td>").append($("<div>").append(qASImg(item[2]||item[1]))),
        $("<td>").append(
            item[4 + server],
        ),
        $("<td>").text(amount),
        $("<td>").append('<i class="far fa-question-circle"></i>').attr("data-type", type).attr("data-key", key),
    );
}
function qItemIntro(type, key) {
    var item = (items[type] && items[type][key]) ? items[type][key] : data.items[type][key];
    var names = [], descriptions = [];
    descriptions[0] = item[11];
    for (var i = 1; i <= 3; i++) {
        names[i] = item[4 + i];
        descriptions[i] = item[7 + i];
    }
    var div = $("<div>").append(
        $("<h5>").text(names[server]),
        descriptions[1] || descriptions[2] || descriptions[3] ? $("<h6>").addClass("header-desc").text("官方介绍") : "",
        descriptions[0] ? $("<h6>").text("补充介绍") : "",
        descriptions[0] ? $("<p>").html(descriptions[0].replace(/\n/g, "<br>")) : "",
    );
    for (var i = 3; i >= 1; i--) {
        if (server == i)
            continue;
        if (names[i]) {
            div.find("h5").after($("<p>").addClass("eis-sif-note").text(names[i]));
        }
        if (descriptions[i]) {
            div.find(".header-desc").after($("<p>").addClass("eis-sif-note").html(descriptions[i].replace(/\n/g, "<br>")));
        }
    }
    if (descriptions[server]) {
        div.find(".header-desc").after($("<p>").html(descriptions[server].replace(/\n/g, "<br>")));
    }
    return div;
}

$(document).ready(function() {
    init();
    produce(true);
    $.each(regionNotices["limit"], function(noticeIndex, notice) {
        $("<p>").addClass("eis-sif-note").html("※ " + notice).appendTo("#dialog-gallery-notes");
    });
    $.each(regionNotices["limit-campaign"], function(noticeIndex, notice) {
        $("<p>").addClass("eis-sif-note").html("※ " + notice).appendTo("#dialog-gallery-free");
    });
})
