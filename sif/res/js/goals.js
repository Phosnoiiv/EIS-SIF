function produce(ignoreRecord) {
    var server = $("#server").val();
    $("#goals").empty();
    $.each(goals[server], function(index, goal) {
        var isExpired = goal[2] && goal[2] < Date.now() / 1000;
        var isNew = !isExpired && goal[1] && goal[1] < Date.now() / 1000;
        $("<div>").addClass("goal").append(
            $("<span>").addClass("sif-goal-tag-new").append(
                $("<div>").addClass("sif-goal-tag tag-" + (goal[4] || (goal[3] == 1 ? 4 : 5)) + " server-" + server),
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
            $("<p>").addClass("goal-desc").html(sifGoalDesc(server, goal[6], {params1:goal[7],params2:goal[8],params3:goal[9],params4:goal[10],params5:goal[11],params6:goal[12],params7:goal[13],params8:goal[14],params9:goal[15],params10:goal[16],params11:goal[17]})),
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
function sifGoalDesc(server, type, params) {
    var strs = strings[server][type];
    var strDifficulties = [null, "EASY", "NORMAL", "HARD", "EXPERT", "TECHNICAL", "MASTER"];
    var strRanks = [null, "S", "A", "B", "C"];
    var str = strs[1];
    switch (type) {
        case 1:
        case 11:
            break;
        case 2:
            str = strs[params.params3 ? 2 : 1];
            params.difficulty = strDifficulties[params.params1];
            break;
        case 3:
        case 4:
            str = strs[params.params1 == 1 ? 2 : 1];
            params.live_goal_rank = strRanks[params.params1];
            break;
        case 6:
            str = strs[params.params2];
            params.group_name = unitGroups[server][params.params1];
            break;
        case 7:
            str = strs[params.params2];
            params.group_type_name = memberGroups[server][params.params1];
            break;
        case 9:
            str = strs[params.params3];
            params.group_type_name = memberGroups[server][params.params2];
            params.live_track_name = $("<span>").append(qTrack(params.params1, server)).html();
            break;
        case 32:
        case 37:
            params.live_track_name = $("<span>").append(qTrack(params.params1, server)).html();
            break;
        case 50:
            var str = "";
            if (params.params1) {
                params.live_track_name = $("<span>").append(qTrack(params.params1, server)).html();
                if (!params.params2 && !params.params4) {
                    str += strs[1];
                } else if (params.params2 && !params.params4) {
                    str += strs[2];
                    params.difficulty = strDifficulties[params.params2];
                } else if (!params.params2 && params.params4) {
                    str += strs[3];
                    params.live_type = strs[34 + params.params4];
                } else if (params.params2 && params.params4) {
                    str += strs[4];
                    params.difficulty = strDifficulties[params.params2];
                    params.live_type = strs[34 + params.params4];
                }
            } else if (params.params3) {
                params.live_attribute = strs[31 + params.params3];
                if (!params.params2 && !params.params4) {
                    str += strs[7];
                } else if (params.params2 && !params.params4) {
                    str += strs[8];
                    params.difficulty = strDifficulties[params.params2];
                } else if (!params.params2 && params.params4) {
                    str += strs[9];
                    params.live_type = strs[34 + params.params4];
                } else if (params.params2 && params.params4) {
                    str += strs[10];
                    params.difficulty = strDifficulties[params.params2];
                    params.live_type = strs[34 + params.params4];
                }
            } else {
                if (params.params2 && !params.params4) {
                    str += strs[5];
                    params.difficulty = strDifficulties[params.params2];
                } else if (params.params2 && params.params4) {
                    str += strs[6];
                    params.difficulty = strDifficulties[params.params2];
                    params.live_type = strs[34 + params.params4];
                } else if (params.params4) {
                    str += strs[11];
                    params.live_type = strs[34 + params.params4];
                }
            }
            if (params.params7 == 1) {
                str += strs[11 + params.params9];
                params.group_name = unitGroups[server][params.params8];
            } else if (params.params7 == 2) {
                str += strs[14 + params.params9];
                params.group_type_name = memberGroups[server][params.params8];
            }
            if (params.params5 && params.params6) {
                if (params.params5 == 1 && params.params6 == 1) {
                    str += strs[21];
                } else if (params.params5 == 1 && params.params6 != 1) {
                    str += strs[20];
                    params.combo_rank = strRanks[params.params6];
                } else if (params.params5 != 1 && params.params6 == 1) {
                    str += strs[19];
                    params.score_rank = strRanks[params.params5];
                } else {
                    str += strs[18];
                    params.score_rank = strRanks[params.params5];
                    params.combo_rank = strRanks[params.params6];
                }
            } else if (params.params5 && !params.params6) {
                if (params.params5 == 1) {
                    str += strs[27];
                } else {
                    str += strs[26];
                    params.score_rank = strRanks[params.params5];
                }
            } else if (!params.params5 && params.params6) {
                if (params.params6 == 1) {
                    str += strs[29];
                } else {
                    str += strs[28];
                    params.combo_rank = strRanks[params.params6];
                }
            }
            if (params.params10 == 1) {
                str += strs[31];
            } else {
                str += strs[30];
            }
            break;
        default:
            str = "暂不支持此类课题";
    }
    return str.G1template(params);
}

$(document).ready(function() {
    produce(true);
});
