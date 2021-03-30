var playdata, state = {}, boxStorage = {}, unitStorage = {}, skillStorage = {};
var boxGuarantees = [null, [3, 1], [5, 1], [3, 2], [3, 3], [4, 1]];
function startConfirm() {
    showDialogConfirm($("#dialog-confirm-start"), start);
}
function start() {
    showDialogMessage($("#dialog-message-start"), $.noop);
    $("#panel-box-main").addClass("eis-sif-hidden");
    $("#maintab").tabs({disabled:[1,3]}).tabs("option", "active", 2);
    playdata={server:parseInt($("#server").val()), pocket:{}, income:{}, progress:{}, selection:{}, stamps:{}, knapsacks:{}};
    $.each(items, function(type, typeItems) {
        $.each(typeItems, function(key, item) {
            playdata.pocket[type + "-" + key] = 0;
        });
    });
    $.each(freeItems, function(index, freeItem) {
        if (!freeItem[1+playdata.server]) return;
        giveItem(freeItem[0], freeItem[1], freeItem[1+playdata.server]);
    });
    showIncome("获得了以下道具！");
    $("#boxes").empty();
    var listBoxes = [];
    $.each(normalBoxes[playdata.server], function(index, box) {
        listBoxes.push([0, index].concat(box));
        if (box[9]) {
            playdata.stamps[$.isArray(box[0]) ? box[0][0] + "-" + box[0][1] : box[0]] = 0;
        }
    });
    $.each(stepups[playdata.server], function(index, stepup) {
        listBoxes.push([1, index].concat(stepup));
    });
    $.each(knapsacks[playdata.server], function(index, knapsack) {
        listBoxes.push([2, index].concat(knapsack));
        playdata.knapsacks[$.isArray(knapsack[0]) ? knapsack[0][0] + "-" + knapsack[0][1] : knapsack[0]] = knapsackSettings[knapsack[7]].slice();
    });
    $.each(bags[playdata.server], function(index, bag) {
        listBoxes.push([3, index].concat(bag));
    });
    listBoxes.sort(function(box1, box2) {
        return box1[8] - box2[8];
    });
    $.each(listBoxes, function(index, box) {
        $("<li>").append(
            $("<span>").addClass("box-clickable eis-sif-text category-" + box[7]).text(box[3]).attr("onclick", "showBox(" + box[0] + "," + box[1] + ")"),
        ).appendTo("#boxes");
        playdata.progress[$.isArray(box[2]) ? box[2][0] + "-" + box[2][1] : box[2]] = 0;
    });
    refreshPageBar();
}
function showBox(boxType, index) {
    var rarityImages = [null, "n", "m/r", "m/s", "m/u", "m/ss", "ul", "us"];
    $("#box-currencies, #box-series, #box-ad, #box-selection, #box-buttons, .sheet-contents").empty();
    $("#box-extra>span").not("#box-lineup").hide();
    $("#box-buttons").show();
    var box;
    switch (boxType) {
        case 0:
            box = normalBoxes[playdata.server][index];
            var costs = boxCosts[box[7]], template = boxTemplates[box[8]];
            $("#box-rarities table").empty().append(function() {
                var a = [$("<tr><td></td></tr>"), $("<tr><th>提供比例</th></tr>")];
                $.each([[0, 4], [1, 5], [2, 3], [3, 2], [-1, 1]], function(index, config) {
                    var rate = config[0] < 0 ? 100 - template[0] - template[1] - template[2] - template[3] : template[config[0]];
                    if (!(rate > 0))
                        return;
                    $("<td>").append(qImg("icon/" + rarityImages[config[1]])).appendTo(a[0]);
                    $("<td>").text(rate.toFixed(1) + "%").appendTo(a[1]);
                });
                return a;
            });
            $.each(costs, function(costIndex, cost) {
                qBoxButton(cost[0], cost[1], cost[2], cost[3], cost[3] >= template[5] ? template[4] : 0, box[9] && cost[3] == 11).appendTo("#box-buttons");
            });
            break;
        case 1:
            box = stepups[playdata.server][index];
            var setting = stepupSettings[box[7]];
            $("#box-rarities table").empty().append(function() {
                var tr = $("<tr><td></td></tr>");
                $("<td>").append(qImg("item/ls")).appendTo(tr);
                $.each([4, 5, 3, 2], function(index, config) {
                    $("<td>").append(qImg("icon/" + rarityImages[config])).appendTo(tr);
                });
                return tr;
            });
            $.each(setting, function(index, stepSetting) {
                var stepID, giftID, rateUR, rateSSR, rateSR;
                if ($.isArray(stepSetting)) {
                    stepID = stepSetting[0]; giftID = stepSetting[1];
                } else {
                    stepID = stepSetting; giftID = 0;
                }
                var step = steps[stepID];
                if (step[3]) {
                    rateUR = step[3]; rateSSR = step[4]; rateSR = step[5];
                } else {
                    rateUR = 1; rateSSR = 4; rateSR = 15;
                }
                $("<tr>").attr("data-step", index + 1).append(
                    $("<th>").text("第 " + (index + 1) + " 次"),
                    $("<td>").text(step[0]),
                    $("<td>").text(rateUR.toFixed(1) + "%"),
                    $("<td>").text(rateSSR.toFixed(1) + "%"),
                    $("<td>").text(rateSR.toFixed(1) + "%"),
                    $("<td>").text((100 - rateUR - rateSSR - rateSR).toFixed(1) + "%"),
                ).appendTo("#box-rarities table");
            });
            $(".dialog-scout-stepup-num span:nth-of-type(2)").text(setting.length);
            break;
        case 2:
            box = knapsacks[playdata.server][index];
            var setting = knapsackSettings[box[7]], costs = boxCosts[box[8]];
            $("#box-rarities table").empty().append(function() {
                var a = [$("<tr><th class='highlight'></th></tr>"), $("<tr><th>剩余数量</th></tr>"), $("<tr><th>当前出率</th></tr>")];
                $.each([[0, 7], [1, 6], [2, 4], [3, 5], [4, 3], [5, 2]], function(index, config) {
                    var capacity = setting[config[0]];
                    if (!(capacity > 0))
                       return;
                    $("<td>").append(qImg("icon/" + rarityImages[config[1]])).appendTo(a[0]);
                    $("<td>").append(
                        $("<span>").attr("data-remain", config[0]),
                        $("<span>").text("/" + capacity).addClass("capacity"),
                    ).appendTo(a[1]);
                    $("<td>").attr("data-remain-rate", config[0]).appendTo(a[2]);
                });
                return a;
            });
            $.each(costs, function(costIndex, cost) {
                qBoxButton(cost[0], cost[1], cost[2], cost[3], 0).appendTo("#box-buttons");
            });
            $("#knapsack-reset").show();
            break;
        case 3:
            box = bags[playdata.server][index];
            var setting = bagSettings[box[7]], cost = boxCosts[box[8]][0];
            $("#box-rarities table").empty().append(function() {
                var tr = $("<tr><td></td></tr>");
                $.each([4, 5, 3, 2], function(index, config) {
                    $("<td>").append(qImg("icon/" + rarityImages[config])).appendTo(tr);
                });
                return tr;
            });
            $.each(setting, function(index, bagSetting) {
                $("<tr>").append(
                    $("<th>").text("第 " + (index + 1) + " 位"),
                    $("<td>").text(bagSetting[0] ? bagSetting[0].toFixed(1) + "%" : "-"),
                    $("<td>").text(bagSetting[1] ? bagSetting[1].toFixed(1) + "%" : "-"),
                    $("<td>").text(bagSetting[2] ? bagSetting[2].toFixed(1) + "%" : "-"),
                    $("<td>").text(bagSetting[3] ? bagSetting[3].toFixed(1) + "%" : "-"),
                ).appendTo("#box-rarities table");
            });
            qBoxButton(cost[0], cost[1], cost[2], cost[3], 0, false).appendTo("#box-buttons");
            break;
    }
    state.boxType = boxType;
    state.boxLocal = box;
    var boxID = $.isArray(box[0]) ? box[0][0] : box[0], runID = $.isArray(box[0]) ? box[0][1] : 0;
    state.boxKey = boxID + (runID ? "-" + runID : "");
    state.boxRecordKey = boxID + "-" + playdata.server + "-" + runID;
    switch (boxType) {
        case 1:
            refreshStepup();
            break;
    }
    $("#box-name").removeClass().addClass("eis-sif-text category-" + box[5]);
    if (box[2]) {
        $("#box-time").hide();
    } else {
        $("#box-time").text("游戏内开放期间：" + serverDate(box[3], playdata.server).getUTCDateTimeFull() + "～" + (box[4] ? serverDate(box[4], playdata.server).getUTCDateTimeFull() : "无期限")).show();
    }
    if (boxType == 0 && box[9]) {
        var sheet = boxStampSheets[box[9]];
        $.each(sheet.slice(2), function(stampIndex, stampID) {
            var stamp = boxStamps[stampID];
            var stampItem = stamp.i ? gItem(stamp.i[0], stamp.i[1], playdata.server, stamp.i[2], {o:true}, gConfig) : $("<span>").append(qImg(stamp.m[playdata.server - 1]));
            stampItem.addClass("stamp").attr("data-num", stampIndex + 1).appendTo(".sheet-contents");
        });
        $(".sheet-loop-note").text("※ " + (sheet[0] ? "获得全部印章后重复第 " + sheet[0] + "～" + sheet[1] + " 个印章的循环。" : "各印章仅可获得一次。"));
        $("#box-sheet").show();
        refreshSheet();
    }
    $(".sif-box-button").each(function() {
        var type = $(this).attr("data-type"), key = $(this).attr("data-key");
        if ($(".box-currency[data-type=" + type + "][data-key=" + key + "]").length)
            return;
        var item = items[type][key];
        $("<div>").addClass("box-currency").attr("data-type", type).attr("data-key", key).append(
            qImg(item[playdata.server - 1] || item[0]),
            $("<span>").text(playdata.pocket[type + "-" + key]),
        ).appendTo("#box-currencies");
    });
    $("#panel-box-main").removeClass().addClass("category-" + box[5]);
    var key = boxID + "-" + playdata.server + "-" + runID;
    if (boxStorage[key]) {
        state.boxRemote = boxStorage[key];
        showBoxFin();
        return;
    }
    $.getJSON("/sif/interface/box.php", {b:boxID, s:playdata.server, r:runID, m:"s"}, function(data) {
        state.boxRemote = boxStorage[key] = data;
        showBoxFin();
    }).fail(function() {
        $("#panel-box-main").addClass("eis-sif-hidden");
    });
}
function showBoxFin() {
    $("#box-name").text(state.boxRemote.box[0]);
    var seriesCount = 0;
    $.each(state.boxRemote.box[6], function(seriesIndex, seriesID) {
        for (var i = 0; i < 2; i++) {
            var seriesImage = series[seriesID][3 + i];
            if (!seriesImage)
                break;
            $("<div>").addClass("sif-album-collection").attr("data-category", state.boxLocal[5]).attr("data-direction", (seriesCount++) % 2 + 1).append(
                qImg("series/" + seriesImage),
                $("<span>").text(series[seriesID][playdata.server - 1]),
            ).appendTo("#box-series");
        }
    });
    var ads = state.boxRemote.box[4].split("\\n").concat(state.boxRemote.box[5].split("\\n"));
    $.each(ads, function(index, ad) {
        $("<p>").html(ad).addClass(ad.substring(0, 1) == "※" ? "eis-sif-note" : "").appendTo("#box-ad");
    });
    state.boxSelectType = state.boxRemote.box[3];
    switch (state.boxType) {
        case 0:
            if (state.boxSelectType) {
                playdata.selection[state.boxKey] = 0;
                $("#box-select-member").show();
                $("#box-buttons").hide();
            }
            break;
        case 2:
            refreshKnapsack();
            break;
    }
    $(window).scrollTop($("#box-name").offset().top - 100);
    $(".dialog-box-title").empty().append(
        state.boxLocal[2] ? "" : $("<span>").addClass("eis-sif-tag server-" + playdata.server).text(serverNameAShort[playdata.server]),
        $("<span>").addClass("eis-sif-text category-" + state.boxLocal[5]).text(state.boxRemote.box[0]),
    )
    var unitIDs = [], sheetID;
    if (sheetID = state.boxType == 0 ? state.boxLocal[9] : 0) {
        $.each(boxStampSheets[sheetID].slice(2), function(stampIndex, stampID) {
            var stamp = boxStamps[stampID];
            if (stamp.i && stamp.i[0] == 1001 && !unitStorage[stamp.i[1]]) {
                unitIDs.push(stamp.i[1]);
            }
        });
    }
    for (var i = 1; i <= 7; i++)
        $.each(state.boxRemote.contents[i], function(groupIndex, group) {
            $.each(group, function(unitIndex, unitID) {
                if (!unitStorage[unitID]) {
                    unitIDs.push(unitID);
                }
            });
        });
    if (!unitIDs.length)
        return;
    $.post("/sif/interface/units.php", {u:unitIDs.join("a")}, function(unitData) {
        $.each(unitData.units, function(unitID, unit) {
            unitStorage[unitID] = unit;
        });
        $.each(unitData.skills, function(skillID, skill) {
            skillStorage[skillID] = skill;
        });
    }).fail(function() {
        $("#panel-box-main").addClass("eis-sif-hidden");
    });
    var _paq = window._paq || [];
    _paq.push(["setCustomVariable", 1, "Box Key", state.boxRecordKey, "page"]);
    _paq.push(["trackEvent", "Sim Boxes", "Switch Box"]);
}
function refreshPocket() {
    $(".box-currency").each(function() {
        $(this).find("span").text(playdata.pocket[$(this).attr("data-type") + "-" + $(this).attr("data-key")]);
    });
}
function refreshStepup() {
    var progress = playdata.progress[state.boxKey];
    var stepCount = stepupSettings[state.boxLocal[7]].length;
    var endless = state.boxLocal[8];
    var situation;
    if (progress < stepCount) {
        situation = progress + 1;
    } else if (endless < 0) {
        situation = (progress - stepCount) % (stepCount + endless + 1) - endless;
    } else if (endless == 0) {
        situation = stepCount;
    } else {
        situation = 0;
    }
    if (endless >= 0) {
        $("#stepup-reset").show();
        if ((situation == stepCount && endless == 0) || situation == 0) {
            $("#stepup-reset").button("enable");
        } else {
            $("#stepup-reset").button("disable");
        }
    } else {
        $("#stepup-reset").hide();
    }
    state.stepupSituation = situation;
    $("#box-buttons").empty();
    $("tr[data-step]").removeClass("highlight");
    if (situation > 0) {
        var stepSetting = stepupSettings[state.boxLocal[7]][situation - 1];
        var stepID, giftID;
        if ($.isArray(stepSetting)) {
            stepID = stepSetting[0]; giftID = stepSetting[1];
        } else {
            stepID = stepSetting; giftID = 0;
        }
        var step = steps[stepID];
        $("tr[data-step=" + situation + "]").addClass("highlight");
        qBoxButton(3001, 0, step[0], step[1], step[2]).appendTo("#box-buttons");
    } else {
        qBoxButton(3001, 0, 5, 1, 0).removeAttr("onclick").addClass("disabled").appendTo("#box-buttons");
    }
}
function refreshKnapsack() {
    var selection = playdata.selection[state.boxKey];
    var selectType = state.boxRemote.box[3];
    var selectNeeded = selectType && !selection;
    var totalRemain = 0;
    for (var i = 0; i <= 5; i++) {
        var remain = playdata.knapsacks[state.boxKey][i];
        totalRemain += remain;
        $("[data-remain=" + i + "]").text(selectNeeded && i == 0 ? "-" : remain);
    }
    $("#box-rarities th.highlight").text("剩余 " + totalRemain);
    $("#box-buttons").empty();
    $("[data-remain-rate]").text(function() {
        return totalRemain > 0 ? (playdata.knapsacks[state.boxKey][$(this).attr("data-remain-rate")] / totalRemain).toPercent(1) : "-";
    });
    if (selection) {
        if (selectType == 1) {
            $("#box-selection").empty().append(
                $("<span>").text("选择的成员"),
                qImg("member/s" + selection, members[selection][playdata.server - 1]).addClass("selection-member"),
            );
        } else {
            $("#box-selection").empty().append(
                $("<span>").text("选择的社员"),
            );
        }
    }
    if (selectNeeded) {
        qImg("button/s/s1").addClass("box-button-select").attr("onclick", selectType == 1 ? "selectMember()" : "selectUnit()").appendTo("#box-buttons");
    } else if (totalRemain == 0) {
        qBoxButton(3001, 0, 5, 1, 0).removeAttr("onclick").addClass("disabled").appendTo("#box-buttons");
    } else {
        $.each(boxCosts[state.boxLocal[8]], function(costIndex, cost) {
            if (totalRemain < cost[3])
                return;
            qBoxButton(cost[0], cost[1], cost[2], cost[3], 0).appendTo("#box-buttons");
        });
    }
}
function refreshSheet() {
    var progress = playdata.stamps[state.boxKey];
    var sheet = boxStampSheets[state.boxLocal[9]];
    var sheetLength = sheet.length - 2;
    var currentMark = progress < sheetLength ? progress : (progress - sheetLength) % (sheet[1] - sheet[0] + 1) + sheet[0] - 1;
    for (var i = 1; i <= sheetLength; i++) {
        if (i <= currentMark || (progress > sheetLength && i > sheet[1]) || (progress >= sheetLength && currentMark == sheet[0] - 1 && i == (progress > sheetLength ? sheet[1] : sheetLength))) {
            $(".stamp[data-num=" + i + "]").addClass("get");
        } else {
            $(".stamp[data-num=" + i + "]").removeClass("get");
        }
    }
    var stamp = state.stamp = boxStamps[sheet[currentMark + 2]];
    var ad;
    if (stamp.g) {
        var guarantee = boxGuarantees[stamp.g];
        ad = "必得 " + rarityNames[guarantee[0]] + (guarantee[0] == 4 ? "" : " 或以上") + "×" + guarantee[1];
    } else if (stamp.p) {
        ad = "必得特定社员×" + stamp.p[1];
    }
    $(".sif-box-button.stamp-enabled").each(function() {
        $(this).find(".ad").text(ad || $(this).attr("data-ad"));
    });
    return currentMark || (progress == sheetLength ? sheetLength : sheet[1]);
}
function selectMember() {
    $("#dialog-select-member-options").empty();
    var listMembers = [];
    $.each(state.boxRemote.contents[state.boxType == 2 ? 7 : 4], function(groupIndex, group) {
        $.each(group, function(unitIndex, unitID) {
            var memberID = unitStorage[unitID][1];
            if (listMembers.indexOf(memberID) >= 0)
                return;
            listMembers.push(memberID);
        });
    });
    listMembers.sort(function(member1, member2) {
        return member1 - member2;
    });
    $.each(listMembers, function(memberIndex, memberID) {
        qImg("member/s" + memberID, members[memberID][playdata.server - 1]).addClass("select-member-option").attr("data-member", memberID).click(function() {
            $(".select-member-option").removeClass("selected").addClass("unselected");
            $(this).removeClass("unselected").addClass("selected");
            $("#dialog-select-member+.ui-dialog-buttonpane .ui-button:first-of-type").button("enable");
        }).appendTo("#dialog-select-member-options");
    })
    showDialogConfirm($("#dialog-select-member"), function() {
        changeMember($(".select-member-option.selected").attr("data-member"));
    });
    $("#dialog-select-member+.ui-dialog-buttonpane .ui-button:first-of-type").button("disable");
}
function selectUnit() {
}
function changeMember(memberID) {
    playdata.selection[state.boxKey] = memberID;
    switch (state.boxType) {
        case 0:
            $("#box-selection").empty().append(
                $("<span>").text("选择的成员"),
                qImg("member/s" + memberID, members[memberID][playdata.server - 1]).addClass("selection-member"),
            );
            $("#box-buttons").show();
            break;
        case 2:
            refreshKnapsack();
            break;
    }
}
function changeUnit() {
}
function getEventUnitRates() {
    return state.boxType==2 ? [null,1,1,1,1,1,1,1] : [null,2,null,5,null,1];
}
function showLineup() {
    var contents = state.boxRemote.contents;
    var eventUnitRates = getEventUnitRates();
    var region = $("<div>");
    var selection = playdata.selection[state.boxKey];
    var SIGroup = 0;
    $.each([7, 6, 4, 5, 3, 2, 1], function(rarityIndex, rarity) {
        var countRarity = 0, rateNormal = 100;
        for (var i = 1; i < contents[0].length; i++) {
            if (!contents[rarity][i].length)
                continue;
            rateNormal -= contents[0][i][1];
        }
        var rarityLineup = $("<div>");
        $.each(contents[rarity], function(groupIndex, group) {
            if (!group.length)
                return;
            var table = $("#template-table-lineup").clone().attr("id", ""), tableEvent = $(table).clone();
            var count = 0, countEvent = 0;
            $.each(group, function(unitIndex, unitID) {
                var unit = unitStorage[unitID], skillID = unit[6], skill = skillStorage[skillID] || [0, 0];
                if (state.boxType == 0 && state.boxSelectType && selection && unit[1] != selection)
                    return;
                var tr = $("<tr>").append(
                    $("<td>").text("#" + unit[0]),
                    $("<td>").addClass("eis-sif-text attribute-" + unit[3]).text(members[unit[1]][playdata.server - 1]),
                    $("<td>").text(unit[6 + playdata.server]),
                    $("<td>").append($("<div>").append(
                        skill[0] ? qImg("skill/" + skill[0]) : "",
                        skill[1] ? skillTriggerShortNames[skill[1]] : "",
                    )),
                    $("<td>").append(contents[0][groupIndex][3] ? gItem(5500, SIUnits[unitID][contents[0][groupIndex][3]-1], playdata.server, 1, {d:true}, gConfig) : ""),
                    $("<td>").text(unit[5] ? series[unit[5]][playdata.server - 1] : ""),
                ).addClass(rarity == 7 && (state.boxSelectType == 1 ? unit[1] : unitID) == playdata.selection[state.boxKey] ? "highlight" : "");
                if (unitStorage[unitID][4] == 2) {
                    tr.appendTo(tableEvent);
                    countEvent++;
                } else {
                    tr.appendTo(table);
                    count++;
                }
            });
            countRarity += count + countEvent;
            var groupLineup = $("<div>");
            if (count) {
                groupLineup.append(
                    state.boxType != 2 ? $("<h5>").text(contents[0][groupIndex][2].replace("#", rarityNames[rarity]) || (rarityNames[rarity] + " " + (groupIndex > 0 ? "出现率提升" : "通常") + "社员")).addClass(groupIndex > 0 ? "up" : "") : "",
                    rarity != 7 ? $("<p>").text("每位社员在" + (rarity <= 5 ? " " : "") + rarityNames[rarity] + " 中的出现率为：" + ((groupIndex > 0 ? contents[0][groupIndex][1] : rateNormal) / (count + countEvent / (eventUnitRates[rarity] || 1)) / 100).toPercent(2)) : "",
                    rarity == 7 ? $("<p>").text("以下列出本招募中“选择 UR”可能出现的全部社员，招募时只会出现您事先选择的社员。") : "",
                    table,
                );
            }
            if (countEvent) {
                groupLineup.append(
                    $("<h5>").text(rarityNames[rarity] + " 活动先行配信" + (groupIndex > 0 ? "出现率提升" : "") + "社员").addClass(groupIndex > 0 ? "up" : "event"),
                    $("<p>").text("每位社员在 " + rarityNames[rarity] + " 中的出现率为：" + ((groupIndex > 0 ? contents[0][groupIndex][1] : rateNormal) / (count * eventUnitRates[rarity] + countEvent) / 100).toPercent(2)),
                    tableEvent,
                );
            }
            groupLineup.prependTo(rarityLineup);
            SIGroup = SIGroup || contents[0][groupIndex][4];
        });
        if (!countRarity)
            return;
        rarityLineup.prepend(
            $("<h4>").addClass("eis-sif-dialog-section-header").text(rarityNames[rarity] + "（共 " + countRarity + " 种类）"),
        ).appendTo(region);
    });
    showBoxDetail({lineupGenerated:region, SIRandomGroup:SIGroup}, {}, gConfig);
    $("#box-detail .ui-tabs").tabs("option", "active", 1);
}
function showSheet() {
    showDialogMessage($("#dialog-sheet"), function() {
        $(".stamp").removeClass("new");
    });
}
function scoutConfirm(type, key, amount, count, guaranteeID, stampEnabled) {
    var item = items[type][key];
    $(".dialog-confirm-scout-item").attr("src", "/vio/sif/" + (item[playdata.server - 1] || item[0]) + ".png");
    $(".dialog-scout-count").text(count);
    if (stampEnabled) {
        $(".dialog-confirm-scout-sheet").show();
    } else {
        $(".dialog-confirm-scout-sheet").hide();
    }
    if (state.boxType == 1) {
        $(".dialog-scout-stepup-num span:nth-of-type(1)").text(state.stepupSituation);
        $(".dialog-scout-stepup-num").show();
    } else {
        $(".dialog-scout-stepup-num").hide();
    }
    $("#dialog-confirm-scout-cost").text(amount);
    var pocket = playdata.pocket[type + "-" + key];
    $("#dialog-confirm-scout-pocket").text(pocket);
    if (pocket < amount) {
        $("#dialog-confirm-scout-insufficient").show();
    } else {
        $("#dialog-confirm-scout-insufficient").hide();
    }
    showDialogConfirm($("#dialog-confirm-scout"), function() {
        scout(type, key, amount, count, guaranteeID, stampEnabled);
    });
    if (pocket < amount) {
        $("#dialog-confirm-scout+.ui-dialog-buttonpane .ui-button:first-of-type").button("disable");
    }
}
function scout(type, key, amount, count, guaranteeID, stampEnabled) {
    var guarantee = boxGuarantees[stampEnabled ? state.stamp.g || guaranteeID : guaranteeID] || [0, 0];
    var listUnitIDs = [], countGuarantee = 0;
    for (var i = count; i > 0; i--) {
        var currentGuarantee = guarantee[1] - countGuarantee == i ? guarantee[0] : 0;
        var currentScout = scoutOne(currentGuarantee, stampEnabled && state.stamp.p && i <= state.stamp.p[1] ? state.stamp.p[0] : 0, count - i);
        var scoutUnitID = currentScout[0];
        if (unitStorage[scoutUnitID][2].toSortedRarity() >= guarantee[0].toSortedRarity()) {
            countGuarantee++;
        }
        if (state.boxType == 2) {
            playdata.knapsacks[state.boxKey][[null, null, 5, 4, 2, 3, 1, 0][currentScout[1]]]--;
        }
        listUnitIDs.push(currentScout);
    }
    if (state.boxType != 3) {
    listUnitIDs = listUnitIDs.shuffle();
    }
    $("#result, #result-table>tbody").empty();
    $.each(listUnitIDs, function(index, scout) {
        var unitID = scout[0], unit = unitStorage[unitID], skillID = unit[6], skill = skillStorage[skillID] || [0, 0];
        if (unit[4] == 3 || state.boxLocal[1].indexOf("篇章") >= 0) {
            gItem(1001, unitID, playdata.server, 0, {g:scout[2]}, gConfig).appendTo("#result");
        } else
        $("<tr>").append(
            $("<td>").text("#" + unit[0]),
            $("<td>").append($("<div>").append(
                qImg("icon/" + rarityShortNames[unit[2]] + unit[3]),
            )),
            $("<td>").addClass("eis-sif-text attribute-" + unit[3]).text(members[unit[1]][playdata.server - 1]),
            $("<td>").text(unit[6 + playdata.server]),
            $("<td>").append($("<div>").append(
                skill[0] ? qImg("skill/" + skill[0]) : "",
                skill[1] ? skillTriggerShortNames[skill[1]] : "",
            )),
            $("<td>").append(scout[3] ? gItem(5500, scout[3], playdata.server, 1, {d:true}, gConfig) : ""),
            $("<td>").text(unit[5] ? series[unit[5]][playdata.server - 1] : ""),
        ).appendTo("#result-table>tbody");
        if ($("#result-table>tbody").children().length) {
            $("#result-table").show();
        } else {
            $("#result-table").hide();
        }
    });
    playdata.pocket[type + "-" + key] -= amount;
    if (stampEnabled) {
        if (state.stamp.i) {
            giveItem(state.stamp.i[0], state.stamp.i[1], state.stamp.i[2]);
        }
        playdata.stamps[state.boxKey]++;
        $(".stamp[data-num=" + refreshSheet() + "]").addClass("new");
    }
    refreshPocket();
    switch (state.boxType) {
        case 1:
            playdata.progress[state.boxKey]++;
            refreshStepup();
            break;
        case 2:
            refreshKnapsack();
            break;
    }
    var dialogButtons = [
        {text:"确定", click:function(){$(this).dialog("close");}},
    ];
    if ([0].indexOf(state.boxType) >= 0) {
        dialogButtons.unshift({text:"再次招募", click:function(){scoutConfirm(type, key, amount, count, guaranteeID, stampEnabled);}});
    }
    $("#dialog-result-scout").dialog({modal:true, position:{of:window}, resizable:false, draggable:false, closeText:"确定", buttons:dialogButtons, close:function(event, ui) {
        $(this).dialog("destroy");
    }});
    if (stampEnabled) {
        showSheet();
    }
    showIncome("通过招募获得了以下道具！");
    $(window).resize();
}
function scoutOne(guaranteeRarity, pickGroup, scoutIndex) {
    var eventUnitRates = getEventUnitRates();
    var rarityRates, totalRates = 100, signedRate = 0, scoutRarity, scoutGroup, listUnitIDs = [], scoutUnitID;
    switch (state.boxType) {
        case 0:
            var template = boxTemplates[state.boxLocal[8]];
            rarityRates = [null, 100 - template[0] - template[1] - template[2] - template[3], template[3], template[2], template[0], template[1]];
            signedRate = template[6];
            break;
        case 1:
            var stepSetting = stepupSettings[state.boxLocal[7]][state.stepupSituation - 1];
            var stepID = $.isArray(stepSetting) ? stepSetting[0] : stepSetting;
            var step = steps[stepID];
            var rateUR, rateSSR, rateSR;
            if (step[3]) {
                rateUR = step[3]; rateSSR = step[4]; rateSR = step[5];
            } else {
                rateUR = 1; rateSSR = 4; rateSR = 15;
            }
            rarityRates = [null, 0, 100 - rateUR - rateSSR - rateSR, rateSR, rateUR, rateSSR];
            break;
        case 2:
            var remains = playdata.knapsacks[state.boxKey];
            rarityRates = [null, 0, remains[5], remains[4], remains[2], remains[3], remains[1], remains[0]];
            totalRates = remains[0] + remains[1] + remains[2] + remains[3] + remains[4] + remains[5];
            break;
        case 3:
            var rates = bagSettings[state.boxLocal[7]][scoutIndex];
            rarityRates = [null, 0, rates[3], rates[2], rates[0], rates[1]];
            break;
    }
    do {
    do {
        var r = Math.random() * totalRates;
        for (scoutRarity = 8; r >= 0; r -= rarityRates[--scoutRarity] || 0);
    } while (scoutRarity.toSortedRarity() < guaranteeRarity.toSortedRarity());
    var contents = state.boxRemote.contents;
    do {
        var g = Math.random() * 100;
        for (scoutGroup = contents[0].length; g >= 0 && scoutGroup > 0; g -= contents[scoutRarity][--scoutGroup].length > 0 ? contents[0][scoutGroup][1] : 0);
    } while (!contents[scoutRarity][scoutGroup].length);
    } while (pickGroup && contents[0][scoutGroup][0] != pickGroup);
    $.each(contents[scoutRarity][scoutGroup], function(index, unitID) {
        var unit = unitStorage[unitID];
        if (scoutRarity == 7 && (state.boxSelectType == 1 ? unit[1] : unitID) != playdata.selection[state.boxKey])
            return;
        if (state.boxType == 0 && state.boxSelectType && unit[1] != playdata.selection[state.boxKey])
            return;
        for (var i = unitStorage[unitID][4] == 2 ? 1 : eventUnitRates[scoutRarity] || 1; i > 0; i--) {
            listUnitIDs.push(unitID);
        }
    });
    var scoutUnitID = listUnitIDs[Math.floor(Math.random() * listUnitIDs.length)];
    var scoutSI = contents[0][scoutGroup][3] ? SIUnits[scoutUnitID][contents[0][scoutGroup][3]-1] : 0;
    if (!scoutSI && contents[0][scoutGroup][4]) {
        var group = SIGroups[contents[0][scoutGroup][4]][unitStorage[scoutUnitID][2]];
        if (group) {
            scoutSI = group[Math.floor(Math.random() * group.length)];
        }
    }
    return [scoutUnitID, scoutRarity, unitStorage[scoutUnitID][2] == 4 ? Math.random() * 100 < signedRate : false, scoutSI];
}
function resetStepupConfirm() {
    showDialogConfirm($("#dialog-confirm-stepup-reset"), resetStepup);
}
function resetStepup() {
    showDialogMessage($("#dialog-message-stepup-reset"), $.noop);
    playdata.progress[state.boxKey] = 0;
    refreshStepup();
}
function resetKnapsackConfirm() {
    showDialogConfirm($("#dialog-confirm-knapsack-reset"), resetKnapsack);
}
function resetKnapsack() {
    showDialogMessage($("#dialog-message-knapsack-reset"), $.noop);
    playdata.progress[state.boxKey]++;
    playdata.knapsacks[state.boxKey] = knapsackSettings[state.boxLocal[7]].slice();
    playdata.selection[state.boxKey] = 0;
    $("#box-selection").empty();
    refreshKnapsack();
}
function qBoxButton(type, key, amount, count, guaranteeID, stampEnabled) {
    var item = items[type][key];
    var guarantee = boxGuarantees[guaranteeID];
    var ad = guarantee ? "必得 " + rarityNames[guarantee[0]] + (guarantee[0] == 4 ? "" : " 或以上") + "×" + guarantee[1] : "";
    return $("<div>").addClass("sif-box-button" + (stampEnabled ? " stamp-enabled" : "")).attr("data-type", type).attr("data-key", key).append(
        qImg("button/s/b" + (count > 1 ? 2 : 1)),
        count > 1 ? $("<span>").addClass("number").text(count) : "",
        $("<div>").addClass("cost").append(
            qImg(item[playdata.server - 1] || item[0]).addClass("item"),
            $("<span>").text(amount),
        ),
        stampEnabled ? $("<div>").addClass("stamp-notice").text("可获得招募印章") : "",
        guarantee ? $("<div>").addClass("ad").text(ad) : "",
    ).attr("data-amount", amount).attr("data-ad", ad).attr("onclick", "scoutConfirm(" + type + "," + key + "," + amount + "," + count + "," + guaranteeID + "," + (stampEnabled || 0) + ")");
}
function giveItem(type, key, amount) {
    var ref = type + "-" + key;
    playdata.pocket[ref] += amount;
    playdata.income[ref] = (playdata.income[ref] || 0) + amount;
}
function showIncome(text) {
    if ($.isEmptyObject(playdata.income)) return;
    $("#dialog-income-text").text(text);
    $("#dialog-income-items").empty();
    $.each(playdata.income, function(itemRef, amount) {
        var split = itemRef.split("-"), type = parseInt(split[0]), key = split[1];
        gItemBlock(type, key, playdata.server, amount, {}, gConfig).appendTo("#dialog-income-items");
    });
    showDialogMessage("#dialog-income", $.noop);
    playdata.income = {};
}
function changeTab(event, ui) {
    switch (ui.newPanel.attr("id")) {
        case "tab-shop-history":
            listItems();
            $("#others").accordion("option", "active", false);
            break;
    }
}
function listItems() {
    $("#pocket").empty();
    $.each(playdata.pocket, function(itemRef, amount) {
        if (!amount) return;
        var split = itemRef.split("-"), type = split[0], key = split[1];
        gItemBlock(type, key, playdata.server, amount, {d:true}, gConfig).appendTo("#pocket");
    });
}

