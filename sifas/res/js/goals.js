var dataStorage = {};
var groupUpdateTimes = [null,{},{},{}], groupEndTimes = [null,{},{},{}];

function selectServer(server) {
    if (server in dataStorage) {
        selectServerFin(server);
        return;
    }
    $.get("/sifas/interface/goals.php", {s:server}).done(function(response) {
        dataStorage[server] = response;
        $.each(response.goals, function(goalIndex, goal) {
            var period = response.periods[goal[5]];
            groupUpdateTimes[server][goal[6]] = Math.max(groupUpdateTimes[server][goal[6]] || 0, period[0]);
            groupEndTimes[server][goal[6]] = Math.max(groupEndTimes[server][goal[6]] || 0, period[1]);
        });
        selectServerFin(server);
    });
}
function selectServerFin(server) {
    $("#dialog-server").dialog("close");
    $("#button-current").attr("onclick", "showCurrent(" + server + ")");
    $("#events").empty().attr("data-server", server);
    var listSubevents = [], listOthers = [];
    $.each(events, function(eventOrder, event) {
        if (!event || !event[2+2*server]) return;
        switch (event[0]) {
            case 1: case 2:
                $("<div>").addClass("eis-sif-gallery-item").append(qASImg("event/banner/" + event[9+server] + ".jpg", "", true)).attr("data-event", eventOrder).attr("onclick", "selectGroup(" + server + ",1," + eventOrder + ")").prependTo("#events");
                break;
            case 3:
                var dateOpen = serverDate(event[2+2*server], server);
                listSubevents.push($("<div>").addClass("eis-sif-gallery-item subevent").text(dateOpen.getUTCFullYear() + " 年 " + (dateOpen.getUTCMonth() + 1) + " 月 " + eventTypeNames[event[0]]).attr("onclick", "selectGroup(" + server + ",1," + eventOrder + ")").attr("data-open", event[2+2*server]));
                break;
        }
    });
    $.each(towerTerms, function(termID, term) {
        if (!term || !term[2*server-1]) return;
        var dateOpen = serverDate(term[2*server-2], server);
        listSubevents.push($("<div>").addClass("eis-sif-gallery-item subevent").text(dateOpen.getUTCFullYear() + " 年 " + (dateOpen.getUTCMonth() + 1) + " 月 DLP").attr("onclick", "selectGroup(" + server + ",2," + termID + ")").attr("data-open", term[2*server-2]));
    });
    $.each(campaigns, function(campaignID, campaign) {
        var groupID = events.length + towerTerms.length - 1 + campaignID;
        if (!groupUpdateTimes[server][groupID]) return;
        listOthers.push($("<div>").addClass("eis-sif-gallery-item other").append($("<span>").text(campaign[server-1])).attr("onclick", "selectGroup(" + server + ",3," + campaignID + ")").attr("data-update", groupUpdateTimes[server][groupID]));
    });
    $.each(dataStorage[server].topics, function(topicID, topic) {
        var groupID = events.length + towerTerms.length + campaigns.length - 1 + topicID;
        listOthers.push($("<div>").addClass("eis-sif-gallery-item other").append($("<span>").html(topic)).attr("onclick", "selectGroup(" + server + ",4," + topicID + ")").attr("data-update", groupUpdateTimes[server][groupID]));
    });
    listSubevents.sort(function(e1, e2) {
        return $(e2).attr("data-open") - $(e1).attr("data-open");
    });
    listOthers.sort(function(o1, o2) {
        return $(o2).attr("data-update") - $(o1).attr("data-update");
    });
    $("#subevents").empty().append(listSubevents);
    $("#others").empty().append(listOthers);
    refreshPageBar();
    lazyload();
    $("#groups-container").accordion("option", "active", 0);
    showDialogGroup();
}
function selectGroupCommon() {
    $("#dialog-group").dialog("close");
    $("body").removeClass("eis-sif-init");
    $("#goals").empty();
}
function selectGroup(server, type, key) {
    selectGroupCommon();
    showGroup(server, type, key);
    var _paq = window._paq || [];
    _paq.push(["setCustomVariable", 2, "Data Server", server, "page"]);
    _paq.push(["setCustomVariable", 3, "Goal Group Type", type, "page"]);
    _paq.push(["setCustomVariable", 4, "Goal Group Key", key, "page"]);
    _paq.push(["trackEvent", "Goals (SIFAS)", "Switch Group"]);
}
function showCurrent(server) {
    selectGroupCommon();
    var time = new Date().getTime() / 1000;
    var listGroups = [];
    $.each(groupUpdateTimes[server], function(groupID, groupUpdateTime) {
        if (groupEndTimes[server][groupID] < time) return;
        listGroups.push(groupID);
    });
    listGroups.sort(function(g1, g2) {
        return groupUpdateTimes[server][g2] - groupUpdateTimes[server][g1] || g1 - g2;
    });
    $.each(listGroups, function(groupIndex, groupID) {
        if (groupID < events.length) {
            showGroup(server, 1, groupID);
        } else if (groupID < events.length + towerTerms.length - 1) {
            showGroup(server, 2, groupID - events.length + 1);
        } else if (groupID < events.length + towerTerms.length + campaigns.length - 1) {
            showGroup(server, 3, groupID - events.length - towerTerms.length + 1);
        } else {
            showGroup(server, 4, groupID - events.length - towerTerms.length - campaigns.length + 1);
        }
    });
    var _paq = window._paq || [];
    _paq.push(["setCustomVariable", 2, "Data Server", server, "page"]);
    _paq.push(["deleteCustomVariable", 3, "page"]);
    _paq.push(["deleteCustomVariable", 4, "page"]);
    _paq.push(["trackEvent", "Goals (SIFAS)", "Show Current Goals"]);
}
function showGroup(server, type, key) {
    var groupID, groupName, groupImage, groupIntro;
    switch (type) {
        case 1:
            var event = events[key];
            groupID = key;
            groupName = ([1,2].indexOf(event[0]) >= 0 ? eventTypeNames[event[0]] + "活动" : "") + "「" + event[server] + "」课题";
            groupImage = "event/banner/" + event[9+server] + ".jpg";
            groupIntro = "活动时间：" + serverDate(event[2+2*server], server).getUTCDateTimeFull() + "～" + serverDate(event[3+2*server], server).getUTCDateTime();
            break;
        case 2:
            var term = towerTerms[key];
            var dateOpen = serverDate(term[2*server-2], server);
            groupID = events.length - 1 + key;
            groupName = dateOpen.getUTCFullYear() + " 年 " + (dateOpen.getUTCMonth() + 1) + " 月 DLP 课题";
            groupImage = "event/banner/" + term[5+server] + ".jpg";
            groupIntro = "活动时间：" + serverDate(term[2*server-2], server).getUTCDateTimeFull() + "～" + serverDate(term[2*server-1], server).getUTCDateTime();
            break;
        case 3:
            var campaign = campaigns[key];
            groupID = events.length + towerTerms.length - 1 + key;
            groupName = "「" + campaign[server-1] + "」课题";
            groupImage = campaign[2+server] ? "campaign/banner/" + campaign[2+server] + ".jpg" : "";
            groupIntro = campaign[6];
            break;
        case 4:
            groupID = events.length + towerTerms.length + campaigns.length - 1 + key;
            groupName = "「" + dataStorage[server].topics[key] + "」";
            break;
    }
    $("<section>").append(
        $("<header>").append(
            groupImage ? qASImg(groupImage) : "",
            $("<h3>").html(groupName),
            $("<p>").html(groupIntro),
        ),
        $("<div>").attr("id", "goal-group-" + groupID).addClass("goal-group"),
    ).appendTo("#goals");
    listGroup(server, groupID);
    if (!$("#goal-group-" + groupID).children().length) {
        $("<p>").text("本次活动没有课题。").insertBefore("#goal-group-" + groupID);
    }
}
function listGroup(server, groupID) {
    $.each(dataStorage[server].goals, function(goalID, goal) {
        if (goal[6] != groupID) return;
        var period = dataStorage[server].periods[goal[5]], rewards = dataStorage[server].rewards[goal[7]];
        $("<div>").addClass("goal" + (rewards.length == 1 ? " item-type-" + rewards[0][0] : "")).append(
            rewards.length > 1 ? qASImg("icon/d0").addClass("goal-pack").attr("onclick", "listPack(" + server + "," + goal[7] + ")") : gItem(rewards[0][0], rewards[0][1], server, rewards[0][2], {}, gConfig).addClass("goal-reward"),
            $("<div>").addClass("goal-desc").append(
                goal[0] != 3 ? $("<span>").addClass("eis-sif-tag term-" + goal[0]).text(missionTermNames[goal[0]]) : "",
                aGoalDesc(goal[1], goal[2], goal[3], goal[4]),
                words[goal[1]][0][0] ? $("<span>").addClass("goal-note").text("※ 跳过券不适用") : "",
            ),
            $("<div>").addClass("goal-time").text(serverDate(period[0], server).getUTCDateTimeFull() + "～" + serverDate(period[1] - 1, server).getUTCDateTime()),
        ).appendTo("#goal-group-" + groupID);
    });
}
function listPack(server, packID) {
    $("#dialog-pack-items").empty();
    $.each(dataStorage[server].rewards[packID], function(rewardIndex, reward) {
        gItemBlock(reward[0], reward[1], server, reward[2], {}, gConfig).appendTo("#dialog-pack-items");
    });
    showDialogMessage("#dialog-pack", $.noop);
}
function showDialogServer() {
    $(window).resize();
    $("#dialog-server").dialog("open");
}
function showDialogGroup() {
    $("#dialog-group").dialog("open");
}
function aGoalDesc(type, count, param1, param2) {
    switch (type) {
        case 13: case 14:
            if (param1) {
                var song = songs[param1 % 10000];
                return (words[type][1][2] || words[type][1][1]).replace("$", count).replace("^", song[0]);
            }
            return (words[type][0][2] || words[type][0][1]).replace("$", count);
        case 15:
            var song = songs[param2 % 10000];
            return (words[type][0][2] || words[type][0][1]).replace("$", count).replace("^", song[0]).replace("*", difficultyNamesCD[param1/10] + (param1<30 ? "以上" : ""));
        case 22:
            return (words[type][0][2] || words[type][0][1]).replace("$", count).replace("^", memberGroupNamesCT[param1]);
        case 24: case 50: case 51:
            return (words[type][0][2] || words[type][0][1]).replace("$", count).replace("^", members[param1][0]);
        case 53:
            if (count == 1) return (words[type][1][2] || words[type][1][1]);
            return (words[type][0][2] || words[type][0][1]).replace("$", count);
        case 63:
            if (param1) return (words[type][0][2] || words[type][0][1]).replace("$", count).replace("^", rarityNamesD[param1 / 10]);
            return (words[type][1][2] || words[type][1][1]).replace("$", count);
        case 72: case 93:
            return (words[type][param1][2] || words[type][param1][1]).replace("$", count);
        case 9: case 94:
            if (param1) return (words[type][1][2] || words[type][1][1]).replace("$", count).replace("^", members[param1][0]);
            return (words[type][0][2] || words[type][0][1]).replace("$", count);
        default:
            if (!words[type][0][1]) return "未知课题：" + type + "," + count + "," + param1 + "," + param2;
            return (words[type][0][2] || words[type][0][1]).replace("$", count).replace("#", param1);
    }
}

