var ff = {
    sortMethod1:function(index){return function(itemID,item) {
        var difID = commons.r.g[2001].options[1];
        if (!item[1][difID]) return -99;
        return item[1][difID][index];
    }},
};
commons.c.g[2001] = {
    defaultViewType:2,
    getItems:function(){return songs;},
    filterIDs:[1], filterDefaults:{1:0},
    checkFilter:function(itemID,item,filterID,filterValue){
        switch (filterID) {
            case 1: if(filterValue&&!(songFlags[itemID]&&songFlags[itemID].length)) return false; return true;
        }
    },
    optionIDs:[1], optionDefaults:{1:3},
    sortMethods:{
        1:{v:function(itemID,item){var difID=commons.r.g[2001].options[1];if(!item[1][difID])return -99;return songRouteOrders[item[9]]*1000+item[8];}},
        2:{n:"游戏内顺序",v:function(itemID,item){return item[2];}},
        3:{n:"歌曲 ID",v:function(itemID,item){return itemID;}},
        4:{d:"58",k:"cf",a:"低",z:"高",v:ff.sortMethod1(1)},
        5:{n:"推荐力",a:"低",z:"高",v:ff.sortMethod1(2)},
        6:{n:"推荐体力",a:"低",z:"高",v:ff.sortMethod1(3)},
        7:{d:"58",k:"w7",a:"低",z:"高",v:ff.sortMethod1(4)},
    }, sortDefault:[1,1],
    itemClick:function(itemID,item){return "songLinkClicked("+itemID+")"},
    itemSearchWords:function(itemID,item){var a=[];
        a.push(item[11],item[12],item[13]);
        a.push(["#"+itemID,1]);
    return a;},
    createViewItem:function(itemID,item,viewType){
        var projectID = getSongCategory(itemID);
        var difID = commons.r.g[2001].options[1], dif = item[1][difID];
        var sortMethod = parseInt(commons.r.g[2001].sort.m);
        var hasFlags = songFlags[itemID]&&songFlags[itemID].length, $flags;
        if (hasFlags) {
            $flags = $('<span class="eis-sif-flag static">');
            if (hasFlags>1) {
                $flags.addClass("dynamic").hide().attr("data-song",itemID);
            } else {
                $flags.text(getFlagText(itemID,0));
            }
        }
        switch (viewType) {
            case 1: return qSongLink(itemID,"").append(hasFlags?$flags:null);
            case 2: return $('<div class="g-song">').append(
                $('<div class="eis-sif-gallery-item song-link category-'+projectID+'">').append($("<span>").html(item[11]), hasFlags?$flags:null),
                $('<div class="g-song-view-2-detail">').append(
                    $('<div class="eis-sif-row">').append(
                        dif ? $('<div>').append(
                            $('<span class="eis-sif-tag" data-g2-dif='+difID+'>').text(G2C.difficultyN[difID]),
                            ("icon/a"+dif[0]).toJQImg(1,2).addClass("g-song-view-2-attr"),
                            dif[6] ? $('<i class="fas fa-crown map-deck-icon">') : null,
                        ) : $('<span>'),
                        $('<div>').append(
                            $('<span>').text(sortMethod==3 ? "#"+itemID : getRouteDesc(itemID)),
                            item[0] ? $('<span class="eis-sif-tag default">').text("2D") : null,
                        ),
                    ),
                    $('<div class="eis-sif-row">').append(
                        $('<div data-song='+itemID+'>').append(
                            $('<div class="song-link-tags">'),
                        ),
                        $('<span>').text(function() {
                            if (!dif) return "";
                            switch (sortMethod) {
                                case 5: return dif[2];
                                case 6: return dif[3];
                                case 7: return dif[4];
                                default: return dif[1];
                            }
                        }),
                    ),
                ),
            );
        }
    },
    eRefreshed:function(){
        refreshSongTags();
        if (songFlagIntervalID>=0) clearInterval(songFlagIntervalID);
        animateFlag();
        songFlagIntervalID = setInterval(animateFlag, 5000);
    }
};