$(document).ready(function() {
    $("#maintab").tabs({disabled:[1,2,3,4], beforeActivate:changeTab});
    $("#start").button({icon:"ui-icon-play"});
    $("#box-extra>span").button();
    $("#box-lineup").button({icon:"ui-icon-person"});
    $("#box-sheet").button({icon:"ui-icon-print"});
    $("#stepup-reset").button({icon:"ui-icon-arrowrefresh-1-n"}).hide();
    $("#knapsack-reset").button({icon:"ui-icon-arrowrefresh-1-n"}).hide();
    $(".sheet-contents, #box-detail, #result, #result-table").tooltip({items:".eis-sif-item", content:function() {
        return gItemTooltip(parseInt($(this).attr("data-type")), $(this).attr("data-key"), playdata.server, gConfig);
    }, position:{my:"left+5 top-5", at:"left bottom"}});
});

var gConfig = $.extend({}, gConfigDefault, {
    itemNames:[null,3,4,5], itemDesc:[null,6,7,8], itemIntro:9, itemImages:[null,10,11,12], itemImagesSmall:[null,0,1,2],
    fUnit:function(unitID){return unitStorage[unitID];},
    unitMember:1, unitRarity:2, unitNames:[null,7,8,9],
    memberNames:[null,0,1,2],
    SINames:[null,1,2,3], SIImage:4, SIString:5, SIValue:6,
});
