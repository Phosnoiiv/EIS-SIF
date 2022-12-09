var data, got = {};
var server, year, month;
var calendarArg = {
    classDefault:"empty", classBlank:"blank", dateIdPrefix:"day-", dateClass:".date",
    components:["title separated", "unit", "stamp separated", "date", "item1 separated", "item2", "background separated", "item3 separated", "item3"],
}
var countResults = {};
function jimgv(path) {
    return $("<img>").attr("src", "/vio/sif/" + path);
}
function init() {
    if (inAprilFools) {
        items[1000][1][1] = "../sifas/ticket/1"; items[1000][1][4] = "../sifas/ticket/1s";
        items[1000][1200][1] = "../sifas/item/k"; items[1000][1200][4] = "../sifas/item/ks";
        items[3000][0][4] = "../sifas/item/gs";
        items[3001][0][1] = "../sifas/item/s"; items[3001][0][4] = "../sifas/item/ss";
        items[3002][0][4] = "../sifas/item/es";
        items[3006][2][4] = "../sifas/exchange/g0s";
        items[8000][1][1] = "../sifas/item/l1"; items[8000][1][4] = "../sifas/item/l1s"; items[8000][1][0] = 2;
    }
    $.each(countItems, function(index, itemRef) {
        if (!itemRef)
            return;
        var type = itemRef[0], key = itemRef[1], item = items[type][key];
        $("<div>").addClass("count-item").append(
            qImg(item[1], item[7]),
            $("<div>").addClass("count-amount"),
        ).attr("data-type", type).attr("data-key", key).appendTo("#counts");
        countResults[type + "-" + key] = 0;
    });
    $("#calendar").tooltip({items:".calendar-item, .eis-sif-item", content:function() {
        var type = parseInt($(this).attr("data-type")), key = $(this).attr("data-key");
        return qItemIntro(type, key);
    }, position:{my:"left-15 top-5", at:"right bottom"}});
}
function produce(ignoreRecord) {
    year = $("#month").val().substr(0, 4);
    month = $("#month").val().substr(5, 2);
    if (year < 2018 || year > 2022 || month < 1 || month > 12)
        return;
    var dateBegin = new Date(year, month - 1);
    server = parseInt($("#server").val());
    if (year == "" || month == "" || server == "")
        return;
    calendar("#calendar", year, parseInt(month), calendarArg);
    $("#list").empty();
    var key = server + "-" + year + "-" + month;
    if (key in got) {
        data = got[key];
        produceFinal();
        return;
    }
    $.get(
        "/sif/interface/login.php",
        {"s": server, "y": year, "m": month},
    ).done(function(response) {
        data = got[key] = response;
        produceFinal();
    }).fail(function() {
        alert("获取数据时出错。");
    });
    var _paq = window._paq || [];
    if (!ignoreRecord) {
        _paq.push(["setCustomVariable", 1, "Data Month", year + "-" + month, "page"]);
        _paq.push(["setCustomVariable", 2, "Data Server", server, "page"]);
        _paq.push(["trackEvent", "Calendar Login (SIF)", "Switch Calendar"]);
    }
}
function produceFinal() {
    $.each(countResults, function(pointer, amount) {
        countResults[pointer] = 0;
    });
    $.each(data.bonuses, function(id, bonus) {
        var dateOpen = serverDate(bonus[1], server);
        var desc = (dateOpen.getUTCMonth() + 1) + "/" + dateOpen.getUTCDate();
        if (bonus[2] == 0) {
            desc += " 起无限期";
        } else if (bonus[2] - bonus[1] > 86400) {
            var dateClose = new Date(bonus[2] * 1000);
            desc += "～" + (dateClose.getUTCMonth() + 1) + "/" + dateClose.getUTCDate();
        }
        desc += "（共 " + bonus[3].length + " 天）：" + bonus[0];
        var dateLast = serverDate(bonus[1] + (bonus[3].length - 1) * 86400, server);
        if (dateOpen.getUTCMonth() == month - 1 || dateLast.getUTCMonth() == month - 1) {
            $("<li>").append(
                $("<span>").addClass("bonus-date-open").text(dateOpen.getUTCDateMedium()),
                $("<span>").addClass("bonus-clickable").text(bonus[0]).attr("onclick", "popupBonus(" + id + ")"),
            ).appendTo("#list");
        }
        for (var i = 0; i < bonus[3].length; i++) {
            var dateThis = new Date((bonus[1] + serverTimezone[server]) * 1000 + i * 86400000);
            if (dateThis.getUTCMonth() != month - 1)
                continue;
            var date = dateThis.getUTCDate();
            var cell = $("#day-" + dateThis.getUTCDate()).removeClass("empty");
            $.each(gifts[bonus[3][i]], function(type, content) {
                type = parseInt(type);
                if ($.isNumeric(content)) {
                    addItem(date, type, 0, content);
                } else if ($.isArray(content)) {
                    $.each(content, function(index, key) {
                        addItem(date, type, parseInt(key), 1);
                    });
                } else if ($.isPlainObject(content)) {
                    $.each(content, function(key, amount) {
                        addItem(date, type, parseInt(key), amount);
                    });
                }
            });
        }
    });
    var title = year + " 年 " + month + " 月" + $("#server :selected").text();
    $("#title").text(title + "特殊登录奖励");
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
    var dateOpen = new Date((bonus[1] + serverTimezone[server]) * 1000);
    var dateClose = new Date((bonus[2] + serverTimezone[server]) * 1000);
    var dialog = $("#dialog-bonus").clone().attr("id", "");
    $(dialog).find(".eis-sif-dialog-title").append(
        $("<span>").addClass("eis-sif-tag server-" + server).text(serverNameAShort[server]),
        $("<span>").text(bonus[0]),
    );
    $(dialog).find(".eis-sif-dialog-info").append(
        $("<p>").append(
            $("<span>").addClass("ui-icon ui-icon-clock"),
            dateOpen.getUTCDateTimeFull() + "～" + (bonus[2] ? dateClose.getUTCDateTimeFull() : "无期限"),
        ),
    );
    var table = $(dialog).find("table");
    for (var i = 0; i < bonus[3].length; i++) {
        var isFirstItem = true;
        $.each(gifts[bonus[3][i]], function(type, content) {
            type = parseInt(type);
            if ($.isNumeric(content)) {
                qItemTable(isFirstItem ? i + 1 : 0, type, 0, content).appendTo(table);
                isFirstItem = false;
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
        });
    }
    $(dialog).dialog({modal:true, position:{of:window}, resizable:false, draggable:false, closeText:"关闭", classes:{
        "ui-dialog":"dialog-bonus",
    }, close:function(event, ui) {
        $(this).dialog("destroy");
    }}).tooltip({items:".bonus-table td:nth-child(4)", content:function() {
        var type = parseInt($(this).attr("data-type")), key = $(this).attr("data-key");
        return qItemIntro(type, key);
    }, position:{my:"right top", at:"right bottom"}});
    $(window).resize();
}
function getImgCalendar(type, key) {
    switch (type) {
        case 1001:
            if (inAprilFools) {
                var unit = data.units[key], memberId = unit[1];
                return "unit/" + (memberId <= 9 ? 1529 + memberId : memberId >= 101 && memberId <= 109 ? 1438 + memberId : key);
            }
            return "unit/" + key;
        case 5100:
            if (inAprilFools) {
                var emblemPath = "";
                $.each([77, 231, 316], function(index, baseId) {
                    var diff = key - baseId;
                    if (diff >= 0 && diff <= 17)
                        emblemPath = "m" + (diff + 1) + "1";
                });
                if (emblemPath)
                    return "../sifas/emblem/" + emblemPath;
            }
            return "title/" + data.titles[key][server - 1];
        case 5200:
            return "background/" + key + "t";
        case 5600:
            if (inAprilFools) {
                if (key < 100) {
                    return "../sifas/stamp/" + ((key - 1) % 9 + 1);
                } else if (key < 200) {
                    return "../sifas/stamp/" + ((key - 101) % 9 + 10);
                }
            }
            return "stamp/" + (server == 1 ? "" : "s/") + key + (server == 2 ? "e" : server == 3 ? "c" : "");
    }
    var item = (items[type] && items[type][key]) ? items[type][key] : data.items[type][key];
    return item[3 * item[0] - 3 + server] || item[3 * item[0] - 2];
}
function getImgTable(type, key) {
    var common = getImgSmall(type, key);
    if (common)
        return common;
    switch (type) {
        case 1001:
            return "icon/" + [null, "n", "r", "s", "u", "ss"][data.units[key][2]];
    }
    var item = (items[type] && items[type][key]) ? items[type][key] : data.items[type][key];
    return item[3 + server] || item[4] || getImgCalendar(type, key);
}
function getItemName(type, key, server) {
    switch (type) {
        case 1001:
            var unit = data.units[key], member = members[unit[1]];
            return [null, "N", "R", "SR", "UR", "SSR"][unit[2]] + " " + member[server - 1] + (unit[3 + server] ? "【" + unit[3 + server] + "】" : "");
        case 5100:
            return data.titles[key][2 + server];
        case 5200:
            return data.backgrounds[key][server];
        case 5600:
            return data.stamps[key][server - 1];
    }
    var item = (items[type] && items[type][key]) ? items[type][key] : data.items[type][key];
    return item[6 + server];
}
function getItemDesc(type, key, server) {
    switch (type) {
        case 1001:
            return "";
        case 5100:
            return data.titles[key][5 + server];
        case 5200:
            var desc = data.backgrounds[key][3 + server];
            if (desc.indexOf(getItemName(type, key, server).replace("【背景】", "")) >= 0) {
                desc = desc.substring(desc.indexOf("\\n") + 2);
            }
            return desc;
        case 5600:
            return server == 4 ? data.stamps[key][3] : "";
    }
    var item = (items[type] && items[type][key]) ? items[type][key] : data.items[type][key];
    return item[9 + server];
}
function getItemPosition(type, key) {
    switch (type) {
        case 1001:
            return 1;
        case 5100:
            return 0;
        case 5200:
            return 6;
        case 5600:
            return 2;
    }
    var item = (items[type] && items[type][key]) ? items[type][key] : data.items[type][key];
    return item[14];
}
function addItem(date, type, key, amount) {
    var region = $("#" + calendarArg.dateIdPrefix + date + ">div")[getItemPosition(type, key)];
    if ($(region).hasClass("separated")) {
        for (var i = amount; i > 0; i--) {
            qItemCalendar(type, key, 0).appendTo(region);
        }
    } else {
        qItemCalendar(type, key, amount).appendTo(region);
    }
    var pointer = type + "-" + key;
    if (countResults[pointer] != undefined) {
        countResults[pointer] += amount;
    }
}
function qItemCalendar(type, key, amount) {
    if (type==1001) return gItem(1001,key,1,amount,{v:78},gConfig);
    return $("<span>").addClass("calendar-item").append(
        qImg(getImgCalendar(type, key)),
        amount ? $("<span>").addClass("calendar-amount " + (amount > 1 ? "" : "single")).text(amount) : "",
    ).attr("data-type", type).attr("data-key", key);
}
function qItemTable(dayNum, type, key, amount) {
    return $("<tr>").append(
        $("<td>").text(dayNum ? "第 " + dayNum + " 天：" : ""),
        $("<td>").append(
            qImg(getImgTable(type, key)),
            getItemName(type, key, server),
        ),
        $("<td>").text(amount),
        $("<td>").append($("<span>").addClass("ui-icon ui-icon-help")).attr("data-type", type).attr("data-key", key),
    );
}
function qItemIntro(type, key) {
    var names = [], descriptions = [];
    descriptions[0] = getItemDesc(type, key, 4);
    for (var i = 1; i <= 3; i++) {
        names[i] = getItemName(type, key, i);
        descriptions[i] = getItemDesc(type, key, i);
    }
    var div = $("<div>").append(
        $("<h5>").text(names[server]),
        descriptions[1] || descriptions[2] || descriptions[3] ? $("<h6>").addClass("header-desc").text("官方介绍") : "",
        descriptions[0] ? $("<h6>").text("补充介绍") : "",
        descriptions[0] ? $("<p>").html(descriptions[0].replace(/\\n/g, "<br>")) : "",
    );
    for (var i = 3; i >= 1; i--) {
        if (server == i)
            continue;
        if (names[i]) {
            div.find("h5").after($("<p>").addClass("eis-sif-note").text(names[i]));
        }
        if (descriptions[i]) {
            div.find(".header-desc").after($("<p>").addClass("eis-sif-note").html(descriptions[i].replace(/\\n/g, "<br>")));
        }
    }
    if (descriptions[server]) {
        div.find(".header-desc").after($("<p>").html(descriptions[server].replace(/\\n/g, "<br>")));
    }
    return div;
}

$(document).ready(function() {
    init();
    produce(true);
})

var gConfig = $.extend({}, gConfigDefault, {
    fUnit:function(cardID){return null;},
});
