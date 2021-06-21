var currentSongID, currentMapType, currentMapIndex, songStorage = {}, charts = {};
function init() {
    $.each(songs, function(songID, song) {
        var group = getSongGroup(songID);
        qSongLink(songID, group ? "songGroupConfirm("+songID+")" : "produce("+songID+")").appendTo("#songs");
    });
    $.each(currentEvents, function(eventIndex, currentEvent) {
        if (currentEvent[2] < (new Date).getTime() / 1000)
            return;
        var event = events[currentEvent[0]];
        $.each(event[10], function(songIndex, eventSong) {
            if (eventSong[0] == 1)
                return;
            $("<span>").addClass("eis-sif-flag static").append(
                serverNameAShort[currentEvent[1]] + " ",qDict("58","vs"),"排名歌曲／",
                $("<span>").addClass("eis-sif-countdown").attr("data-time", currentEvent[2]).attr("data-countdown-short", 1),
            ).appendTo(".song-link[data-song='" + eventSong[1] + "']");
        });
    });
    sortSong();
    refreshSongTags();
    enableCountdown();
}
function songGroupConfirm(songID) {
    var group = getSongGroup(songID);
    $("#dialog-song-group-songs").empty();
    qSongLink(songID, "produce("+songID+")", true, "继续选择【"+group[songID]+"】").appendTo("#dialog-song-group-songs");
    $.each(group, function(groupSongID, text) {
        if (groupSongID==songID) return;
        qSongLink(groupSongID, "produce("+groupSongID+")", true, "改选【"+text+"】").appendTo("#dialog-song-group-songs");
    });
    refreshSongTags();
    showDialogMessage("#dialog-song-group-select", $.noop, "取消");
}
function produce(songID, target) {
    $("#dialog-song-group-select.ui-dialog-content").dialog("close"); // This must go first
    $("#dialog-songs").dialog("close");
    $("body").removeClass("eis-sif-init");
    currentSongID = songID;
    if (songID in songStorage) {
        produceFin(songID, target);
        return;
    }
    $.get("/sifas/interface/lives.php", {s:songID}).done(function(response) {
        songStorage[songID] = response;
        produceFin(songID, target);
    });
}
function produceFin(songID, target) {
    var song = songs[songID], data = songStorage[songID], strings = data.strings;
    $("h2").removeClass().addClass("eis-sif-text category-" + getSongCategory(songID)).html(song[11]);
    $("#titles, #writers, #missions-focus, #missions, #maps").empty();
    for (var lang = 2; lang < 4; lang++) {
        if (song[10+lang]) {
            $("<span>").append($("<span>").addClass("eis-sif-tag server-" + lang).text(serverNameAShort[lang]), song[10+lang]).appendTo("#titles");
        }
    }
    $("<p>").html(data.source).appendTo("#titles");
    $.each(data.writers.split("\n"), function(paragraphIndex, paragraph) {
        $("<p>").html(paragraph).appendTo("#writers");
    });
    $.each(data.missions, function(missionIndex, mission) {
        var type = mission[6][0][0], key = mission[6][0][1];
        $("<div>").addClass("mission").append(
            mission[6].length > 1 ? qASImg("icon/d0").addClass("mission-pack") : qItem(type, key, mission[6][0][2]),
            strings[mission[3]] ? $("<div>").addClass("mission-campaign").html(strings[mission[3]]) : "",
            $("<p>").append(
                mission[0] != 3 ? $("<span>").addClass("eis-sif-tag term-" + mission[0]).text([null, "每日", "每周"][mission[0]]) : "",
                {13:"游玩 # 次此歌曲", 14:"完成 # 次此歌曲", 15:"完成 # 次*的此歌曲"}[mission[4]].replace("#", mission[5]).replace("*", difficultyNamesCD[mission[7]/10] + (mission[7]<30 ? "以上" : "")),
            ),
            $("<div>").addClass("mission-time").text(serverDate(mission[1], 1).getUTCDateTimeFull() + "～" + (mission[2] ? serverDate(mission[2] - 1, 1).getUTCDateTimeFull() : "无期限")),
        ).appendTo([15,26].indexOf(type) < 0 ? "#missions" : "#missions-focus");
    });
    if ($("#missions").children().length) {
        $("#missions-container").show();
    } else {
        $("#missions-container").hide();
    }
    for (var i = 1; i <= 5; i++) {
        $.each(data.maps[i], function(mapIndex, map) {
            $("<div>").addClass("eis-sif-gallery-item map-link " + ([3,5].indexOf(i)>=0 ? (map[0][2] == 2 ? "story-hard" : "story-normal")  : "difficulty-" + map[0])).addClass("difficulty-" + ([3,5].indexOf(i)>=0 ? 0 : map[0])).attr("data-map", i + "-" + mapIndex).append(
                $("<span>").text(getMapName(i, map[0])),
                qASImg("icon/a" + map[1]).addClass("map-link-attribute"),
            ).attr("onclick", "showMap(" + songID + "," + i + "," + mapIndex + ")").appendTo("#maps");
        });
    }
    $(window).scrollTop(0);
    $("#missions-container").trigger("eFold");
    $("#map-detail").hide();
    $("#detail").removeClass().addClass("category-" + getSongCategory(songID));
    showEvents();
    if (target) {
        if (target.tower) {
            $.each(data.maps[5], function(mapIndex, map) {
                if (map[0][0] == target.tower[0] && map[0][1] == target.tower[1]) {
                    showMap(songID, 5, mapIndex);
                    return false;
                }
            });
            $(window).scrollTop($(".map-link.active").position().top);
        }
    }
    var _paq = window._paq || [];
    _paq.push(["setCustomVariable", 1, "Song ID", songID, "page"]);
    _paq.push(["trackEvent", "Live Detail (SIFAS)", "Switch Song"]);
}
function showDialogSongs() {
    $(window).resize();
    $("#dialog-songs").dialog("open");
}
function filterSong() {
    $("#songs").children().each(function() {
        var songID = $(this).attr("data-song"), song = songs[songID];
        var match = matchTrackName($("#search-song").val(), song, [null,11,12,13,14]);
        if (match) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
    if ($("#songs").children(":visible").length) {
        $("#search-song-none").hide();
    } else {
        $("#search-song-none").show();
    }
}
function sortSong() {
    var sortMethod = parseInt($("#sort-song").val()), sortDirection = $("#sort-song-direction").val();
    var listSongs = $("#songs").children().each(function() {
        var songID = $(this).attr("data-song"), song = songs[songID];
        var info, value;
        switch (sortMethod) {
            case 0:
                info = "#" + songID;
                value = songID;
                break;
            case 1:
                info = [1,3,4,8,9].indexOf(song[9]) >= 0 ? songRouteStrings[song[9]].replace("#", song[10]) : null;
                value = song[2];
                break;
            case 2:
                info = songRouteStrings[song[9]].replace("#", song[9] == 2 ? songDailyNames[song[10]] : song[10]);
                value = songRouteOrders[song[9]] * 1000 + song[8];
                break;
            default:
                info = song[sortMethod];
                value = song[sortMethod] || -1;
        }
        if (info != null) {
            $(this).find(".song-link-info").text(info).show();
        } else {
            $(this).find(".song-link-info").hide();
        }
        $(this).attr("data-sort-value", value);
    });
    listSongs.sort(function(s1, s2) {
        var v1 = $(s1).attr("data-sort-value") >= 0 ? $(s1).attr("data-sort-value") : 2e9 * sortDirection;
        var v2 = $(s2).attr("data-sort-value") >= 0 ? $(s2).attr("data-sort-value") : 2e9 * sortDirection;
        return (v1 - v2) * sortDirection;
    });
    $("#songs").empty().append(listSongs);
}
function showEvents() {
    var server = parseInt($("#events-server").val());
    var song = songs[currentSongID], data = songStorage[currentSongID];
    $("#events").empty();
    $.each(data.events || [], function(stageIndex, stage) {
        var event = events[stage[0]];
        if (!event[3 + server])
            return;
        $("<div>").addClass("event").append(
            stage[1] == 2 ? (stage[1 + server] ? qASImg("emblem/event/" + stage[1 + server]) : qImg("title/0")).addClass("event-emblem") : "",
            $("<h6>").text(event[server]),
            $("<p>").append(
                $("<span>").addClass("eis-sif-tag event-" + event[0]).text(eventTypeNames[event[0]]),
                $("<span>").addClass("eis-sif-tag event-song-" + stage[1]).text([null, "先行开放", "张力排名"][stage[1]]),
                $("<span>").addClass("event-time").text(serverDate(event[3 + server], server).getUTCDateMedium()),
            ),
        ).appendTo("#events");
    });
    if ($("#events").children().length) {
        $("#events-none").hide();
    } else {
        $("#events-none").show();
    }
}
function showMap(songID, mapType, mapIndex) {
    currentMapType = mapType; currentMapIndex = mapIndex;
    var song = songs[songID], data = songStorage[songID], map = data.maps[mapType][mapIndex], strings = data.strings;
    var settingLang = parseInt(readSetting(1,"k4"));
    var hasLinkedMap = map[38].d, linkedMap = hasLinkedMap ? data.maps[1][map[38].d-1] : [], untrusted = map[38].u;
    if (!linkedMap) hasLinkedMap = false;
    $(".map-detail-type").hide();
    $(".map-detail-type[data-type='" + mapType + "']").show();
    switch (mapType) {
        case 5:
            $("#map-detail-tower-prev, #map-detail-tower-clear, #map-detail-tower-progress, #map-detail-tower-next").empty();
            $("#map-detail-tower-name").text(towers[map[0][0]][0]);
            $.each(rewards[map[38].c], function(rewardIndex, reward) {
                gItem(reward[0], reward[1], 1, reward[2], {}, gConfig).appendTo("#map-detail-tower-clear");
            });
            if (map[38].o) {
                $("<span>").html("下一个达成报酬<br>位于 " + (map[38].o - map[0][1]) + " 层后").appendTo("#map-detail-tower-progress");
            } else {
                $.each(rewards[map[38].r], function(rewardIndex, reward) {
                    gItem(reward[0], reward[1], 1, reward[2], {}, gConfig).appendTo("#map-detail-tower-progress");
                });
            }
            if (map[38].p) {
                var prevSongID = map[38].p[0], prevSong = songs[prevSongID];
                $("<span>").addClass("map-detail-jump").append('<i class="fas fa-backward"></i>', qASImg("icon/a" + prevSong[3]), prevSong[11]).attr("onclick", "produce(" + prevSongID + ",{tower:[" + map[0][0] + "," + (map[0][1]-1) + "]})").button().appendTo("#map-detail-tower-prev");
            } else {
                $("<span>").addClass("map-detail-jump").append('<i class="fas fa-fast-backward"></i>', '该层为第一层').button({disabled:true}).appendTo("#map-detail-tower-prev");
            }
            if (map[38].n) {
                var nextSongID = map[38].n[0], nextSong = songs[nextSongID];
                $("<span>").addClass("map-detail-jump").append(qASImg("icon/a" + nextSong[3]), nextSong[11], '<i class="fas fa-forward"></i>').attr("onclick", "produce(" + nextSongID + ",{tower:[" + map[0][0] + "," + (map[0][1]+1) + "]})").button().appendTo("#map-detail-tower-next");
                if (map[38].n.length > 1) {
                    var p = $("<p>").append("后续：");
                    for (var i = 1; i < map[38].n.length; i++) {
                        var futureSongID = map[38].n[i], futureSong = songs[futureSongID];
                        qASImg("icon/a" + futureSong[3], futureSong[11]).addClass("map-detail-info-icon").appendTo(p);
                    }
                    $("#map-detail-tower-next").append("<br>", p);
                }
            } else {
                $("<span>").addClass("map-detail-jump").append('该层为最后一层', '<i class="fas fa-fast-forward"></i>').button({disabled:true}).appendTo("#map-detail-tower-next");
            }
            break;
    }
    $(".map-data").each(function() {
        var cellData = map[$(this).attr("data-index")], useLinked = false;
        if (!cellData && $(this).attr("data-link") && hasLinkedMap) {
            cellData = linkedMap[$(this).attr("data-index")]; useLinked = true;
        }
        if (cellData && $(this).attr("data-date")) {
            cellData = new Date(cellData.fromDatestamp(18000)).getUTCDateFull();
        }
        if (!cellData && $(this).attr("data-hide")) {
            $(this).parent().hide();
            return;
        }
        $(this).text(cellData + (untrusted || useLinked ? " (?)" : "")).parent().show();
    });
    $(".map-data-date").text(function() {
        var data = parseInt(map[$(this).attr("data-index")]);
        return data ? new Date(data.fromDatestamp(18000)).getUTCDateFull() : $(this).attr("data-empty") || "???";
    });
    $(".map-data-string").html(function() {
        return strings[map[$(this).attr("data-index")]].replace(/\n/g, "");
    });
    $("#map-notes, #map-waves, #map-drops").empty();
    var waveEnds = [], waveEndsLinked = false;
    $.each(map[25], function(waveIndex, wave) {
        if (wave[4]) {
            waveEnds.push(wave[4], wave[5]);
        } else if (hasLinkedMap) {
            var linkedWave = linkedMap[25][waveIndex];
            waveEnds.push(linkedWave[4], linkedWave[5]);
            waveEndsLinked = true;
        }
    });
    var r = /<:icon_gimmick_([0-9]+)\/>/;
    var r2 = /<img src="Common\/InlineImage\/Icon\/tex_inlineimage_gimmick_([0-9]+)".*\/>/;
    $.each(map[24], function(noteIndex, note) {
        var noteIDs = [];
        if (waveEnds.length) {
            $.each(note[1], function(noteIDIndex, noteID) {
                for (var i=noteID, j=0; j<waveEnds.length && i>waveEnds[j]; i--,j++);
                noteIDs.push(i);
            });
        }
        $("<div>").addClass("map-note" + (note[3] != 2 ? (effects[note[6]][0] ? " buff" : " debuff") : "")).append(
            $("<h6>").append(
                qASImg("gimmick/" + gimmicks[r2.exec(strings[note[2]])[1]]),
                strings[note[2]].replace(r2, ""),
            ),
            $("<h6>").html(aNoteName(settingLang, note[6], note[7])),
            $("<p>").html(strings[note[4]].replace(/\n/g, "<br>")),
            $("<p>").html(aNoteDesc(settingLang, note[6], note[7], note[8], note[9], note[5], note[3])),
            noteIDs.length ? $("<p>").text(noteIDs.join(", ") + (untrusted||waveEndsLinked?" (?)":"")) : "",
            $("<div>").addClass("map-note-tag count").text(note[1].length),
            targets[note[5]] ? $("<div>").addClass("map-note-icons").html(targets[note[5]][effects[note[6]][0]].replace(/\[(.+?)\]/g, function(match, p1) {
                return $("<div>").append(qASImg(p1)).html();
            })) : "",
        ).appendTo("#map-notes");
    });
    if ($("#map-notes").children().length) {
        $("#map-notes-none").hide();
    } else {
        $("#map-notes-none").show();
    }
    $.each(map[25], function(waveIndex, wave) {
        var waveData = [wave[4], wave[5], wave[6], wave[7]], useLinked = false;
        if (!waveData[0] && hasLinkedMap) {
            var linkedWave = linkedMap[25][waveIndex];
            waveData = [linkedWave[4], linkedWave[5], linkedWave[6], linkedWave[7]]; useLinked = true;
        }
        var questionable = untrusted || useLinked ? " (?)" : "";
        $("<div>").addClass("map-wave" + ([3,255].indexOf(wave[1]) >= 0 ? "" : effects[wave[8]][0] ? " buff" : " debuff")).append(
            $("<h6>").html(strings[wave[0]]),
            $("<p>").html(strings[wave[2]].replace(/\n+$/, "").replace(/\n/g, "<br>")),
            $("<p>").html(aWaveDesc(settingLang, wave[8], wave[9], wave[10], wave[11], wave[3], wave[1])),
            waveData[0] ? $("<div>").addClass("map-wave-tag range").text(waveData[0] + "～" + waveData[1] + questionable) : "",
            waveData[2] ? $("<div>").addClass("map-wave-tag voltage").text(waveData[2] + questionable) : "",
            waveData[3] ? $("<div>").addClass("map-wave-tag damage").text(waveData[3] + questionable) : "",
            targets[wave[3]] ? $("<div>").addClass("map-wave-icons").html(targets[wave[3]][effects[wave[8]][0]].replace(/\[(.+?)\]/g, function(match, p1) {
                return $("<div>").append(qASImg(p1)).html();
            })) : "",
        ).appendTo("#map-waves");
    });
    if ($("#map-waves").children().length) {
        $("#map-waves-none").hide();
    } else {
        $("#map-waves-none").show();
    }
    if (charts.evaluation) {
        charts.evaluation.destroy();
    }
    charts.evaluation = new Chart($("#map-evaluation"), {type:"horizontalBar", data:{labels:["张力"], datasets:[
        {label:"C " + map[10], data:[map[10]], backgroundColor:"#ffbde2" /* #ffeff8 */, barPercentage:1, categoryPercentage:1},
        {label:"B " + map[9], data:[map[9] - map[10]], backgroundColor:"#c5def1" /* #ecf2f7 */, barPercentage:1, categoryPercentage:1},
        {label:"A " + map[8], data:[map[8] - map[9]], backgroundColor:"#fff67a" /* #fff9a3 */, barPercentage:1, categoryPercentage:1},
        {label:"S " + map[7], data:[map[7] - map[8]], backgroundColor:"#5cf4ff" /* #84f7ff */, barPercentage:1, categoryPercentage:1},
    ]}, options:{
        legend:{onClick:$.noop},
        scales:{xAxes:[{stacked:true, ticks:{max:map[7], callback:function(value, index, values) {
            if (index == values.length - 2)
                return;
            return value;
        }}}], yAxes:[{stacked:true}]},
        tooltips:{enabled:false},
        maintainAspectRatio:false,
    }});
    $(".map-link").removeClass("active");
    $(".map-link[data-map='" + mapType + "-" + mapIndex + "']").addClass("active");
    $("#map-detail, #map-drops-button").show();
}
function showDrops() {
    var data = songStorage[currentSongID], map = data.maps[currentMapType][currentMapIndex];
    var dropConfig = [[null,3,2,1],[null,5,5,4],[null,5,5,4],[null,7,7,6],[null,7,7,6]];
    var drops = [];
    for (var i = 0; i <= 4; i++) {
        $.each(data.drops[map[27 + i]], function(groupIndex, group) {
            var dropType = dropConfig[i][group[0]];
            drops[dropType] = (drops[dropType] || []).concat(group[1]);
        });
    }
    $.each([4,5,6,7,1,2,3], function(typeIndex, dropType) {
        if (!drops[dropType] || !drops[dropType].length)
            return;
        $("<tr>").append(
            $("<th>").append(qASImg("icon/d" + dropType)),
            $("<td>").append(function() {
                var l = [], b = [];
                $.each(drops[dropType], function(dropIndex, drop) {
                    var type = drop[0], key = drop[1], amount = drop[2] || 1;
                    if (b.indexOf(type + "-" + key + "-" + amount) >= 0)
                        return;
                    l.push(qItem(type, key, amount));
                    b.push(type + "-" + key + "-" + amount);
                });
                return l;
            }),
        ).appendTo("#map-drops");
    });
    $("#map-drops-button").hide();
}
function refreshSongTags(force) {
    $(".song-link-tags").filter(force?"*":":empty").empty().append(function() {
        var a = [];
        var songID = $(this).parent().attr("data-song"), song = songs[songID];
        $.each(song[1], function(tagIndex, tagID) {
            var tag = songTags[tagID];
            a.push($('<span class="eis-sif-tag song-'+tag[1]+'" data-tid="'+tagID+'">').text(tag[3]).attr("title",tag[2]+"："+tag[5]));
        });
        a.sort(function(a1,a2) {
            return songTags[$(a1).attr("data-tid")][0]-songTags[$(a2).attr("data-tid")][0];
        });
        return a;
    });
}
function getSongCategory(songID) {
    return Math.ceil(songID / 1000);
}
function getSongGroup(songID) {
    for (var i=1; i<songGroups.length; i++) {
        if (songGroups[i][songID]) return songGroups[i];
    }
    return false;
}
function getMapName(mapType, difficulty) {
    switch (mapType) {
        case 3:
            return difficulty[0] + "-" + difficulty[1] + ["（原）", " (N)", " (H)"][difficulty[2]];
        case 5:
            return towers[difficulty[0]][1] + (difficulty[1] ? " " + difficulty[1] : difficulty[2]);
        default:
            return [null, "", "活动", null, "SBL "][mapType] + [null, "初级", "中级", "上级", "上级＋", "挑战"][difficulty];
    }
}
function getItemImg(type, key) {
    switch (type) {
        case 15:
            return emblems[key] ? "emblem/" + emblems[key][0] : "";
    }
    return items[type] && items[type][key] ? items[type][key][0] : "";
}
function qItem(type, key, amount) {
    var img = getItemImg(type, key);
    return $("<div>").addClass("item").append(
        img ? qASImg(img) : qImg("0"),
        [15,23,26].indexOf(type) < 0 ? $("<div>").addClass("item-amount").text(amount) : "",
    );
}
function qSongLink(songID, onclick, noTag, text) {
    var song = songs[songID];
    return $('<div class="eis-sif-gallery-item song-link category-'+getSongCategory(songID)+'" data-song='+songID+' onclick="'+onclick+'">').append(
        $("<span>").html(text||song[11]),
        song[3] ? qASImg("icon/a"+song[3]).addClass("song-link-attribute") : null,
        song[15] ? qASImg("icon/a"+song[15]).addClass("song-link-attribute-2") : null,
        noTag ? null : $('<span class="eis-sif-tag song-link-info '+(song[0]?'default':'route-'+song[9])+'">'),
        $('<div class="song-link-tags">'),
    );
}

$(document).ready(function() {
    init();
    $("body").addClass("eis-sif-init");
    $("#dialog-songs").dialog({modal:true, position:{of:window}, resizable:false, draggable:false, closeText:"关闭", autoOpen:false});
    if (typeof regionNotices != "undefined" && regionNotices["dialog-songs"]) {
        $("#dialog-songs-notice").removeClass("eis-sif-hidden");
        $.each(regionNotices["dialog-songs"], function(noticeIndex, notice) {
            $("<p>").html(notice).appendTo("#dialog-songs-notice");
        });
    }
    showDialogSongs();
    $("#map-drops-button").button();
});

var gConfig = $.extend({}, gConfigDefault, {
    itemNames:[null,1,2,3], itemImages:[null,0,null,null],
    emblemNames:[null,1,null,null], emblemImages:[null,0,null,null],
});
var sConfig = {
    r:1,
    s:{
        "k4":{t:1,l:[[3,"简体中文"],[2,"英语"],[4,"繁体中文"]],d:3},
    },
    l:[
        {g:"语言设置",l:[{k:"k4",n:"演唱会信息翻译语言"}]},
    ],
    f:function() {
        $(".map-link.active").click();
    },
};
