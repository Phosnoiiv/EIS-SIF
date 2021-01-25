function produce(ignoreRecord) {
    var server = $("#server").val();
    if (inMourningCN && parseInt(server) == 3) {
        $("body").addClass("eis-sif-mourning");
    } else {
        $("body").removeClass("eis-sif-mourning");
    }
    $("#goals").empty();
    $.each(goals[server], function(index, goal) {
        var isExpired = goal[2] && goal[2] < Date.now() / 1000;
        var isNew = !isExpired && goal[1] && goal[1] < Date.now() / 1000;
        $("<div>").addClass("goal").append(
            $("<span>").addClass("sif-goal-tag-new").append(
                $("<div>").addClass("sif-goal-tag tag-" + (goal[4] || (goal[3] == 1 ? 4 : 5))),
            ),
            $("<div>").addClass("sif-goal-title").append(
                isNew ? qImg("icon/s/new").addClass("goal-new") : "",
                isExpired ? qImg("icon/clear").addClass("goal-clear") : "",
                $("<span>").addClass("sif-goal-category").text(categories[server][goal[5]]),
                $("<span>").addClass("goal-title").text(goal[0]),
                $("<div>").addClass("goal-time").append(
                    goal[1] > Date.now() / 1000 ? $("<span>").addClass("sif-goal-date").append(
                        $("<span>").addClass("eis-sif-countdown").attr("data-time", goal[1]).attr("data-countdown-short", 1).attr("data-countdown-parent", 1),
                        "后解锁",
                    ) : "",
                    goal[1] > Date.now() / 1000 && goal[2] ? $("<span>").addClass("sif-goal-date").text("期限 " + (goal[2] - goal[1] + 1).toPeriod(true)) : "",
                    goal[1] > Date.now() / 1000 || isExpired ? "" : $("<span>").addClass("sif-goal-date").append(
                        $("<span>").addClass("eis-sif-countdown").attr("data-time", goal[2]).attr("data-countdown-parent", 1),
                        "后截止",
                    ),
                ),
            ),
            $("<p>").addClass("goal-desc").html(sifGoalDesc(server, goal[6], goal[7], goal[8], goal[9], goal[10], goal[11], goal[12], goal[13], goal[14], goal[15], goal[16], goal[17])),
        ).addClass(isNew ? "new" : isExpired ? "expired" : "").appendTo("#goals");
    });
    $(".goal.expired").appendTo("#goals");
    if (!goals[server].length) {
        $("<p>").addClass("eis-sif-text").text("该服务器近期没有无法在查卡器显示文本的限时课题。").appendTo("#goals");
    } else {
        enableCountdown();
    }
    var _paq = window._paq || [];
    if (!ignoreRecord) {
        _paq.push(["setCustomVariable", 2, "Data Server", server, "page"]);
        _paq.push(["trackEvent", "Goals", "Switch Server"]);
    }
}
function sifGoalDesc(server, type, param1, param2, param3, param4, param5, param6, param7, param8, param9, param10, param11) {
    var strs = strings[server][type];
    var strDifficulties = [null, "EASY", "NORMAL", "HARD", "EXPERT", "TECHNICAL", "MASTER"];
    var strRanks = [null, "S", "A", "B", "C"];
    switch (type) {
        case 1:
            return strs[1].replace("%d", param1);
        case 2:
            return strs[param3 ? 2 : 1].replace("%s", strDifficulties[param1]).replace("%d", param2);
        case 3:
        case 4:
            if (param1 == 1) {
                return strs[2].replace("%d", param2);
            } else {
                return strs[1].replace("%s", strRanks[param1]).replace("%d", param2);
            }
        case 6:
            return strs[param2].replace("%s", unitGroups[server][param1]);
        case 7:
            return strs[param2].replace("%s", memberGroups[server][param1]);
        case 9:
            return strs[param3].replace("%s", memberGroups[server][param2]).replace("%s", $("<span>").append(qTrack(param1, server)).html());
        case 10:
            return strs[1].replace("%s", strs[param1 == 1000 ? (param2 == 5 ? 5 : 3) : param1 + 1]).replace("%d", param3);
        case 11:
            return strs[1].replace("%d", param1);
        case 37:
            return strs[1].replace("%s", $("<span>").append(qTrack(param1, server)).html()).replace("%d", param2);
        case 50:
            var str = "";
            if (param1) {
                var t = $("<span>").append(qTrack(param1, server)).html();
                if (!param2 && !param4) {
                    str += strs[1].replace("%s", t);
                } else if (param2 && !param4) {
                    str += strs[2].replace("%s", t).replace("%s", strDifficulties[param2]);
                } else if (!param2 && param4) {
                    str += strs[3].replace("%s", t).replace("%s", strs[34 + param4]);
                } else if (param2 && param4) {
                    str += strs[4].replace("%s", t).replace("%s", strDifficulties[param2]).replace("%s", strs[34 + param4]);
                }
            } else if (param3) {
                var t = strs[31 + param3];
                if (!param2 && !param4) {
                    str += strs[7].replace("%s", t);
                } else if (param2 && !param4) {
                    str += strs[8].replace("%s", t).replace("%s", strDifficulties[param2]);
                } else if (!param2 && param4) {
                    str += strs[9].replace("%s", t).replace("%s", strs[34 + param4]);
                } else if (param2 && param4) {
                    str += strs[10].replace("%s", t).replace("%s", strDifficulties[param2]).replace("%s", strs[34 + param4]);
                }
            } else {
                if (param2 && !param4) {
                    str += strs[5].replace("%s", strDifficulties[param2]);
                } else if (param2 && param4) {
                    str += strs[6].replace("%s", strDifficulties[param2]).replace("%s", strs[34 + param4]);
                } else if (param4) {
                    str += strs[11].replace("%s", strs[34 + param4]);
                }
            }
            if (param7 == 1) {
                str += strs[11 + param9].replace("%s", unitGroups[server][param8]);
            } else if (param7 == 2) {
                str += strs[14 + param9].replace("%s", memberGroups[server][param8]);
            }
            if (param5 && param6) {
                if (param5 == 1 && param6 == 1) {
                    str += strs[21];
                } else if (param5 == 1 && param6 != 1) {
                    str += strs[20].replace("%s", strRanks[param6]);
                } else if (param5 != 1 && param6 == 1) {
                    str += strs[19].replace("%s", strRanks[param5]);
                } else {
                    str += strs[18].replace("%s", strRanks[param5]).replace("%s", strRanks[param6]);
                }
            } else if (param5 && !param6) {
                if (param5 == 1) {
                    str += strs[27];
                } else {
                    str += strs[26].replace("%s", strRanks[param5]);
                }
            } else if (!param5 && param6) {
                if (param6 == 1) {
                    str += strs[29];
                } else {
                    str += strs[28].replace("%s", strRanks[param6]);
                }
            }
            if (param10 == 1) {
                str += strs[31];
            } else {
                str += strs[30].replace("%d", param10);
            }
            return str;
        default:
            return "暂不支持此类课题";
    }
}

$(document).ready(function() {
    produce(true);
});
