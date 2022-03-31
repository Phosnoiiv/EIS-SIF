var eventConfigs = {
    1:{n:"Icon Collection",l:[
        {n:"通常歌曲",i:true,d:{1:{i:5},2:{i:10},3:{i:16},4:{i:27},6:{i:27}}},
        {n:"活动歌曲",l:false,d:{1:{c:15,p:58,b:[6,11]},2:{c:30,p:117,b:[7,12]},3:{c:45,p:198,b:[8,13]},4:{c:75,p:410,b:[9,14]},6:{c:75,p:533,b:[10,15]}}},
    ]},
    2:{n:"Score Match",l:[
        {b:[1,2],d:{1:{p:42,f:14},2:{p:100,f:34},3:{p:177,f:59},4:{n:"EXPERT/TECHNICAL",p:357,f:119},6:{p:464,f:155}}},
    ],a:1},
    3:{n:"Medley Festival",l:[
        {x:3,b:[1,3],d:{1:{l:4,p:31,x:1},2:{l:8,p:72,x:3},3:{l:12,p:126,x:5},4:{l:20,p:241,x:9},6:{l:20,p:382,x:9}}},
    ],m:true,g:{e:1.1,p:1.1},y:{p:[1,1.00637]},a:3},
    4:{n:"Challenge Festival",l:[
        {n:"第 1 回合",b:[1,3],d:{1:{p:39},2:{p:91},3:{p:158},4:{p:301},5:{p:391},6:{p:301}}},
        {n:"第 2 回合",b:[1,3],d:{1:{p:40,e:13},2:{p:94,e:29},3:{p:164,e:51},4:{p:320,e:93},5:{p:420,e:93},6:{p:391,e:93}}},
        {n:"第 3 回合",b:[1,3],d:{1:{p:41,e:14},2:{p:97,e:32},3:{p:170,e:56},4:{p:339,e:103},5:{p:449,e:103},6:{p:481,e:103}}},
        {n:"第 4 回合",b:[1,3],d:{1:{p:42,e:15},2:{p:100,e:35},3:{p:176,e:61},4:{p:358,e:113},5:{p:478,e:113},6:{p:661,e:113}}},
        {n:"第 5 回合",b:[1,3],d:{1:{p:43,e:16},2:{p:103,e:38},3:{p:182,e:66},4:{p:377,e:123},5:{p:507,e:123},6:{p:931,e:123}}},
    ],m:true,d:{1:{n:"初级"},2:{n:"中级"},3:{n:"高级"},4:{n:"超级"},5:{n:"神级"},6:{n:"极级"}},g:{e:1.1,p:1.1},y:{l:[0,1.54],e:[1,1.015],p:[1,1.148]},a:2},
    6:{n:"友情大合战",l:[
        {b:[1,3,4,5],d:{1:{p:39,f:13},2:{p:89,f:29},3:{p:153,f:51},4:{p:301,f:100},6:{p:390,f:130}}},
    ],a:1},
};
var bonusSeriesConfigs = {
    1:{n:"得分",b:[["S",1.2],["A",1.15],["B",1.1],["C",1.05],["D",1]]},
    6:{n:"得分",b:[["S",1.14],["A",1.12],["B",1.1],["C",1.05],["D",1]]},
    7:{n:"得分",b:[["S",1.18],["A",1.14],["B",1.1],["C",1.05],["D",1]]},
    8:{n:"得分",b:[["S",1.2],["A",1.15],["B",1.1],["C",1.05],["D",1]]},
    9:{n:"得分",b:[["S",1.22],["A",1.16],["B",1.1],["C",1.05],["D",1]]},
    10:{n:"得分",b:[["S",1.26],["A",1.18],["B",1.1],["C",1.05],["D",1]]},
    3:{n:"连击",b:[["S",1.08],["A",1.06],["B",1.04],["C",1.02],["D",1]]},
    11:{n:"连击",b:[["S",1.1],["A",1.06],["B",1.04],["C",1.02],["D",1]]},
    12:{n:"连击",b:[["S",1.12],["A",1.08],["B",1.04],["C",1.02],["D",1]]},
    13:{n:"连击",b:[["S",1.14],["A",1.1],["B",1.04],["C",1.02],["D",1]]},
    14:{n:"连击",b:[["S",1.16],["A",1.12],["B",1.04],["C",1.02],["D",1]]},
    15:{n:"连击",b:[["S",1.18],["A",1.14],["B",1.04],["C",1.02],["D",1]]},
    2:{n:"排名",b:[["1 位",1.25],["2 位",1.15],["3 位",1.05],["4 位",1]]},
    4:{n:"贡献度",b:[["1 位",1.08],["2 位",1.05],["3 位",1.02],["4 位",1]]},
    5:{n:"任务",b:[["SSS",1.45],["SS",1.35],["S",1.25],["A",1.15],["B",1.1],["C",1.05],["D",1]]},
};
var yellChoices = [100,60,50,30,20,10,5,1];
var difficultyLPs = [null, 5, 10, 15, 25, 25, 25];
var difficultyEXPs = [null, 12, 26, 46, 83, 83, 83];
var gBonusNames = {e:"购买经验提升", p:"购买 pt 提升"};
var luckyBonusNames = {l:"返还 LP 应援", e:"经验提升应援", p:"pt 提升应援"};

