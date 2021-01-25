var coverTypes = [null, "常规", "活动", "商品", "线下", "周年"];
function produce() {
    if (inMourningCN) {
        $("body").addClass("eis-sif-mourning");
    }
    $.each(covers, function(coverId, cover) {
        if (!cover)
            return;
        var server = cover[0];
        var dateStart = new Date((cover[3] + serverTimezone[server]) * 1000);
        var dateStop = new Date((cover[4] + serverTimezone[server]) * 1000);
        $("<div>").addClass("eis-sif-gallery-item eis-sif-page-hidden category-" + cover[5]).append(
            $("<div>").addClass("gallery-item-title").append(
                $("<span>").addClass("eis-sif-tag server-" + server).text(serverNameAShort[server]),
                $("<span>").addClass("eis-sif-tag cover-type-" + cover[6]).text(coverTypes[cover[6]]),
                cover[1],
            ),
            $("<img>").addClass("gallery-item-thumb").attr("data-thumb", cover[2]).attr("onclick", "popupCover(" + coverId + ")"),
            $("<div>").addClass("gallery-item-note").text((cover[3] ? dateStart.getUTCDateMedium() : "不详") + "～" + (cover[4] ? dateStop.getUTCDateMedium() : "不详")),
        ).attr("data-thumb", cover[2]).prependTo("#covers");
    });
    refreshPageBar();
}
function popupCover(coverID) {
    var cover = covers[coverID], server = cover[0];
    $("#dialog-gallery-title").empty().append(
        $("<span>").addClass("eis-sif-tag server-" + server).text(serverNameAShort[server]),
        $("<span>").addClass("eis-sif-tag cover-type-" + cover[6]).text(coverTypes[cover[6]]),
        cover[1] + "封面背景",
    );
    galleryDialog(cover[2]);
}

$(document).ready(function() {
    produce();
});
