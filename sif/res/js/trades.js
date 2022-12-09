var server, asideTop;
function init() {
    if (inAprilFools) {
        items[1000][1][3] = items[1000][1][4] = items[1000][1][5] = "../sifas/ticket/1";
        items[1000][1200][3] = items[1000][1200][4] = items[1000][1200][5] = "../sifas/item/k";
        items[3000][0][3] = items[3000][0][4] = items[3000][0][5] = "../sifas/item/g";
        items[3001][0][3] = items[3001][0][4] = items[3001][0][5] = "../sifas/item/s";
        items[3002][0][3] = items[3002][0][4] = items[3002][0][5] = "../sifas/item/e";
        items[3006][2][3] = items[3006][2][4] = items[3006][2][5] = "../sifas/exchange/g0";
        items[8000][1][3] = items[8000][1][4] = items[8000][1][5] = "../sifas/item/l1";
    }
    $.each(trades, function(id, trade) {
        if (!trade)
            return;
        $("<div>").addClass("eis-sif-gallery-item" + (trade[7] ? "" : " text")).append(
            trade[7] ? qImg(trade[7], trade[2]) : $("<span>").text(trade[2]),
        ).attr("onclick", "produce(" + id + ")").appendTo("#trades");
    });
}
function produce(tradeId) {
    var trade = trades[tradeId];
    server = trade[0];
    $("#trade-detail").empty().append(
        $("<h2>").text(trade[1]),
        server != 3 ? $("<p>").text(trade[2]) : "",
        $("<div>").addClass("eis-sif-timetip on-background").append(
            "道具获取期间：" + serverDate(trade[3], server).getUTCDateTimeFull() + "～" + serverDate(trade[5], server).getUTCDateTimeFull(),
            $("<span>").addClass("eis-sif-countdown").attr("data-time", trade[5]).attr("data-text-ended", "已结束"),
        ),
        $("<div>").addClass("eis-sif-timetip on-background").append(
            "道具交换期间：" + serverDate(trade[4], server).getUTCDateTimeFull() + "～" + serverDate(trade[6], server).getUTCDateTimeFull(),
            $("<span>").addClass("eis-sif-countdown").attr("data-time", trade[6]).attr("data-text-ended", "已结束"),
        ),
    );
    $("#trade-tabs-nav, #currencies").empty();
    $("#categories>div").remove();
    var listCurrencies = {}, currencyCount = 0;
    $.each(trade[8], function(tradeId, category) {
        $.each(currencies[category[5]], function(num, currency) {
            var type = currency[0], key = currency[1];
            if (listCurrencies[type] && listCurrencies[type][key])
                return;
            listCurrencies[type] = listCurrencies[type] || [];
            listCurrencies[type][key] = ++currencyCount;
        $("<div>").addClass("currency").append(
                gItem(type, key, server, 0, {}, gConfig),
                $("<div>").attr("id", "need-" + currencyCount).addClass("need"),
        ).appendTo("#currencies");
        });
    });
    $.each(trade[8], function(id, category) {
        $("<li>").append($("<a>").text(category[1] || category[0]).attr("href", "#tab-category-" + id)).appendTo("#trade-tabs-nav");
        var tr = $("<tr>").append(
            $("<th>"),
            $("<th>"),
            $("<th>").text("交换限制"),
        );
        $.each(currencies[category[5]], function(num, currency) {
            var type = currency[0], key = currency[1];
            $("<th>").append(gItem(type, key, server, 0, {d:true}, gConfig)).appendTo(tr);
        });
        tr.append(
            $("<th>").text("交换计划"),
        );
        $("<div>").attr("id", "tab-category-" + id).append(
            category[1] ? $("<h3>").text(category[0]) : "",
            category[2] || category[3] ? $("<div>").addClass("eis-sif-timetip").text(
                "交换期间：" + serverDate(category[2] || trade[4], server).getUTCDateTimeFull() + "～" + serverDate(category[3] || trade[6], server).getUTCDateTimeFull(),
            ) : "",
            category[6] ? $("<p>").html(category[6]) : "",
            $("<table>").append(tr).attr("id", "category-" + id).addClass("eis-sif-table"),
        ).appendTo("#categories");
        var collection = [];
        $.each(category[4], function(_, group) {
            $.each(goods[group[0]], function(__, goods1) {
                collection.push(goods1.concat(group[1]));
            });
        });
        collection.sort(function(g1, g2) {
            return g1[5] - g2[5];
        });
        $.each(collection, function(_, goods1) {
            var type = goods1[0], key = goods1[1];
            var tr = $("<tr>").addClass("goods").append(
                $("<td>").append(gItem(type, key, server, goods1[2], {d:true}, gConfig)),
                $("<td>").append(gItemName(type, key, server, gConfig) + ([5100, 5200].indexOf(type) < 0 ? "×" + goods1[2] : "")).addClass(type == 1001 ? "eis-sif-text attribute-" + units[key][3] : ""),
                $("<td>").append(goods1[3] ? goods1[3] + " 次" : "无限制").addClass("limit" + (goods1[3] ? "" : " unlimited")),
            ).attr("data-type", type);
            if ($.isArray(goods1[6])) {
                $.each(goods1[6], function(num, cost) {
                    var currency = currencies[category[5]][num], type = currency[0], key = currency[1];
                    $("<td>").text(cost).addClass("cost").attr("data-currency", listCurrencies[type][key]).appendTo(tr);
                });
            } else {
                var currency = currencies[category[5]][0], type = currency[0], key = currency[1];
                $("<td>").text(goods1[6]).addClass("cost").attr("data-currency", listCurrencies[type][key]).appendTo(tr);
            }
            var input = $("<input>").attr("type", "number").attr("min", 0);
            if (goods1[3]) {
                input.attr("max", goods1[3]);
            }
            tr.append(
                $("<td>").append(
                    input,
                    $("<button>").text("清").click(function() {
                        $(this).siblings("input[type=number]").val(0);
                        calculate();
                    }),
                    goods1[3] ? $("<button>").text("全").click(function() {
                        $(this).siblings("input[type=number]").val(goods1[3]);
                        calculate();
                    }) : "",
                ),
            );
            $("#category-" + id).append(tr);
        });
    });
    $("input[type=number]").change(calculate);
    setPlanFull();
    enableCountdown();
    $("aside").removeClass("eis-sif-hidden");
    $(".plan").each(function() {
        if ($("tr.goods[data-type=" + $(this).attr("data-type") + "]").length) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
    $("#categories").tabs().tabs("refresh");
    $(window).resize();
    $(window).scrollTop($("#trade-detail").position().top - 15);
    var _paq = window._paq || [];
    _paq.push(["setCustomVariable", 1, "Trade Name", trade[1], "page"]);
    _paq.push(["trackEvent", "Trades (SIF)", "Switch Trade"]);
}
function calculate() {
    var currencyCount = $(".currency").length;
    var sums = [];
    for (var i = 1; i <= currencyCount; i++) {
        sums[i] = 0;
    }
    $("tr.goods").each(function() {
        var amount = $(this).find("input[type=number]").val();
        for (var i = 1; i <= currencyCount; i++) {
            sums[i] += $(this).children("[data-currency=" + i + "]").text() * amount;
        }
    });
    for (var i = 1; i <= currencyCount; i++) {
        $("#need-" + i).text(sums[i]);
    }
}
function setPlanFull(type) {
    $("tr.goods" + (type ? "[data-type=" + type + "]" : "") + " input[type=number]").val(function() {
        if ($(this).attr("max"))
            return $(this).attr("max");
        return 0;
    });
    calculate();
}
function setPlanEmpty(type) {
    $("tr.goods" + (type ? "[data-type=" + type + "]" : "") + " input[type=number]").val(0);
    calculate();
}

$(window).resize(function() {
    if ($("aside:not(.positioned)").length) {
        asideTop = $("aside").offset().top;
    } else if ($("aside").length) {
        $("aside").css("top", "");
        asideTop = $("aside").offset().top;
        $(window).scroll();
    }
});
$(window).scroll(function() {
    if ($(window).scrollTop() > asideTop - 20) {
        $("aside").addClass("positioned").offset({top:$(window).scrollTop() + 20});
    } else if ($("aside").hasClass("positioned")) {
        $("aside").removeClass("positioned").css("top", "");
    }
});
$(document).ready(function() {
    init();
    $("aside, #categories").tooltip({items:".eis-sif-item", content:function() {
        return gItemTooltip(parseInt($(this).attr("data-type")), $(this).attr("data-key"), server, gConfig);
    }, position:{my:"left-15 top-5", at:"right bottom"}});
});

var gConfig = $.extend({}, gConfigDefault, {
    itemNames:[null,0,1,2], itemDesc:[null,null,null,null], itemImages:[null,3,4,5],
    unitMember:1, unitRarity:2, unitNames:[null,4,5,6],
    memberNames:[null,0,1,2],
    titleNames:[null,3,4,5], titleDesc:[null,null,null,null], titleIntro:6, titleImages:[null,0,1,2],
    backgroundNames:[null,1,2,3], backgroundDesc:[null,null,null,null], backgroundIntro:4, backgroundMotion:0,
    SINames:[null,1,2,3], SIImage:4, SIString:5, SIValue:6,
});
