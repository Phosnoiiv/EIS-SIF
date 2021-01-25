function produce() {
    stepups.sort(function(stepup1, stepup2) {
        return stepup2[5] - stepup1[5];
    });
    $.each(stepups, function(id, stepup) {
        var server = stepup[1];
        var dateOpen = new Date((stepup[5] + serverTimezone[server]) * 1000);
        var dateClose = new Date((stepup[6] + serverTimezone[server]) * 1000);
        var date = dateOpen.getUTCDateMedium() + "～" + dateClose.getUTCDateMedium();
        $("<li>").attr("id", "stepup-" + id).append(
            $("<span>").addClass("date").text(date),
            $("<span>").addClass("eis-sif-tag server-" + server).text(serverNameAShort[server]),
            $("<span>").addClass("clickable").text(stepupNames[stepup[3]] + stepupNameSuffixes[stepup[4]]).attr("onclick", "popupStepup(" + id + ")"),
        ).data("id", id).appendTo("#list");
        if (stepup[11]) {
            $("<span>").addClass("eis-sif-tag series-" + unitSeries[stepup[11]][0]).text(unitSeries[stepup[11]][4])
                .insertAfter($("#stepup-" + id + " .eis-sif-tag").last());
        }
    });
}
function relatedStepup(id) {
    $("#info-related-stepup").remove();
    $("<div>").attr("id", "info-related-stepup").addClass("eis-sif-notice").append(
        "现在正在显示 ",
        $("<b>").text(stepupNames[stepups[id][3]] + stepupNameSuffixes[stepups[id][4]]),
        " 的相关阶梯（各单级及赠品均完全相同的阶梯），",
        $("<span>").addClass("clickable").text("点击此处取消筛选").click(function() {
            $("#info-related-stepup").remove();
            $("#list").children().show();
        }),
    ).insertBefore("#list");
    $("#list").children().each(function() {
        var related = false;
        if (stepups[$(this).data("id")][8] == stepups[id][8]) {
            related = true;
        }
        if (related) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

$(document).ready(function() {
    produce();
});
