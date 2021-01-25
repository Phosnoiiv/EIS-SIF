function showBoxDetail(contents, options, config) {
        $("#box-detail-card-rate").hide();
    if (contents.lineupGenerated) {
        $("#box-detail-lineup").empty().append(contents.lineupGenerated);
        $("#box-detail-card-lineup").show();
    } else {
        $("#box-detail-card-lineup").hide();
    }
    if (contents.SIRandomGroup) {
        $("#box-detail-si").empty().append(generateBoxDetailSI(SIGroups[contents.SIRandomGroup], {}, config));
        $("#box-detail-card-si").show();
    } else {
        $("#box-detail-card-si").hide();
    }
    showDialogMessage("#box-detail", $.noop);
    $("#box-detail-tabs").tabs("refresh");
}
function generateBoxDetailSI(list, options, config) {
    var r = $("<div>");
    $.each([4,5,3,2], function(rarityIndex, rarity) {
        if (!list[rarity]) return;
        $("<div>").addClass("box-detail-lineup-header").append(qImg("ui/unit/lineup/h" + rarity)).appendTo(r);
        $.each(list[rarity], function(SIIndex, SIID) {
            gItemBlock(5500, SIID, playdata.server, 0, {d:true}, config).appendTo(r);
        });
    });
    return r;
}
