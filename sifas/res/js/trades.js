var asideTop;
function img(file, desc) {
    return $("<img>").attr("src", "/vio/sifas/" + file + ".png").attr("title", desc).attr("alt", desc);
}
function init() {
    if (inAprilFools) {
        items[1][0][1] = "../sif/item/l";
        items[4][0][1] = "../sif/item/f";
        items[10][0][1] = "../sif/item/g";
        items[17][1300][1] = "../sif/recovery/1";
    }
    $.each(trades, function(id, trade) {
        if (!trade)
            return;
        $("<div>").addClass("eis-sif-gallery-item").append(
            qASImg(trade[9], trade[2]),
        ).attr("onclick", "produce(" + id + ")").prependTo("#trades");
    });
}
function produce(tradeId) {
    var trade = trades[tradeId];
    var server = trade[0];
    $("#trade-detail").empty().append(
        $("<h2>").text(trade[1]),
        $("<p>").text(trade[2]),
        $("<div>").addClass("eis-sif-timetip on-background").append(
            "道具获取期间：" + serverDate(trade[3], server).getUTCDateTimeFull() + "～" + serverDate(trade[5], server).getUTCDateTimeFull(),
            $("<span>").addClass("eis-sif-countdown").attr("data-time", trade[5]).attr("data-text-ended", "已结束"),
        ),
        $("<div>").addClass("eis-sif-timetip on-background").append(
            "道具交换期间：" + serverDate(trade[4], server).getUTCDateTimeFull() + "～" + serverDate(trade[6], server).getUTCDateTimeFull(),
            $("<span>").addClass("eis-sif-countdown").attr("data-time", trade[6]).attr("data-text-ended", "已结束"),
        ),
    );
    $("#categories, #currencies").empty();
    $.each(currencies[trade[7]], function(num, currency) {
        var item = getItem(currency[0], currency[1], server);
        $("<div>").addClass("currency").append(
            img(item[0], item[1]),
            $("<div>").attr("id", "need-" + num).addClass("need"),
        ).appendTo("#currencies");
    });
    $.each(trade[8], function(id, category) {
        var tr = $("<tr>").append(
            $("<th>"),
            $("<th>"),
            $("<th>").text("交换限制"),
        );
        $.each(currencies[trade[7]], function(num, currency) {
            var item = getItem(currency[0], currency[1], server);
            $("<th>").append(img(item[0], item[1])).appendTo(tr);
        });
        tr.append(
            $("<th>").text("交换计划"),
        );
        $("<div>").append(
            $("<h3>").text(category[0]),
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
            var type = goods1[0];
            var item = getItem(goods1[0], goods1[1], server);
            var tr = $("<tr>").addClass("goods").append(
                $("<td>").append(img(item[0], item[1])),
                $("<td>").append(item[1] + "×" + goods1[2]),
                $("<td>").append(goods1[3] ? goods1[3] + " 次" : "无限制").addClass("limit" + (goods1[3] ? "" : " unlimited")),
            ).attr("data-type", type);
            if ($.isArray(goods1[6])) {
                $.each(goods1[6], function(num, cost) {
                    $("<td>").text(cost).addClass("cost").attr("data-currency", num).appendTo(tr);
                });
            } else {
                $("<td>").text(goods1[6]).addClass("cost").attr("data-currency", 0).appendTo(tr);
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
    $("#categories, aside").removeClass("eis-sif-hidden");
    $(".plan").each(function() {
        if ($("tr.goods[data-type=" + $(this).attr("data-type") + "]").length) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
    $(window).resize();
    $(window).scrollTop($("#trade-detail").offset().top - 15);
    var _paq = window._paq || [];
    _paq.push(["setCustomVariable", 1, "Trade Name", trade[1], "page"]);
    _paq.push(["trackEvent", "Trades (SIFAS)", "Switch Trade"]);
}
function calculate() {
    var currencyCount = $(".currency").length;
    var sums = [];
    for (var i = 0; i < currencyCount; i++) {
        sums[i] = 0;
    }
    $("tr.goods").each(function() {
        var amount = $(this).find("input[type=number]").val();
        for (var i = 0; i < currencyCount; i++) {
            sums[i] += $(this).children("[data-currency=" + i + "]").text() * amount;
        }
    });
    for (var i = 0; i < currencyCount; i++) {
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
function getItem(type, key, server) {
    if (type == 3) {
        return ["card/" + key, ""];
    } else {
        return [items[type][key][server], items[type][key][server - 1]];
    }
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
});
