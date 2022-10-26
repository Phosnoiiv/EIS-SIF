function produce() {
    $("#event-title").addClass("category-" + event[3]).text(event[1]);
    $(".eis-sif-timetip").text(serverDate(event[2], 1).getUTCDateTime() + " 截止");
    var seriesCount = [];
    $.each(event[4], function(cheerIndex, cheer) {
        var unit = units[cheer[0]];
        if (!unit)
            return;
        seriesCount[unit[5]] = (seriesCount[unit[5]] || 0) + 1;
    })
    $.each(event[4], function(cheerIndex, cheer) {
        var unitID = cheer[0], unit = units[unitID];
        var isReward = event[5].indexOf(unitID) > -1, isPreviousReward = !isReward && previousEvent[0].indexOf(unitID) > -1;
        $("<tr>").append(
            $("<td>").text(unit ? "#" + unit[0] : "?"),
            $("<td>").append($("<div>").append(unit ? qImg("icon/" + rarityShortNames[unit[2]] + unit[3]) : "")),
            $("<td>").addClass(unit ? "eis-sif-text attribute-" + unit[3] : "").text(unit ? members[unit[1]] : ""),
            $("<td>").text(unit ? unit[4] : ""),
            $("<td>").append(unit ? (unit[5] && series[unit[5]] && (seriesCount[unit[5]] > 1 || event[5].indexOf(unitID) > -1) ? $("<span>").addClass("eis-sif-tag unit-" + unit[6]).text(series[unit[5]]) : "") : "（本期新卡）"),
            $("<td>").append(unit && (cheer[2] >= 30 || isReward || isPreviousReward) && !unit[7] ? gItem(1001, unitID, 1, 0, {d:true}, gConfig) : ""),
            $("<td>").append(unit && unit[7] ? $("<div>").append(qImg("icon/is")) : cheer[1] + "%").addClass(cheer[1] ? "" : "zero"),
            $("<td>").append(unit && (cheer[2] >= 30 || isReward || isPreviousReward) ? gItem(1001, unitID, 1, 0, {i:!unit[7], d:true}, gConfig) : ""),
            $("<td>").text(cheer[2] + "%"),
        ).addClass(isReward ? "reward-unit" : "").appendTo("#cheers>tbody");
    });
    $.each(lives, function(liveIndex, live) {
        var trackID = live[13];
        var stages = difficulties[trackID];
        $("<tr>").append(
            $("<td>").addClass("eis-sif-text attribute-" + live[0]).text(live[1]),
            $("<td>").append(
                stages ? $.map(stages, function(difficulty) {
                    return $("<span>").addClass("live-difficulty").append(
                        difficulty[3] < 9 ? "<b>" + difficulty[3] + " 键 </b>" : "",
                        difficulty[0] == 4 && difficulty[2] ? "<b>滑键</b> " : "",
                        difficultyNames[difficulty[0]],
                        difficulty[1] ? " 随机" + (live[12] ? "（初登场 " + '<span class="eis-sif-map"><span class="level">' + live[12] + '</span></span>' + "）" : "") : "",
                    );
                }) : null,
                !stages && live[12] ? ["初登场随机谱面 ", $("<span>").addClass("eis-sif-map").append($("<span>").addClass("level").text(live[12]))] : "",
                !stages && live[14] ? "5 键 MASTER" : "",
            ),
            stages && !$.grep(stages, function(stage) { return stage[0] == 4 && stage[2] == 0; }).length ? $("<td>") : $("<td>").append(
                qMapBrief(live[5], live[6]),
                $("<span>").addClass("weight").text(live[7] ? live[7].toFixed(1) : "?"),
            ),
            stages && !$.grep(stages, function(stage) { return stage[0] == 6 && stage[3] == 9; }).length ? $("<td>") : $("<td>").append(
                qMapBrief(live[8], live[10]),
                $("<span>").addClass("weight").text(live[11] ? live[11].toFixed(1) : "?"),
            ).addClass(live[9] ? "swing" : ""),
        ).appendTo("#lives>tbody");
    });
    $(".event1, .event2, .event3, .event4, .event5, .event6").hide();
    $(".event" + event[0]).show();
    if (!$("#lives>tbody>tr").length) {
        $("<p>").text("本期活动无可提供的歌单数据。").replaceAll("#lives");
    }
}

$(document).ready(function() {
    produce();
});

var gConfig = $.extend({}, gConfigDefault, {
});