$(document).ready(function() {
    $.each(serverName, function(serverID, name) {
        $("<div>").addClass("eis-sif-gallery-item server-select server-" + serverID).text(name).attr("onclick", "selectServer(" + serverID + ")").appendTo("#servers");
    });
    $("#events").tooltip({items:".eis-sif-gallery-item", content:function() {
        var server = $("#events").attr("data-server");
        var eventID = $(this).attr("data-event"), event = events[eventID];
        var t = $("<div>").append($("<h5>").text(event[server]));
        for (var i = 1; i <= 3; i++) {
            if (i == server || !event[i]) continue;
            $("<p>").addClass("eis-sif-note").text(event[i]).appendTo(t);
        }
        $("<p>").text(serverDate(event[2+2*server], server).getUTCDateFull() + "～" + serverDate(event[3+2*server], server).getUTCDateShort()).appendTo(t);
        return t;
    }, position:{my:"left+5 top-5", at:"left bottom"}});
    $("#goals").tooltip({items:".eis-sif-item", content:function() {
        return gItemTooltip(parseInt($(this).attr("data-type")), $(this).attr("data-key"), $(this).attr("data-server"), gConfig);
    }, position:{my:"left-15 top-5", at:"right bottom"}});
    $("body").addClass("eis-sif-init");
    showDialogServer();
});

var gConfig = $.extend({}, gConfigDefault, {
    itemNames:[null,0,1,2], itemDescStrings:[null,4,5,6], itemIntroString:7, itemImages:[null,3,null,null],
    emblemNames:[null,0,1,2], emblemDesc:[null,6,7,8], emblemIntro:9, emblemImages:[null,3,4,5],
});