var plans, reachableYellEffects;
function showCurrentEvents() {
    $(window).resize();
    $("#dialog-current").dialog("open");
}
function selectEvent(eventID, server) {
    var now = (new Date()).getTime() / 1000;
    var currentEvent = currentEvents[eventID];
    var timeOpen = currentEvent[3+2*server], timeEnd = currentEvent[4+2*server];
    $("#type").val(currentEvent[0]);
    changeType();
    $("#time").val(Math.floor((timeEnd - Math.max(now, timeOpen)) / 360) / 10);
    $("#dialog-current").dialog("close");
}
function changeType() {
    var type = $("#type").val(), eventConfig = eventConfigs[type];
    $("#config-play, #config-lucky").empty();
    $("#collected").attr("disabled","");
    if (eventConfig.d) {
        qFormSelect("difficulty-all", "难度", generateDifficultyOptions(eventConfig.d), "changeDifficulty(0)").appendTo("#config-play");
    }
    $.each(eventConfigs[type].l, function(lIndex, l) {
        if (l.x) {
            qFormSelect("chain-"+lIndex, (l.n||"")+"连续演唱会曲数", generateContinuousOptions(1, l.x, " 曲", l.x), "changeChain("+lIndex+")").appendTo("#config-play");
        }
        if (!eventConfig.d) {
            for (var i = 1; i <= (l.x || 1); i++) {
                qFormSelect("difficulty-"+lIndex+(l.x ? "-"+i : ""), (l.n||"")+(l.x ? "第 "+i+" 曲" : "")+"难度", generateDifficultyOptions(l.d), "changeDifficulty("+lIndex+")").appendTo("#config-play");
            }
        }
        if (eventConfig.m) {
            qFormSlider("multiple-" + lIndex, (l.n || "") + "消费 LP", [{v:1, t:"1 倍", s:true}, {v:2, t:"2 倍"}, {v:3, t:"3 倍"}, {v:4, t:"4 倍"}, {v:-1, t:"自定义"}], 1, 1).appendTo("#config-play");
        }
        if (l.i) {
            qFormSlider("icon", "收集活动图标数", [], 0, 0.1).appendTo("#config-play");
            qFormCheck("icon-setlist", "Setlist", null).appendTo("#config-play");
            $("#collected").removeAttr("disabled");
        }
        if (l.b) {
            $.each(l.b || [], function(bIndex, bonusSeriesID) {
                var bonusSeriesConfig = bonusSeriesConfigs[bonusSeriesID];
                qFormSlider("bonus-" + lIndex + "-" + bIndex, (l.n || "") + bonusSeriesConfig.n, (function() {
                    var options = [{v:-1, t:"自定义"}];
                    $.each(bonusSeriesConfig.b, function(bIndex, b) {
                        options.push({v:b[1], t:b[0] + " (" + b[1] + ")", s:bIndex == 0});
                    });
                    return options;
                })(), 1, 0.001).appendTo("#config-play");
            });
        } else {
            $("<div>").attr("id", "config-live-" + lIndex).appendTo("#config-play");
        }
        changeDifficulty(lIndex);
    });
    $.each(eventConfig.g, function(gKey, g) {
        qFormCheck("g-" + gKey, gBonusNames[gKey], gKey == "p" ? "checked" : null).appendTo("#config-play");
    });
    $.each(eventConfig.y, function(yKey, y) {
        qFormSlider("lucky-" + yKey, luckyBonusNames[yKey], [{v:y[1], t:"推荐值", s:true}, {v:y[0], t:"最低值"}, {v:-1, t:"自定义"}], y[0], 0.001).appendTo("#config-lucky");
    });
    if (eventConfig.a) {
        $("#tabs-main").tabs("enable", 1);
    } else {
        $("#tabs-main").tabs("option", {disabled:[1], active:0});
    }
    $(".event-specific").hide();
    $(".event-specific[data-event" + type + "]").show();
}
function changeDifficulty(lIndex) {
    var type = $("#type").val(), l = eventConfigs[type].l[lIndex];
    var difficulty = $("#difficulty-" + lIndex).val(), d = l.d[difficulty];
    if (l.i) {
        $("#icon").prev().empty().append(qSelect([{v:-1, t:"自定义"}, {v:d.i, t:d.i, s:true}]).children()).change();
    }
    if (!l.b) {
        $("#config-live-" + lIndex).empty();
        $.each(d.b || [], function(bIndex, bonusSeriesID) {
            var bonusSeriesConfig = bonusSeriesConfigs[bonusSeriesID];
            qFormSlider("bonus-" + lIndex + "-" + bIndex, (l.n || "") + bonusSeriesConfig.n, (function() {
                var options = [{v:-1, t:"自定义"}];
                $.each(bonusSeriesConfig.b, function(bIndex, b) {
                    options.push({v:b[1], t:b[0] + " (" + b[1] + ")", s:bIndex == 0});
                });
                return options;
            })(), 1, 0.001).appendTo("#config-live-" + lIndex);
        });
    }
}
function changeChain(lIndex) {
    var type = $("#type").val(), l = eventConfigs[type].l[lIndex];
    var chainCount = $("#chain-"+lIndex).val();
    for (var i = 1; i <= l.x; i++) {
        $("#difficulty-"+lIndex+"-"+i).attr("disabled", i<=chainCount ? null : "");
    }
}
function changeRank() {
    var rank = parseInt($("#rank").val());
    $("#exp-rank").text(sifEXP(rank));
    $("#lp-rank").text(sifLP(rank));
}
function addYell(amount) {
    if ($("#yell-chosen").children().length >= 5) {
        showDialogMessage("#dialog-yell-full", $.noop);
        return;
    }
    $("<span>").addClass("eis-sif-gallery-item").text(amount + "%").attr("onclick", "removeYell(this)").appendTo("#yell-chosen");
    refreshYell();
}
function removeYell(element) {
    $(element).remove();
    refreshYell();
}
function refreshYell() {
    var effect = 100;
    $("#yell-chosen").children().each(function() {
        var text = $(this).text();
        effect += parseInt(text.substring(0, text.length - 1));
    });
    $("#yell-effect").text("x" + effect + "%");
}
function getLuckyBonus(eventType, key, situation) {
    var eventConfig = eventConfigs[eventType];
    if (!eventConfig.y || !eventConfig.y[key])
        return;
    var y = eventConfig.y[key];
    switch (situation) {
        case "set":
            return $("#lucky-" + key).val();
        case "worst":
            return y[0];
    }
}
function calculateChain(l, chainDifficulties) {
    var chainCount = chainDifficulties.length;
    var chainResult = {lp:0, exp:0, pt:0, name:""};
    for (var i = 0; i < chainCount; i++) {
        var dID = chainDifficulties[i], d = l.d[dID];
        if (!d) return undefined;
        chainResult.lp += d.l || difficultyLPs[dID];
        chainResult.exp += d.e || difficultyEXPs[dID];
        chainResult.pt += d.p + (i>0 ? d.x*chainCount : 0);
        chainResult.name += (i>0 ? ", " : "") + (d.n || difficultyNames[dID]);
    }
    return chainResult;
}
function calculate() {
    var type = $("#type").val(), eventConfig = eventConfigs[type];
    if (eventConfig.l[0].x) {
        var firstDifficulty = Math.min($("#difficulty-0-1").val(), 4);
        for (var i = $("#chain-0").val(); i >= 2; i--) {
            if ($("#difficulty-0-"+i).val() < firstDifficulty) {
                showQuickDialogMessage("检测到您混搭了不同的演唱会难度。将最低难度放置在第 1 曲有助于获得更多活动 pt。您可调整不同难度的顺序后再次计算。", "提示", 350);
                break;
            }
        }
    }
    var yellEffectText = $("#yell-effect").text(), yellEffect = parseInt(yellEffectText.substring(1, yellEffectText.length - 1)) / 100;
    var goal = parseInt($("#goal").val());
    var logControl = $("#log-control:checked").length;
    $(".log").empty();
    $.each(["set", "best", "worst"], function(situationIndex, situation) {
        var rank = parseInt($("#rank").val());
        var lp = $("#time").val() * 10 + parseInt($("#lp").val());
        var exp = parseInt($("#exp").val());
        var icon = parseInt($("#collected").val());
        var pt = parseInt($("#gained").val());
        var flagFree = true, minL = 1, liveCount = 0;
        while (flagFree || pt < goal) {
            $.each(eventConfig.l, function(lIndex, l) {
                if (l.l == false)
                    return;
                if (l.x) {
                    var chainCount = $("#chain-"+lIndex).val();
                    var chainDifficulties = [];
                    for (var i = 1; i <= chainCount; i++) {
                        chainDifficulties.push($("#difficulty-"+lIndex+"-"+i).val());
                    }
                    var chainResult = calculateChain(l, chainDifficulties);
                }
                var difficulty = $("#difficulty-" + (eventConfig.d ? "all" : lIndex)).val(), d = l.d[difficulty];
                var multiple = eventConfig.m ? $("#multiple-" + lIndex).val() : 1;
                var lpConsume = Math.floor((l.x ? chainResult.lp : difficultyLPs[difficulty]) * $("#campaign-lp").val()) * multiple - (getLuckyBonus(type, "l", situation) || 0);
                if (flagFree && lpConsume > lp) {
                    recordResult("free-pt", situationIndex, pt);
                    recordResult("free-rank", situationIndex, "+" + (rank - $("#rank").val()) + '<span class="result-note">→' + rank + "</span>");
                    recordResult("free-count", situationIndex, liveCount);
                    flagFree = false;
                    return false;
                }
                var expGet = Math.round((l.x ? chainResult.exp : d.e || difficultyEXPs[difficulty]) * (eventConfig.g && eventConfig.g.e && $("#g-e:checked").length ? eventConfig.g.e : 1) * (getLuckyBonus(type, "e", situation) || 1) * $("#campaign-exp").val()) * multiple;
                var iconGet = l.i ? parseInt($("#icon").val()) : 0;
                var ptGet = l.i ? iconGet : l.x ? chainResult.pt : d.p;
                if (l.i && $("#icon-setlist:checked").length) {
                    iconGet = Math.round(iconGet * yellEffect);
                }
                $.each(l.b || [], function(bIndex, b) {
                    ptGet *= $("#bonus-" + lIndex + "-" + bIndex).val();
                });
                ptGet = Math.round(ptGet * (eventConfig.g && eventConfig.g.p && $("#g-p:checked").length ? eventConfig.g.p : 1) * (getLuckyBonus(type, "p", situation) || 1) * yellEffect) * multiple;
                lp -= lpConsume; exp += expGet; icon += iconGet; pt += ptGet; liveCount++;
                minL = Math.min(minL, Math.floor(lp));
                if (exp >= sifEXP(rank)) {
                    exp -= sifEXP(rank); rank++; lp += sifLP(rank);
                }
                if (logControl) {
                    $("<p>").text("LP-" + lpConsume + "=" + lp + ", EXP+" + expGet + "=" + exp + ", Icon+" + iconGet + "=" + icon + ", pt+" + ptGet + "=" + pt).appendTo("#log-" + situation);
                }
                if (pt >= goal && !flagFree)
                    return false;
            });
            $.each(eventConfig.l, function(lIndex, l) {
                if (l.l != false)
                    return;
                var difficulty = $("#difficulty-" + lIndex).val(), d = l.d[difficulty];
                var iconConsume = d.c;
                var expGet = Math.round(difficultyEXPs[difficulty] * $("#campaign-exp").val());
                var ptGet = d.p;
                $.each(d.b, function(bIndex, b) {
                    ptGet *= $("#bonus-" + lIndex + "-" + bIndex).val();
                });
                ptGet = Math.round(ptGet * yellEffect);
                while (icon >= iconConsume) {
                    icon -= iconConsume; exp += expGet; pt += ptGet; liveCount++;
                    if (exp >= sifEXP(rank)) {
                        exp -= sifEXP(rank); rank++; lp += sifLP(rank);
                    }
                    if (logControl) {
                        $("<p>").text("EXP+" + expGet + "=" + exp + ", Icon-" + iconConsume + "=" + icon + ", pt+" + ptGet + "=" + pt).appendTo("#log-" + situation);
                    }
                }
            });
        }
        if (minL >= 0) {
            recordResult("lp", situationIndex, "平刷即可", "enough");
            recordResult("loveca", situationIndex, "");
            recordResult("pt", situationIndex, "");
            recordResult("rank", situationIndex, "");
            recordResult("count", situationIndex, "");
        } else {
            recordResult("lp", situationIndex, -minL);
            recordResult("loveca", situationIndex, Math.ceil(-minL / sifLP(rank)), "cost");
            recordResult("pt", situationIndex, pt);
            recordResult("rank", situationIndex, "+" + (rank - $("#rank").val()) + '<span class="result-note">→' + rank + "</span>");
            recordResult("count", situationIndex, liveCount);
        }
    });
    $("#result, #log").removeClass("eis-sif-hidden");
}
function addPlans(group, pt, desc, priority) {
    $.each(reachableYellEffects, function(yellIndex, yell) {
        var finalPt = Math.round(pt * yell / 100);
        if (!plans[group][finalPt] || plans[group][finalPt].p > priority) {
            plans[group][finalPt] = {d:desc, y:yell, p:priority};
        }
    });
}
function addBonusPlans(bonusSeries, bonusPlans, funDesc, basePt, basePriority, additionalPriorities) {
    $.each(bonusPlans, function(bonusPlanIndex, bonusPlan) {
        var desc = "", pt = basePt, priority = basePriority;
        $.each(bonusSeries, function(bIndex, bonusSeriesID) {
            var bonusSeriesConfig = bonusSeriesConfigs[bonusSeriesID];
            pt *= bonusSeriesConfig.b[bonusPlan[bIndex]][1];
            desc += bonusSeriesConfig.n + "【" + bonusSeriesConfig.b[bonusPlan[bIndex]][0] + "】，";
            if (additionalPriorities.s && bonusSeriesID == 1) {
                priority *= additionalPriorities.s[bonusPlan[bIndex]];
            }
            if (additionalPriorities.c && bonusSeriesID == 3) {
                priority *= additionalPriorities.c[bonusPlan[bIndex]];
            }
        });
        addPlans(0, pt, funDesc(desc.substring(0, desc.length-1)), priority);
    });
}
function calculateAdjust() {
    var goal = $("#goal").val() - $("#gained").val();
    if (goal <= 0) {
        showQuickDialogMessage("当前活动 pt 已达到目标，无法控分。", "控分计算", 300);
        return;
    } else if (goal >= 180060) {
        showQuickDialogMessage("当前活动 pt 距目标太远，请稍晚再来控分。", "控分计算", 300);
        return;
    }
    plans = [[]];
    reachableYellEffects = [];
    var reachableYellPlans = {100:0}, reachableYellText = "";
    $.each(yellChoices, function(choiceIndex, choice) {
        var store = $("#yell-store-" + choice).val();
        for (var i = 600; i >= 100; i--) {
            if (reachableYellPlans[i] == undefined) continue;
            for (var j = Math.min(store, 5 - reachableYellPlans[i]); j > 0; j--) {
                reachableYellPlans[i+j*choice] = Math.min(reachableYellPlans[i+j*choice] || 99, reachableYellPlans[i] + j);
            }
        }
    });
    $.each(reachableYellPlans, function(yell, unitCount) {
        reachableYellEffects.push(parseInt(yell));
        reachableYellText += yell + "%，";
    });
    $("#result-adjust").empty().append($("<p>").addClass("eis-sif-note").text("可使用的加成配置方案：" + reachableYellText.substring(0, reachableYellText.length - 1)));
    var type = $("#type").val(), eventConfig = eventConfigs[type];
    var bonusPlans = [], currentBonusPlan = [], bonusSeries = eventConfig.l[0].b;
    if (bonusSeries) {
        for (var i = bonusSeries.length; i > 0; i--) {
            currentBonusPlan.push(0);
        }
        while (currentBonusPlan[0] < bonusSeriesConfigs[bonusSeries[0]].b.length) {
            bonusPlans.push(currentBonusPlan.slice());
            currentBonusPlan[bonusSeries.length-1]++;
            for (var i = bonusSeries.length - 1; i > 0; i--) {
                if (currentBonusPlan[i] >= bonusSeriesConfigs[bonusSeries[i]].b.length) {
                    currentBonusPlan[i] = 0;
                    currentBonusPlan[i-1]++;
                }
            }
        }
    }
    switch (eventConfig.a) {
        case 1:
            $.each(eventConfig.l[0].d, function(dID, d) {
                addPlans(0, d.f, "进行 " + (d.n || difficultyNames[dID]) + " 难度演唱会且失败", difficultyLPs[dID]);
            });
            break;
        case 2:
            var priorityScore = [1, 10, 10, 5, 2];
            var priorityComboMaster = [10, 10, 5, 2, 1];
            $.each(eventConfig.l[0].d, function(dID, d) {
                addBonusPlans(eventConfig.l[0].b, bonusPlans, function(desc) {
                    return "选择" + eventConfig.d[dID].n + "，确认没有任何活动 pt 应援（如有，进入演唱会后立即放弃），<b>不购买</b>活动 pt 加成效果，以" + desc + "完成" + eventConfig.l[0].n + "后立即结算";
                }, d.p, difficultyLPs[dID], {s:priorityScore, c:dID>=5?priorityComboMaster:undefined});
                addBonusPlans(eventConfig.l[0].b, bonusPlans, function(desc) {
                    return "选择" + eventConfig.d[dID].n + "，确认没有任何活动 pt 应援（如有，进入演唱会后立即放弃），<b>购买</b>活动 pt 加成效果，以" + desc + "完成" + eventConfig.l[0].n + "后立即结算";
                }, d.p * eventConfig.g.p, difficultyLPs[dID], {s:priorityScore, c:dID>=5?priorityComboMaster:undefined});
            });
            break;
        case 3:
            var priorityScore = [1, 10, 10, 5, 2];
            var priorityCombo = [10, 10, 10, 1, 2];
            for (var chainCount = 1; chainCount <= eventConfig.l[0].x; chainCount++) {
                var chainDifficulties = [];
                for (var i = chainCount; i > 0; i--) {
                    chainDifficulties.push(1);
                }
                while (chainDifficulties[0] <= 6) {
                    var chainResult = calculateChain(eventConfig.l[0], chainDifficulties);
                    if (chainResult) {
                        addBonusPlans(eventConfig.l[0].b, bonusPlans, function(desc) {
                            return "选择 " + chainCount + " 曲并按顺序选择 " + chainResult.name + " 难度，<b>不购买</b>活动 pt 加成效果，确认没有遇到活动 pt 应援（如有，立即清后台重新进游戏），以" + desc + "完成";
                        }, chainResult.pt, chainResult.lp, {s:priorityScore, c:priorityCombo});
                        addBonusPlans(eventConfig.l[0].b, bonusPlans, function(desc) {
                            return "选择 " + chainCount + " 曲并按顺序选择 " + chainResult.name + " 难度，<b>购买</b>活动 pt 加成效果，确认没有遇到活动 pt 应援（如有，立即清后台重新进游戏），以" + desc + "完成";
                        }, chainResult.pt * eventConfig.g.p, chainResult.lp, {s:priorityScore, c:priorityCombo});
                    }
                    chainDifficulties[chainCount-1]++;
                    for (var i = chainCount - 1; i > 0 && chainDifficulties[i] > 6; i--) {
                        chainDifficulties[i] = 1;
                        chainDifficulties[i-1]++;
                    }
                }
            }
            break;
    }
    for (var round = 1, schemes = [[{l:[], p:0}]]; ; round++) {
        schemes[round] = [];
        var minPt = 999999;
        for (var i = goal; i >= 0; i--) {
            if (schemes[round-1][i] == undefined) continue;
            $.each(plans, function(groupID, group) {
                $.each(group, function(planPt, plan) {
                    if (!plan) return;
                    var pt = i + planPt, priority = schemes[round-1][i].p + plan.p;
                    if (pt > goal) return false;
                    if (schemes[round][pt] && schemes[round][pt].p <= priority) return;
                    var list = schemes[round-1][i].l.slice();
                    list.push([groupID, planPt]);
                    schemes[round][pt] = {l:list, p:priority};
                    if (pt < minPt) {
                        minPt = pt;
                    }
                });
            });
        }
        if (schemes[round][goal]) {
            $.each(schemes[round][goal].l, function(step, planRef) {
                var plan = plans[planRef[0]][planRef[1]];
                $("<p>").html("<b>第 " + (step+1) + " 步：</b>将活动加成调整到 " + plan.y + "%，" + plan.d + "，获得 " + planRef[1] + "pt").appendTo("#result-adjust");
            });
            break;
        }
        if (minPt > goal) {
            $("<p>").html("非常遗憾，本站未能找到满足您要求的控分方案。").appendTo("#result-adjust");
            break;
        }
        if (round >= (eventConfig.a == 1 ? 15 : 5)) {
            $("<p>").html("未在 " + round + " 步内找到控分方案。当前活动 pt 可能距目标太远。您可能需要稍晚再来控分。").appendTo("#result-adjust");
            break;
        }
    }
    $("#result-section-adjust").removeClass("eis-sif-hidden");
}

