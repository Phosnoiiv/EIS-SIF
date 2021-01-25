var carnivalDefaultName = [null, "リズミックカーニバル", "Rhythmic Carnival", "节奏嘉年华"];
function produce() {
    var playlistUsage = [];
    $.each(carnivals, function(carnivalId, carnival) {
        if (!carnival)
            return;
        if (!playlistUsage[carnival[0]]) playlistUsage[carnival[0]] = [];
        $("<tr>").append(function() {
            var td = [$("<th>").text(carnivalId)];
            for (var server = 1; server <= 3; server++) {
                if (!carnival[5 + server]) {
                    td.push($("<td>"));
                    continue;
                }
                td.push($("<td>").append(
                    $("<span>").addClass("eis-sif-tag server-" + server).text(serverNameAShort[server]),
                    $("<span>").addClass("carnival-clickable eis-sif-text category-" + carnival[1]).text(getCarnivalDisplayName(carnivalId, server)).attr("onclick", "popupCarnival(" + carnivalId + "," + server + ")"),
                    $("<span>").addClass("carnival-date-open").text(serverDate(carnival[8 + server], server).getUTCDateMedium()),
                ));
                if (!playlistUsage[carnival[0]].includes(server)) {
                    playlistUsage[carnival[0]].push(server);
                }
            }
            return td;
        }).appendTo("#carnivals>tbody");
    });
    var trackUsedCount = [];
    $.each(tracks, function(trackId, track) {
        trackUsedCount[trackId] = [null, 0, 0, 0];
    });
    $.each(playlists, function(playlistId, playlist) {
        $.each(playlist, function(trackIndex, trackId) {
            $.each(playlistUsage[playlistId], function(serverIndex, server) {
                trackUsedCount[trackId][server]++;
            });
        });
    });
    $.each(tracks, function(trackId, track) {
        var trackLang = $("#bar-track-lang").val();
        $("<li>").append(
            $("<span>").addClass("eis-sif-tag default sort-info").attr("data-sort", 1).text(track[8]).hide(),
            $("<span>").addClass("eis-sif-tag sort-info " + (track[11] ? "swing" : "default")).attr("data-sort", 2).text(track[12] ? track[12] : "-").hide(),
            $("<span>").addClass("eis-sif-tag sort-info " + (trackUsedCount[trackId][1] ? "server-1" : "default")).attr("data-sort", 3).text(trackUsedCount[trackId][1]).hide(),
            $("<span>").addClass("eis-sif-tag sort-info " + (trackUsedCount[trackId][2] ? "server-2" : "default")).attr("data-sort", 4).text(trackUsedCount[trackId][2]).hide(),
            $("<span>").addClass("eis-sif-tag sort-info " + (trackUsedCount[trackId][3] ? "server-3" : "default")).attr("data-sort", 5).text(trackUsedCount[trackId][3]).hide(),
            $("<span>").addClass("track-clickable eis-sif-track eis-sif-text attribute-" + track[0]).text(track[trackLang] || track[1]).attr("data-track", trackId).attr("onclick", "popupTrack(" + trackId + ")"),
        ).appendTo("#tracks");
    });
}
function iSwitch(elementId) {
    filterTrack();
}
function filterTrack() {
    $("#track-none").remove();
    var switchCategory1 = $("#filter-category-1").attr("data-switch");
    var switchCategory2 = $("#filter-category-2").attr("data-switch");
    var switchAttribute1 = $("#filter-attribute-1").attr("data-switch");
    var switchAttribute2 = $("#filter-attribute-2").attr("data-switch");
    var switchAttribute3 = $("#filter-attribute-3").attr("data-switch");
    var search = $("#search-track").val();
    var count = 0;
    $("#tracks").children().each(function() {
        var trackId = $(this).find(".eis-sif-track").attr("data-track");
        var track = tracks[trackId];
        var visible = (track[6] != 1 || switchCategory1 == 1)
                   && (track[6] != 2 || switchCategory2 == 1)
                   && (track[0] != 1 || switchAttribute1 == 1)
                   && (track[0] != 2 || switchAttribute2 == 1)
                   && (track[0] != 3 || switchAttribute3 == 1);
        var match = matchTrackName(search, track);
        if (visible && match) {
            $(this).show();
            count++;
        } else {
            $(this).hide();
        }
    });
    if (!count) {
        $("<p>").attr("id", "track-none").addClass("eis-sif-note").text("没有符合条件的歌曲，请尝试清除筛选条件或更换搜索文本。").appendTo("#tab-track");
    }
}
function sortTrack() {
    var listTracks = $("#tracks").children();
    var sortMethod = $("#sort-track").val(), sortDirection = $("#sort-track-direction").val();
    listTracks.sort(function(l1, l2) {
        var trackId1 = $(l1).find(".eis-sif-track").attr("data-track"), trackId2 = $(l2).find(".eis-sif-track").attr("data-track");
        var track1 = tracks[trackId1], track2 = tracks[trackId2];
        switch (sortMethod) {
            case "0":
                return (trackId1 - trackId2) * sortDirection;
            case "2":
                return (track1[12] - track2[12]) * sortDirection;
            default:
                var data1 = parseInt($(l1).find(".sort-info[data-sort=" + sortMethod + "]").text()), data2 = parseInt($(l2).find(".sort-info[data-sort=" + sortMethod + "]").text());
                return (data1 - data2) * sortDirection;
        }
    });
    $("#tracks").empty().append(listTracks);
    $("#tab-track .sort-info:not([data-sort=" + sortMethod + "])").hide();
    $("#tab-track .sort-info[data-sort=" + sortMethod + "]").show();
}
function popupCarnival(id, server, highlightTrackId) {
    var carnival = carnivals[id];
    var dateOpen = serverDate(carnival[8 + server], server);
    var dateClose = serverDate(carnival[11 + server], server);
    var dialog = $("#dialog-carnival").clone().attr("id", "");
    if (inMourningCN && server == 3) {
        $(dialog).addClass("eis-sif-mourning");
    }
    $(dialog).find(".dialog-title").append(
        $("<span>").addClass("eis-sif-tag server-" + server).text(serverNameAShort[server]),
        $("<span>").addClass("eis-sif-text category-" + carnival[1]).text(carnival[2 + server] || carnivalDefaultName[server]),
    );
    $(dialog).find(".dialog-info").append(
        '<i class="fas fa-calendar-week"></i> ',
        dateOpen.getUTCDateTimeFull() + "～" + (dateClose.getUTCMinutes() == 57 ? "提前结束" : dateClose.getUTCDateTimeFull()),
    );
    if (carnival[2]) {
        $(dialog).find("thead tr").append($("<th>").text("MASTER"));
        $(dialog).find("table").after($("<p>").addClass("eis-sif-note").text("※ MASTER 难度中，以粉色底色标注的为滑键谱面。"));
    } else {
        $(dialog).find(".dialog-info").after($("<p>").addClass("eis-sif-note").text("※ 本次活动不开放 MASTER 难度。"));
    }
    var table = $(dialog).find("tbody");
    var trackLang = $("#bar-track-lang").val();
    $.each(playlists[carnival[0]], function(trackIndex, trackId) {
        var track = tracks[trackId];
        $("<tr>").append(
            $("<td>").text(trackIndex + 1),
            $("<td>").addClass("track-clickable eis-sif-text attribute-" + track[0]).text(track[trackLang] || track[1]).attr("onclick", "popupTrack(" + trackId + "," + id + ")"),
            $("<td>").append(qMapBrief(track[7], track[8])),
            carnival[2] ? $("<td>").append(qMapBrief(track[10], track[12])).addClass(track[11] ? "swing" : "") : "",
        ).addClass(highlightTrackId && highlightTrackId == trackId ? "highlight" : "").appendTo(table);
    });
    $(dialog).dialog({modal:true, position:{of:window}, resizable:false, draggable:false, closeText:"关闭", classes:{
        "ui-dialog":"category-" + carnival[1],
    }, close:function(event, ui){
        $(this).dialog("destroy");
    }});
    $(window).resize();
    var _paq = window._paq || [];
    _paq.push(["setCustomVariable", 1, "Carnival ID", id, "page"]);
    _paq.push(["setCustomVariable", 2, "Carnival Server", server, "page"]);
    _paq.push(["trackEvent", "Carnivals", "Show Carnival"]);
}
function popupTrack(id, highlightCarnivalId) {
    var track = tracks[id];
    var dialog = $("#dialog-track").clone().attr("id", "");
    $(dialog).find(".dialog-title").append(
        track[14] ? qImg("sifas/emblem/song/" + track[14]).addClass("eis-sif-dialog-logo") : "",
        img("icon/a" + track[0], ""),
        $("<span>").addClass("eis-sif-text attribute-" + track[0]).text(track[1]),
    );
    $(dialog).find(".eis-sif-dialog-info").append(
        $("<p>").append(
            '<i class="fas fa-info-circle"></i> EXPERT 难度：',
            qMapBrief(track[7], track[8]),
            track[9] ? '　<i class="fas fa-calendar-day" title="谱面发布日期"></i> ' + (new Date((track[9] + 15000) * 86400000).getUTCDateMedium()) : "",
        ),
        track[10] ? $("<p>").append(
            '<i class="fas fa-info-circle"></i> MASTER 难度：',
            qMapBrief(track[10], track[12]),
            track[11] ? "（滑键）" : "",
            track[13] ? '　<i class="fas fa-calendar-plus" title="谱面发布日期"></i> ' + (new Date((track[13] + 15000) * 86400000).getUTCDateMedium()) : "",
        ) : "",
    );
    if ([1, 2, 3].includes(track[6])) {
        $(dialog).find(".dialog-title-tags").append(img("member/c" + track[6], ""));
    }
    var availablePlaylists = [];
    $.each(playlists, function(playlistId, playlist) {
        if (!playlist)
            return;
        if (playlist.includes(id)) {
            availablePlaylists.push(parseInt(playlistId));
        }
    });
    var table = $(dialog).find("tbody");
    var tr = [null, [], [], []];
    var count = 0, countExcludeM = 0;
    $.each(carnivals, function(carnivalId, carnival) {
        if (!carnival)
            return;
        if (!availablePlaylists.includes(carnival[0]))
            return;
        for (var server = 1; server <= 3; server++) {
            if (!carnival[5 + server])
                continue;
            var dateOpen = serverDate(carnival[8 + server], server);
            tr[server].push($("<tr>").attr("data-carnival", carnivalId).append(
                $("<td>").text(++count),
                $("<td>").append(
                    $("<span>").addClass("eis-sif-tag server-" + server).text(serverNameAShort[server]),
                    carnival[2] ? "" : $("<span>").addClass("eis-sif-tag default").text("EX-"),
                    $("<span>").addClass("carnival-clickable eis-sif-text category-" + carnival[1]).text(getCarnivalDisplayName(carnivalId, server)).attr("onclick", "popupCarnival(" + carnivalId + "," + server + "," + id + ")"),
                ),
                $("<td>").text(dateOpen.getUTCDateMedium()),
            ).addClass(highlightCarnivalId && highlightCarnivalId == carnivalId ? "highlight" : ""));
            if (carnival[2] == 0) {
                countExcludeM++;
            }
        }
    });
    for (var server = 1; server <= 3; server++) {
        if (!tr[server].length)
            continue;
        table.append(
            $("<tr>").addClass("eis-sif-table-section-header").append(
                $("<td>").text(serverName[server] + "登场记录").attr("colspan", 3),
            ),
            $.each(tr[server].sort(function(c1, c2) {
                return carnivals[$(c1).attr("data-carnival")][8 + server] - carnivals[$(c2).attr("data-carnival")][8 + server];
            }), function(index, r) {
                $(r).find("td:nth-child(1)").text(index + 1);
            }),
        );
    }
    if (!tr[1].length && !tr[2].length && !tr[3].length) {
        $(dialog).find("table").replaceWith($("<p>").addClass("eis-sif-note").text("此歌曲尚未在节奏嘉年华活动中登场。"));
    }
    if (countExcludeM) {
        $(dialog).find("table").after($("<p>").addClass("eis-sif-note").text("※ 标注“EX-”的活动不开放 MASTER 难度。"));
    }
    $(dialog).dialog({modal:true, position:{of:window}, resizable:false, draggable:false, closeText:"关闭", classes:{
        "ui-dialog":"category-" + track[6],
    }, close:function(event, ui){
        $(this).dialog("destroy");
    }});
    $(window).resize();
    var _paq = window._paq || [];
    _paq.push(["setCustomVariable", 1, "Track ID", id, "page"]);
    _paq.push(["deleteCustomVariable", 2, "page"]);
    _paq.push(["trackEvent", "Carnivals", "Show Track"]);
}
function getCarnivalDisplayName(carnivalID, server) {
    var carnival = carnivals[carnivalID];
    return $.isNumeric(carnival[5 + server]) ? "第 " + carnival[5 + server] + " 周" : carnival[5 + server];
}

$(document).ready(function() {
    produce();
});
