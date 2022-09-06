var cheerTypes = [null, null, "掉落概率", "额外报酬"];

function produce() {
    $("h2").text(arena[1]);
    $(".eis-sif-timetip").text(serverDate(arena[2], 1).getUTCDateTime() + "～" + serverDate(arena[3], 1).getUTCDateTime());
    $.each(arena[4], function(cheerIndex, cheer) {
        var unitID = cheer[0], unit = units[unitID];
        $("<tr>").append(
            $("<td>").text(unit ? "#" + unit[0] : "?"),
            $("<td>").append($("<div>").append(unit ? qImg("icon/" + rarityShortNames[unit[2]] + unit[3]) : "")),
            $("<td>").addClass(unit ? "eis-sif-text attribute-" + unit[3] : "").text(unit ? members[unit[1]] : ""),
            $("<td>").text(unit ? unit[4] : ""),
            $("<td>").text(cheerTypes[cheer[1]]),
            $("<td>").append(unit && (cheer[1] == 3 || cheer[3] >= 4) && !unit[5] ? gItem(1001, unitID, 1, 0, {d:true}, gConfig) : ""),
            $("<td>").append(unit && unit[5] ? $("<div>").append(qImg("icon/is")) : "+" + cheer[2] + (cheer[1] == 3 ? "" : "%")).addClass(cheer[2] ? "" : "zero"),
            $("<td>").append(unit && (cheer[1] == 3 || cheer[3] >= 4) ? gItem(1001, unitID, 1, 0, {i:!unit[5], d:true}, gConfig) : ""),
            $("<td>").text("+" + cheer[3] + "%"),
        ).addClass(cheer[1] == 3 ? "arena-cheer-extra" : "").appendTo("#cheers>tbody");
    });
    $.each(lps, function(rangeIndex, range) {
        var isLast = rangeIndex==range.length-1;
        $("#arena-lp").append(range[1] + (isLast?" 关以后":"～"+range[2]+" 关") + "每关消费 " + range[0] + "LP" + (isLast?"。":"，"));
    });
    $.each(levels, function(levelIndex, level) {
        $("<tr>").append(
            $("<th>").text(levelIndex+1),
            $('<td class="eis-sif-text attribute-'+level[0]+'">').text(level[1]),
            $("<td>").append($.map(level[5], function(difficulty) {
                return $('<span class="arena-live-difficulty">').append(
                    difficultyShortNames[difficulty],
                    difficulty==4 ? qMapBrief(level[6],level[7]) : null,
                    difficulty==6 ? qMapBrief(level[8],level[10]) : null,
                    difficulty==6 && level[9]==1 ? '<span class="eis-sif-tag swing">滑</span>' : null,
                );
            })),
        ).appendTo("#arena-lives>tbody");
    });
}

$(document).ready(function() {
    produce();
});

var gConfig = $.extend({}, gConfigDefault, {
});