function qFormBase(id, label, content) {
    return $("<p>").append(
        $("<label>").attr("for", id).text(label),
        content,
    );
}
function qFormCheck(id, label, checked) {
    return qFormBase(id, label, $("<input>").attr("id", id).attr("type", "checkbox").attr("checked", checked));
}
function qFormSelect(id, label, options, change) {
    return qFormBase(id, label, qSelect(options).attr("id", id).attr("onchange", change));
}
function qFormSlider(id, label, options, min, step) {
    var s = qSelect(options).addClass("slider-select");
    return qFormBase(id, label, [
        s,
        $("<input>").attr("id", id).attr("type", "number").attr("min", min).attr("step", step).addClass("slider-input").val(s.children("[selected]").first().val()),
    ]);
}
function generateDifficultyOptions(difficulties) {
    var options = [];
    $.each(difficulties, function(dID, d) {
        options.push({v:dID, t:d.n || difficultyNames[dID]});
    });
    options[options.length - 1].s = true;
    return options;
}
function generateContinuousOptions(min, max, suffix, defaultOption) {
    var options = [];
    for (var i = min; i <= max; i++) {
        options.push({v:i, t:i+suffix, s:i==defaultOption});
    }
    return options;
}
function recordResult(row, situationIndex, value, classes) {
    $("#result-" + row).children("td:nth-of-type(" + (situationIndex + 1) + ")").html(value).removeClass().addClass(classes);
}

