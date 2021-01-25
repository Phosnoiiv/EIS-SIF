var stepupStorage = {};
function popupStepup(id) {
    var stepup = stepups[id], server = stepup[1];
    var dateOpen = new Date((stepup[5] + serverTimezone[server]) * 1000);
    var dateClose = new Date((stepup[6] + serverTimezone[server]) * 1000);
    var dialog = $("#dialog-stepup");
    dialog.find(".eis-sif-dialog-title").empty().append(
        $("<span>").addClass("eis-sif-tag server-" + stepup[1]).text(serverNameAShort[stepup[1]]),
        $("<span>").text(stepupNames[stepup[3]] + stepupNameSuffixes[stepup[4]]),
    );
    $("#dialog-stepup-main").empty().append(
        $("<p>").text("招募时间：" + dateOpen.getUTCDateTimeFull() + "～" + dateClose.getUTCDateTimeFull()),
        $("<div>").attr("id", "dialog-stepup-intro"),
        $("<div>").attr("id", "dialog-box-highlight"),
        $("<div>").attr("id", "dialog-box-intro-extra"),
    );
    dialog.find(".eis-sif-dialog-tag").empty();
    if (stepup[10]) {
        var group = memberGroups[stepup[10]];
        if (group[2]) {
            dialog.find(".eis-sif-dialog-tag").append(qImg(group[2], group[0]));
        }
    }
    if (stepup[11]) {
        dialog.find(".eis-sif-dialog-tag").append($("<span>").addClass("eis-sif-tag series-" + unitSeries[stepup[11]][0]).text(unitSeries[stepup[11]][4]));
    }
    $.each(stepupSettings[stepup[8]], function(num, setting) {
        var sid = $.isArray(setting) ? setting[0] : setting;
        var gid = $.isArray(setting) ? setting[1] : 0;
        var step = steps[sid], intro = "";
        if (step[0] != 50) {
            intro += step[0] > 0 ? (step[0] + " 心") : "<em>免费</em>";
        }
        if (step[1] != 11) {
            intro += step[1] > 1 ? (" " + step[1] + " 连") : "单抽";
        }
        var allGuarantee = step[3] ? step[3] == 100 ? 1 : step[3] + step[4] == 100 ? 2 : step[3] + step[4] + step[5] == 100 ? 3 : 0 : 0;
        intro += step[2] > 0 ? ("必得 " + ["", "1SR", "<em>1SSR</em>", "2SR", "3SR", "<em>1UR!!!</em>"][step[2]]) : allGuarantee ? "" : "无保底";
        if (step[3]) {
            if (allGuarantee) {
                intro += "<em>" + (step[1] > 1 ? "全部 " : " ") + ["", "UR ", "SSR 以上", "SR 以上"][allGuarantee] + "确定</em>";
            }
            intro += "，<em>UR " + step[3] + "%</em>，SSR " + step[4] + "%，SR " + step[5] + "%";
        }
        if (gid) {
            intro += "，赠送 <span id='stepup-gift-" + num + "'></span>";
        }
        label = "第 " + (num + 1) + " 次" + (num == stepupSettings[stepup[8]].length - 1 && !stepup[7] && !stepup[9] ? "以后" : "");
        $("#dialog-stepup-intro").append($("<span>").append(
            $("<span>").text(label),
            $("<span>").html($.trim(intro)),
        ));
        if (gid) {
            $.each(stepupGifts[gid], function(type, content) {
                if ($.isNumeric(content)) {
                    addGift(num, server, type, 0, content);
                } else if ($.isArray(content)) {
                    $.each(content, function(_, key) {
                        addGift(num, server, type, key, 1);
                    });
                } else if ($.isPlainObject(content)) {
                    $.each(content, function(key, amount) {
                        addGift(num, server, type, key, amount);
                    });
                }
            });
        }
    });
    var key = stepup[0] + "-" + server + "-" + stepup[2];
    if (key in stepupStorage) {
        popupStepupFill(id, stepupStorage[key]);
        return;
    }
    $.get(
        "/sif/interface/box.php",
        {b:stepup[0], s:server, r:stepup[2]},
    ).done(function(data) {
        stepupStorage[key] = data;
        popupStepupFill(id, data);
    }).fail(function() {
        $("<p>").text("从服务器获取数据时出错，部分信息无法显示。").appendTo("#dialog-box-highlight");
        popupStepupFinal(id);
    });
}
function popupStepupFill(id, data) {
    var setting = stepupSettings[stepups[id][8]];
    if (stepups[id][9]) {
        $("<p>").text("抽完 " + setting.length + " 次后，返回第 " + stepups[id][9] + " 次继续。").appendTo("#dialog-box-highlight");
    }
    if (data.stepup[0]) {
        $("<p>").text("本阶梯含有 BOX 限定卡，单张限定卡在 UR 内的出现率为 " + data.stepup[0] + "%。").appendTo("#dialog-box-highlight");
    }
    if (data.stepup[1] != null) {
        $("<p>").text("本阶梯中技能为“得分提升”的 UR 占比按 " + data.stepup[1] + "% 计算。").appendTo("#dialog-box-highlight");
    }
    $.each(data.stepup[3].split('\\n'), function(_, intro) {
        $("<p>").text(intro).appendTo("#dialog-box-intro-extra");
    });
    var tr = $("<tr>").append(
        $("<th>"),
        $("<th>").text("消费").append(img("item/ls", "Loveca Stone")),
        $("<th>").text("0UR"),
        $("<th>").text("1UR"),
        $("<th>").text("2UR"),
        $("<th>").text("3UR"),
    );
    if (!data.stepup[2]) {
        tr.append(
            $("<th>").text("强度收益"),
        );
    }
    $("#dialog-stepup-main").append($("<table>").addClass("eis-sif-table").append(tr));
    for (var i = 0, j = 1, s = setting.length, l = 0; i < data.stepup[4].length; i++, j++) {
        if (j > s) {
            j = stepups[id][9] ? stepups[id][9] : s;
        }
        var sid = $.isArray(setting[j - 1]) ? setting[j - 1][0] : setting[j - 1];
        l += steps[sid][0];
        var profit1 = data.stepup[4][i][0] - l;
        var tr = $("<tr>").append(
            $("<td>").text("抽 " + (i + 1) + " 次"),
            $("<td>").text(l),
            $("<td>").text(data.stepup[4][i][2][0].toPercent(1)),
            $("<td>").text(data.stepup[4][i][2][1].toPercent(1)),
            $("<td>").text(data.stepup[4][i][2][2].toPercent(1)),
            $("<td>").text(data.stepup[4][i][2][3].toPercent(1)),
        );
        if (!data.stepup[2]) {
            tr.append(
                $("<td>").text(profit1.toFixed(2) + " (" + (profit1 / l).toPercent(0) + ")"),
            );
        }
        $("#dialog-stepup-main table").append(tr);
    }
    popupStepupFinal(id);
}
function popupStepupFinal(id) {
    $("#dialog-stepup").dialog("open").dialog("option", {position:{of:window}, resizable:false, draggable:false, buttons:[
        {text:"显示相关阶梯", click:function(){relatedStepup(id);$(this).dialog("close");}},
        {text:"关闭", click:function(){$(this).dialog("close");}},
    ]});
}
function addGift(stepNum, server, type, key, amount) {
    var span = $("<span>");
    if (type == 1001) {
        img("unit/" + key, "").addClass("unit").appendTo(span);
    } else {
        var image = items[type][key][server + 2];
        img(image, items[type][key][server - 1]).appendTo(span);
    }
    if (amount > 1) {
        span.append("×" + amount);
    }
    $("#stepup-gift-" + stepNum).append(span);
}

$(window).resize(function() {
    if ($(window).width() >= 600) {
        $("#dialog-stepup").dialog("option", "width", 600);
    } else {
        $("#dialog-stepup").dialog("option", "width", 300);
    }
});
