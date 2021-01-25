function produce() {
    $.each(posters, function(posterID, poster) {
        var server = poster[0];
        $("<div>").addClass("eis-sif-gallery-item eis-sif-page-hidden category-" + poster[5]).append(
            $("<div>").addClass("gallery-item-title").append(
                $("<span>").addClass("eis-sif-tag server-" + server).text(serverNameAShort[server]),
                poster[1],
            ),
            $("<img>").addClass("gallery-item-thumb").attr("data-thumb", poster[2]).attr("onclick", "popupPoster(" + posterID + ")"),
            $("<div>").addClass("gallery-item-note").text(serverDate(poster[3], server).getUTCDateMedium() + "～" + serverDate(poster[4], server).getUTCDateMedium()),
        ).attr("data-thumb", poster[2]).appendTo("#posters");
    });
    refreshPageBar();
}
function popupPoster(posterID) {
    var poster = posters[posterID], server = poster[0];
    $("#dialog-gallery-title").empty().append(
        $("<span>").addClass("eis-sif-tag server-" + server).text(serverNameAShort[server]),
        poster[1],
    );
    galleryDialog(poster[2]);
}

$(document).ready(function() {
    produce();
});