$(document).ready(function() {
    $.each(eventConfigs, function(eventType, eventConfig) {
        $("<option>").attr("value", eventType).text(eventConfig.n).appendTo("#type");
    });
    var now = (new Date()).getTime() / 1000;
    var listCurrentEvents = [];
    $.each(currentEvents, function(eventID, currentEvent) {
        for (var server = 1; server <= 3; server++) {
            var timeEnd = currentEvent[4+2*server];
            if (timeEnd < now) continue;
            listCurrentEvents.push($("<li>").append(
                $("<span>").addClass("eis-sif-tag server-"+server).text(serverNameAShort[server]),
                $("<span>").addClass("event-clickable eis-sif-text category-"+currentEvent[1]).text(eventConfigs[currentEvent[0]].n+" 活动"+(currentEvent[1+server] ? "「"+currentEvent[1+server]+"」" : "")).attr("onclick", "selectEvent("+eventID+","+server+")"),
                $("<span>").addClass("event-date-end").text(serverDate(timeEnd, server).getUTCDateShort()),
            ).attr("data-end", timeEnd));
        }
    });
    listCurrentEvents.sort(function(e1, e2) {
        return $(e1).attr("data-end") - $(e2).attr("data-end");
    });
    $("#dialog-current-events").append(listCurrentEvents);
    qFormSlider("campaign-exp", "经验提升活动", [{v:1, t:"无", s:true}, {v:-1, t:"自定义"}, {v:1.5, t:"1.5 倍经验"}, {v:2, t:"2 倍经验"}, {v:3, t:"3 倍经验"}, {v:5, t:"5 倍经验"}, {v:10, t:"10 倍经验"}], 1, 0.5).appendTo("#config-campaign");
    qFormSlider("campaign-lp", "LP 打折活动", [{v:1, t:"无", s:true}, {v:-1, t:"自定义"}, {v:0.8, t:"8 折 LP"}, {v:0.7, t:"7 折 LP"}, {v:0.6, t:"6 折 LP"}, {v:0.2, t:"2 折 LP"}], 0.1, 0.1).appendTo("#config-campaign");
    $.each(yellChoices, function(choiceIndex, choice) {
        $("<span>").addClass("eis-sif-gallery-item").text(choice + "%").attr("onclick", "addYell(" + choice + ")").appendTo("#yell-choices");
        qFormSelect("yell-store-" + choice, "+" + choice + "%", [{v:5, t:"持有 5 人以上"}, {v:4, t:"持有 4 人"}, {v:3, t:"持有 3 人"}, {v:2, t:"持有 2 人"}, {v:1, t:"持有 1 人"}, {v:0, t:"未持有", s:true}]).appendTo("#yell-stores");
    });
    $("#calculate-button").button();
    $("#eis-sif-container").on("change", ".slider-select", function() {
        var v = $(this).val();
        if (v >= 0) {
            $(this).next().val(v);
        }
    }).on("change", ".slider-input", function() {
        var c = $(this).prev().children('[value="' + $(this).val() + '"]');
        $(this).prev().val(c.length ? $(this).val() : -1);
    });
    changeType();
    changeRank();
    refreshYell();
});