var currentSongID, currentMapType, currentMapIndex, songStorage = {}, charts = {};
var songFlags = {}, songFlagIntervalID = -1;
function init() {
    $.each(flags, function(flagIndex, flag) {
        if (!songFlags[flag[0]]) songFlags[flag[0]] = [];
        songFlags[flag[0]].push([flag[1],flag[2],flag[3]]);
    });
}
function songLinkClicked(songID) {
    if (getSongGroup(songID)) songGroupConfirm(songID);
    else produce(songID);
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
            var $link = $("<div>").addClass("eis-sif-gallery-item map-link " + ([3,5].indexOf(i)>=0 ? (map[0][2] == 2 ? "story-hard" : "story-normal")  : "difficulty-" + map[0])).addClass("difficulty-" + ([3,5].indexOf(i)>=0 ? 0 : map[0])).attr("data-map", i + "-" + mapIndex).append(
                $("<span>").text(getMapName(i, map[0])),
                qASImg("icon/a" + map[1]).addClass("map-link-attribute"),
                map[38].w!=undefined ? $('<span class="map-link-power">').text(map[38].w) : null,
                map[38].k ? $('<i class="fas fa-crown map-deck-icon">') : null,
            ).attr("onclick", "showMap(" + songID + "," + i + "," + mapIndex + ")");
            if (i==4) {
                $link.attr("data-map-cat", inferMapCat(map));
            }
            $link.appendTo("#maps");
        });
    }
    $(window).scrollTop(0);
    $("#missions-container").trigger("eFold");
    $("#map-deck, #map-detail").hide();
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
    var hasLinkedMap = map[38].d, linkedMap = [], untrusted = map[38].u;
    if (hasLinkedMap) {
        $.each(data.maps[1], function(linkIndex, linkMap) {
            if (linkMap[0]==map[38].d && ((linkMap[16]==100000&&map[16]==50000)||(linkMap[16]>100000&&map[16]>50000))) {
                linkedMap = linkMap;
                return false;
            }
        });
    }
    if (!linkedMap.length) hasLinkedMap = false;
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
            } else if (map[38].f) {
                $("<span>").html("已获得全部达成报酬").appendTo("#map-detail-tower-progress");
            } else {
                $.each(rewards[map[38].r], function(rewardIndex, reward) {
                    gItem(reward[0], reward[1], 1, reward[2], {}, gConfig).appendTo("#map-detail-tower-progress");
                });
            }
            if (map[38].p) {
                var prevSongInfo = map[38].p[0], prevSongID = prevSongInfo[0], prevSong = songs[prevSongID];
                $("<span>").addClass("map-detail-jump").append('<i class="fas fa-backward"></i>', qASImg("icon/a" + prevSongInfo[1]), prevSong[11]).attr("onclick", "produce(" + prevSongID + ",{tower:[" + map[0][0] + "," + (map[0][1]-1) + "]})").button().appendTo("#map-detail-tower-prev");
            } else {
                $("<span>").addClass("map-detail-jump").append('<i class="fas fa-fast-backward"></i>', '该层为第一层').button({disabled:true}).appendTo("#map-detail-tower-prev");
            }
            if (map[38].n) {
                var nextSongInfo = map[38].n[0], nextSongID = nextSongInfo[0], nextSong = songs[nextSongID];
                $("<span>").addClass("map-detail-jump").append(qASImg("icon/a" + nextSongInfo[1]), nextSong[11], '<i class="fas fa-forward"></i>').attr("onclick", "produce(" + nextSongID + ",{tower:[" + map[0][0] + "," + (map[0][1]+1) + "]})").button().appendTo("#map-detail-tower-next");
                if (map[38].n.length > 1) {
                    var p = $("<p>").append("后续：");
                    for (var i = 1; i < map[38].n.length; i++) {
                        var futureSongInfo = map[38].n[i], futureSongID = futureSongInfo[0], futureSong = songs[futureSongID];
                        qASImg("icon/a" + futureSongInfo[1], futureSong[11]).addClass("map-detail-info-icon").appendTo(p);
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
    $(".map-data-strings").each(function() {
        $this = $(this).empty();
        $.each(map[$(this).attr("data-index")], function(index, strId) {
            $("<p>").html(strings[strId]).appendTo($this);
        });
    })
    $("#map-notes, #map-waves, #map-drops").empty();
    var waveEnds = [], waveEndsLinked = false;
    $.each(map[25], function(waveIndex, wave) {
        if (wave[4]) {
            waveEnds.push(wave[4], wave[5]);
        } else if (hasLinkedMap) {
            var linkedWave = linkedMap[25][waveIndex];
            if (linkedWave[4]) {
            waveEnds.push(linkedWave[4], linkedWave[5]);
            waveEndsLinked = true;
            }
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
        $("<div>").addClass("map-note" + (note[3] != 2 ? (effects[note[6]][0] ? " buff" : note[3]==1?" debuff-auto":" debuff") : "")).append(
            $("<h6>").append(
                qASImg("gimmick/" + gimmicks[r2.exec(strings[note[2]])[1]]),
                strings[note[2]].replace(r2, ""),
            ),
            $("<h6>").html(aNoteName(settingLang, note[6], note[7])),
            $("<p>").html(strings[note[4]].replace(/\n/g, "<br>")),
            $("<p>").html(aNoteDesc(settingLang, note[6], note[7], note[8], note[9], note[5], note[3])),
            noteIDs.length ? $("<p>").text(noteIDs.join(", ") + (untrusted||waveEndsLinked?" (?)":"")) : "",
            !effects[note[6]][0] && note[3]==1 ? $('<p class="map-note-debuff-auto-hint">').text("仅在成功时触发此负面效果。可考虑关闭 AUTO 游玩本歌曲。") : "",
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
        var iconString = targets83[wave[3]] && targets83[wave[3]][1-effects[wave[8]][0]];
        $('<div class="map-wave" data-wave-mission='+G2C.waveMissionC[wave[12]]+'>').append(
            $('<div class="map-wave-mission">').append(
                $('<span data-flip=wave data-flip-val=0>').text(G2F.waveMissionD(wave[12], wave[13])),
                $('<span data-flip=wave data-flip-val=1>').html(strings[wave[0]]),
            ),
            waveData[0]||waveData[2]||waveData[3]||targets[wave[3]] ? $('<div class="map-wave-info eis-sif-row">').append(
                $('<div>').append(
                    waveData[0] ? $('<span class="eis-sif-tag map-default">').text(waveData[0]+"～"+waveData[1]+questionable) : null,
                    iconString ? $('<div class="map-wave-icons">').html(iconString.replace(/\((.+?)\)/g, function(match,p1){return $("<div>").append(p1.toJQImg(1,2)).html();})) : null,
                ),
                $('<div>').append(
                    waveData[2] ? $('<span class="map-wave-success">').text(waveData[2]+questionable) : null,
                    waveData[2]||waveData[3] ? $('<span class="map-wave-sf-connect">').html("&nbsp;") : null,
                    waveData[3] ? $('<span class="map-wave-failure">').text(waveData[3]+questionable) : null,
                ),
            ) : null,
            $('<div class="map-wave-detail">').append(
                buffIcons[wave[8]] ? ("ui/buff/"+buffIcons[wave[8]]).toJQImg(1,2).addClass("map-wave-buff") : null,
                $('<span data-flip=wave data-flip-val=0>').html(aWaveDesc(settingLang,wave[8],wave[9],wave[10],wave[11],wave[3],wave[1])),
                $('<span data-flip=wave data-flip-val=1>').html(strings[wave[2]].replace(/\n+$/,"").replace(/\n/g,"<br>")),
            ),
        ).appendTo("#map-waves");
    });
    if ($("#map-waves").children().length) {
        $("#map-waves-none").hide();
    } else {
        $("#map-waves-none").show();
    }
    if (map[38].x) {
        $("#map-extend-provider-s").show();
    } else {
        $("#map-extend-provider-s").hide();
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
    if (map[38].k) {
        eisIF2.setV1Ref('VoltageDeckView_title', '「' + song[11] + '」' + getMapName(mapType, map[0]))
        eisIF2.setV1Ref('VoltageDeckView_reports', map[38].k)
        $("#map-deck").show();
    } else {
        $("#map-deck").hide();
    }
    $(".map-link").removeClass("active");
    $(".map-link[data-map='" + mapType + "-" + mapIndex + "']").addClass("active");
    $("#map-detail, #map-drops-button").show();
    eisFlip.init();
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
        var difID = commons.r.g[2001].options[1];
        $.each(song[1][difID]?song[1][difID][5]:[], function(tagIndex, tagID) {
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
function getRouteDesc(songID) {
    var song = songs[songID];
    return songRouteStrings[song[9]].replace("#", song[9]==2?songDailyNames[song[10]]:song[10]);
}
function getFlagText(songID, flagIndex) {
    var flag = songFlags[songID][flagIndex];
    return G1E.serverSN[flag[1]]+" "+G2E.songFlagN[flag[0]]+"／"+(flag[2]?(flag[2]-commons.now).toPeriod(true):"时间未定");
}
function animateFlag() {
    $(".eis-sif-flag.dynamic").each(function() {
        var songID = $(this).attr("data-song");
        var flagIndex = parseInt($(this).attr("data-next") || 0);
        if ((flagIndex%songFlags[songID].length)==0) flagIndex = 0;
        $(this).text(getFlagText(songID,flagIndex)).fadeIn(300).delay(4000).fadeOut(300).attr("data-next", flagIndex+1);
    });
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
function inferMapCat(map) {
    if (map[16]<=50000) return 3;
    return 4;
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
        song[1][3] ? qASImg("icon/a"+song[1][3][0]).addClass("song-link-attribute") : null,
        song[1][4] ? qASImg("icon/a"+song[1][4][0]).addClass("song-link-attribute-2") : null,
        song[1][5] ? qASImg("icon/a"+song[1][5][0]).addClass("song-link-attribute-3") : null,
        noTag ? null : $('<span class="eis-sif-tag song-link-info '+(song[0]?'default':'route-'+song[9])+'">').text(getRouteDesc(songID)),
        $('<div class="song-link-tags">'),
    );
}

$(document).ready(function() {
    init();
    initGallery("#g-songs");
    recoverGallery("#g-songs");
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
    eisIF2.setV1Ref('VoltageDeckView_title', '')
    eisIF2.setV1Ref('VoltageDeckView_reports', [])
    eisIF2.mountVoltageDeckView('#v2-voltage-deck-container')
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
