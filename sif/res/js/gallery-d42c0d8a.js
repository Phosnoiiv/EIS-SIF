function galleryDialog(key) {
    $("#dialog-gallery-thumb").attr("src", getImgFullPath("thumb/" + key + ".jpg"));
    if (availableJPGs[key]) {
        var jpg = availableJPGs[key], group = availableJPGGroups[jpg[0]];
        $("#dialog-gallery-free").show();
        $("#dialog-gallery-flag .eis-sif-countdown").attr("data-time", group[0]);
        $("#dialog-gallery-link").attr("href", "/sif/interface/jpg.php?p=" + group[1] + "&c=" + jpg[1]);
    } else {
        $("#dialog-gallery-free").hide();
        $("#dialog-gallery-link").attr("href", "/sif/interface/jpg.php?k=" + key);
    }
    showDialogMessage("#dialog-gallery", $.noop, "关闭");
}
function galleryDownloaded() {
    setTimeout(function() {
        refreshLimit($("#dialog-gallery-limit .limit-capacity-amount").attr("data-limit"));
    }, 2000);
}

$(document).ready(function() {
    $(".eis-sif-gallery").on("eProduce", ".eis-sif-gallery-item", function() {
        $(this).find(".gallery-item-thumb").attr("src", getImgFullPath("thumb/" + $(this).attr("data-thumb") + ".jpg"));
    });
    $.each(regionNotices["limit"], function(noticeIndex, notice) {
        $("<p>").addClass("eis-sif-note").html("※ " + notice).appendTo("#dialog-gallery-notes");
    });
    $.each(regionNotices["limit-campaign"], function(noticeIndex, notice) {
        $("<p>").addClass("eis-sif-note").html("※ " + notice).appendTo("#dialog-gallery-free");
    });
});
