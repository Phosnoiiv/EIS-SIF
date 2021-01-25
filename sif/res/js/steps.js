var table = [];
var appearances = {};
function jimgv(path, desc) {
    var img = $("<img>").attr("src", "/vio/sif/" + path);
    if (desc != undefined) {
        img.attr("alt", desc).attr("title", desc);
    }
    return img;
}
function odigit2(num) {
    return num < 10 ? "0" + num : num;
}
function opsign(num) {
    return num > 0 ? "+" + num : num;
}
function odates(timestamp) {
    var date = new Date(timestamp * 1000);
    var str = odigit2(date.getUTCMonth() + 1) + "/" + odigit2(date.getUTCDate());
    var now = new Date();
    if (date.getUTCFullYear() < now.getFullYear()
        && now.getTime() - date.getTime() > 8640000000) {
        str = date.getUTCFullYear() + "/" + str;
    }
    return str;
}
function ogift(span, keys, callback) {
    var current = 0, count;
    $.each(keys, function(_, key) {
        if (key == current) {
            count++;
            return;
        }
        if (current) {
            span = callback(span, current, count);
        }
        current = key;
        count = 1;
    });
    return callback(span, current, count);
}
function list() {
    $.each(pairs, function(pid, pair) {
        if (!pair)
            return;
        var step = steps[pair[0]];
        var guarantee = $("<span>");
        switch (step[2]) {
            case 4:
                jimgv("icon/s.png", "SR").appendTo(guarantee);
            case 3:
                jimgv("icon/s.png", "SR").appendTo(guarantee);
            case 1:
                jimgv("icon/s.png", "SR").appendTo(guarantee);
                break;
            case 2:
                jimgv("icon/ss.png", "SSR").appendTo(guarantee);
                break;
            case 5:
                jimgv("icon/u.png", "UR").appendTo(guarantee);
                break;
        }
        var gift = $("<span>");
        $.each(gifts[pair[1]], function(type, keys) {
            type = parseInt(type);
            switch (type) {
                case 1000: // Item
                case 8000: // Recovery item
                    gift = ogift(gift, keys, function(span, key, count) {
                        var item = $("<span>").append(jimgv(items[type][key][1] + ".png", items[type][key][0]));
                        if (count > 1) {
                            item.append("×" + count);
                        }
                        return span.append(item);
                    })
                    break;
                case 1001: // Unit
                    var icons = {
                        "35": "s5.png",
                        "41": "u1.png",
                        "42": "u2.png",
                        "43": "u3.png",
                    };
                    gift = ogift(gift, keys, function(span, key, count) {
                        var icon = "icon/" + icons[units[key][2] * 10 + units[key][3]];
                        var unit = $("<span>").append(jimgv(icon)).append(members[units[key][1]]);
                        if (count > 1) {
                            unit.append("×" + count);
                        }
                        unit.addClass("unit-" + units[key][3]).appendTo(span);
                        return span;
                    })
                    break;
                case 5200: // Background
                    var i = keys.length;
                    while (i--) {
                        jimgv("type/5200s.png", "背景").appendTo(gift);
                    }
                    break;
            }
        });
        var strengthRatio = ((pair[3] / step[0] - 1) * 100).toFixed(1);
        var adjustedRatio = ((pair[4] / step[0] - 1) * 100).toFixed(1);
        var contents = [
            ["count-1-" + pid, null, "", "showDetail(1," + pid + ")"],
            ["count-3-" + pid, null, "", "showDetail(3," + pid + ")"],
            ["count-2-" + pid, null, "", "showDetail(2," + pid + ")"],
            [null, step[0] == 50 ? "default" : "special", step[0]],
            [null, step[1] == 11 ? "default" : "special", step[1]],
            [null, "guarantee", guarantee.html()],
            [null, step[3] == 1 ? "default" : "special", step[3]],
            [null, step[4] == 4 ? "default" : "special", step[4]],
            [null, step[5] == 15 ? "default" : "special", step[5]],
            [null, "gift", gift.html()],
            [null, "expect", pair[2].toFixed(3)],
            [null, "expect", step[0] ? Math.round(step[0] / pair[2]) : "---"],
            [null, "value", pair[3].toFixed(2)],
            [null, "value", step[0] ? opsign(strengthRatio) + "%" : "------"],
            [null, "value", pair[4].toFixed(2)],
            [null, "value", step[0] ? opsign(adjustedRatio) + "%" : "------"],
        ];
        var row = $("<tr>").attr("data-id", pid).attr("data-strength-ratio", strengthRatio);
        row.attr("data-adjusted-ratio", adjustedRatio);
        $.each(contents, function(_, c) {
            var cell = $("<td>").html(c[2]);
            if (c[0]) {
                cell.attr("id", c[0]);
            }
            if (c[1]) {
                cell.addClass(c[1]);
            }
            if (c[3]) {
                cell.attr("onclick", c[3]);
            }
            cell.appendTo(row);
        });
        table.push(row);
    });
}
function listSort() {
    var compareFunction = function(row1, row2) {
        return row1.attr("data-id") - row2.attr("data-id");
    }
    switch ($("#sort").val()) {
        case "id-desc":
            compareFunction = function(row1, row2) {
                return row2.attr("data-id") - row1.attr("data-id");
            }
            break;
        case "ur-desc":
            compareFunction = function(row1, row2) {
                return $(row2.children()[10]).text() - $(row1.children()[10]).text();
            }
            break;
        case "strength-desc":
            compareFunction = function(row1, row2) {
                return $(row2.children()[12]).text() - $(row1.children()[12]).text();
            }
            break;
        case "ur-ratio-asc":
            compareFunction = function(row1, row2) {
                var val1 = $(row1.children()[11]).text();
                var val2 = $(row2.children()[11]).text();
                if (val1 == "---")
                    return -1;
                if (val2 == "---")
                    return 1;
                return val1 - val2;
            }
            break;
        case "adjusted-desc":
            compareFunction = function(row1, row2) {
                return $(row2.children()[14]).text() - $(row1.children()[14]).text();
            }
            break;
        case "strength-ratio-desc":
            compareFunction = function(row1, row2) {
                return row2.attr("data-strength-ratio") - row1.attr("data-strength-ratio");
            }
            break;
        case "adjusted-ratio-desc":
            compareFunction = function(row1, row2) {
                return row2.attr("data-adjusted-ratio") - row1.attr("data-adjusted-ratio");
            }
            break;
    }
    table.sort(compareFunction);
    $(".count-open").removeClass("count-open").addClass("count");
    $("#list").empty();
    $.each(table, function(_, row) {
        $("#list").append(row);
    });
}
function filter() {
    $("#detail").remove();
    $(".count-open").removeClass("count-open").addClass("count");
    var dateRange1 = new Date($("#range1").val()).getTime() / 1000;
    var dateRange2 = new Date($("#range2").val()).getTime() / 1000 + 86399;
    appearances = {};
    $.each(stepups, function(id, stepup) {
        if (stepup[4] > dateRange2 || stepup[5] < dateRange1)
            return;
        $.each(patterns[stepup[7]], function(_, pair) {
            if (!appearances[pair])
                appearances[pair] = [null, [], [], []];
            if (appearances[pair][stepup[1]].indexOf(id) < 0) {
                appearances[pair][stepup[1]].push(id);
            }
        });
    });
    for (var i = table.length; i >= 1; i--) {
        var row = $("tr[data-id=" + i + "]");
        if (appearances[i]) {
            for (var j = 1; j <= 3; j++) {
                var count = appearances[i][j].length;
                var cell = $("#count-" + j + "-" + i);
                if (count) {
                    cell.text(count).removeClass("count-zero").addClass("count");
                } else {
                    cell.text("-").removeClass("count").addClass("count-zero");
                }
            }
            row.show();
        } else {
            row.hide();
        }
    }
}
function showDetail(server, pair) {
    var cell = $("#count-" + server + "-" + pair);
    if (cell.hasClass("count-zero"))
        return;
    $("#detail").remove();
    $(".count-open").removeClass("count-open").addClass("count");
    cell.removeClass("count").addClass("count-open");
    var detail = $("<ul>");
    $.each(appearances[pair][server], function(_, stepup) {
        var stepup = stepups[stepup];
        var date = odates(stepup[4]) + "～" + odates(stepup[5]);
        var span = $("<span>").text(date).addClass("date");
        $("<li>").append(span).append(boxNames[stepup[3]]).appendTo(detail);
    });
    var row = $("<tr>").attr("id", "detail");
    $("<td>").attr("colspan", 16).append(detail).appendTo(row);
    $("tr[data-id=" + pair + "]").after(row);
}

$(document).ready(function() {
    list();
    listSort();
    filter();
})
