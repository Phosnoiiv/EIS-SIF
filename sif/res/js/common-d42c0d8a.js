var limitStorage = {};
var sCodeInitialized = false;

function matchTrackName(input, track, indices) {
    var reg = new RegExp(input, "i"), r = 0;
    $.each(indices || [null,1,2,3,4], function(loopIndex, index) {
        if (index == null) return;
        if (reg.test(track[index])) {
            r = loopIndex;
            return false;
        }
    });
    return r;
}

var rarityNames = [null, "N", "R", "SR", "UR", "SSR", "限定 UR", "选择 UR"];
var rarityShortNames = [null, "n", "r", "s", "u", "ss"];
var skillTriggerShortNames = {1:"T", 3:"N", 4:"C", 5:"爆分", 6:"P", 12:"星标", 100:"连锁"};
var difficultyNames = [null, "EASY", "NORMAL", "HARD", "EXPERT", "TECHNICAL", "MASTER"];
var difficultyShortNames = [null, "E", "N", "H", "EX", "TE", "MA"];
var articleTagNames = [null, "站务", "资料", "杂谈", "追踪", "搬运"];
var game1Rarities = [null,{l:40},{l:60},{l:80},{l:100},{l:90}];

var serverTimeDiffs = [null, [null, 32400, 0, 28800], [null, 32400, 32400, 28800]];
var serverMerge121 = 1623294000;

Number.prototype.toSortedRarity = function() {
    return [0, 1, 2, 3, 5, 4][this];
}
Number.prototype.fromDatestamp = function(base) {
    return this ? (this + base) * 86400000 : 0;
}
Number.prototype.toServerDate = function(game, server) {
    var diff = serverTimeDiffs[game][server];
    if (game==1 && server==2 && this>=serverMerge121) diff = serverTimeDiffs[1][1];
    return new Date((this+diff)*1000);
}
String.prototype.toJQImg = function(site, game, isLazy) {
    var s = this, r;
    if (r = s.match(/^((?:[g]\d:)*)s(\d):(.*)$/)) {site = r[2]; s = r[1]+r[3];}
    var src = (site<2 ? ["/","/vio/"][site] : resourceHosts[site-2])
            + ([2].indexOf(site)<0 ? ["","sif/","sifas/"][game] : "")
            + s + ([".jpg"].indexOf(s.substring(s.length-4))<0 ? ".png" : "");
    if (isLazy) return $('<img class="lazyload">').attr("data-src",src);
    return $("<img>").attr("src",src);
}
Array.prototype.shuffle = function() {
    var a = this.slice(), r = [];
    for (var i = this.length; i > 0; i--) {
        var d = Math.floor(Math.random() * i);
        r.push(a.splice(Math.floor(Math.random() * i), 1)[0]);
    }
    return r;
}

function sifLP(rank) {
    return rank <= 300 ? Math.floor(rank / 2) + 25 : Math.floor(rank / 3) + 75;
}
function sifEXP(rank) {
    var f = function(x) { return Math.round(0.522 * x * x + 0.522 * x + 10.0005); }
    if (rank <= 99) {
        return [null,6,6,8,10,13,16,20,24,28,34,39,46,52,60,68,76,85,94,104,115,125,137,149,162,174,188,203,217,232,247,264,281,298,310,327,345,362,379,396,413,431,448,465,483,500,517,534,551,569,585,603,620,638,654,672,689,707,723,741,758,775,793,810,827,844,861,878,896,913,930,947,965,982,999,1016,1033,1051,1068,1085,1102,1120,1137,1154,1171,1189,1206,1223,1240,1257,1275,1292,1309,1326,1343,1361,1378,1395,1413,1430][rank];
    }
    return rank <= 1000 ? f(rank) - f(rank - 33) : 34435 + (rank - 1001) * (35 + 1550 * (rank - 1000));
}

function getImgSmall(type, key) {
    if ([5100, 5200, 5600].indexOf(type) >= 0)
        return "type/" + type + "s";
    return undefined;
}
function getImgFullPath(path) {
    path = "/vio/" + (path.substring(0, 6) == "sifas/" ? "" : "sif/") + path;
    if (path.substr(path.length - 4, 4) != '.jpg') {
        path = path + ".png";
    }
    return path;
}

function getResourcePath1(path) {
    return resourceHost1 + path;
}

function qGeneralImg(path, desc) {
    return $("<img>").attr("src",path).attr("title",desc).attr("alt",desc);
}
function qImg(path, desc) {
    path = "/vio/" + (path.substring(0, 6) == "sifas/" ? "" : "sif/") + path;
    if (path.substr(path.length - 4, 4) != '.jpg') {
        path = path + ".png";
    }
    return $("<img>").attr("src", path).attr("title", desc).attr("alt", desc);
}
function qSelect(options) {
    var o = [];
    $.each(options, function(optionIndex, option) {
        o.push($("<option>").attr("value", option.v).text(option.t));
        if (option.s) {
            o[o.length - 1].attr("selected", "");
        }
    });
    return $("<select>").append(o);
}
function qTrack(trackId, lang) {
    var track = tracks[trackId];
    return $("<span>").addClass("eis-sif-track eis-sif-text attribute-" + track[0]).text(track[lang] || track[1]).attr("data-track", trackId);
}
function qMapBrief(level, note) {
    return $("<span>").addClass("eis-sif-map").append(
        $("<span>").addClass("level").text(level),
        $("<span>").addClass("note").text(note),
    );
}

var gConfigDefault = {
    fItem:function(type,key){return items[type][key];},
    fUnit:function(unitID){return units[unitID];},
    fMember:function(memberID){return members[memberID];},
    fTitle:function(titleID){return titles[titleID];},
    fBackground:function(backgroundID){return backgrounds[backgroundID];},
    fSI:function(SIID){return SIs[SIID];},
    fSIString:function(key){return SIStrings[key];},
    SIStrings:[null,0,1,2],
};
function gItemName(type, key, server, config) {
    switch (type) {
        case 1001:
            var unit = config.fUnit(key), member = config.fMember(unit[config.unitMember]);
            var name = unit[config.unitNames[server]];
            return rarityNames[unit[config.unitRarity]] + " " + member[config.memberNames[server]] + (name ? "【" + name + "】" : "");
        case 5100:
            var title = config.fTitle(key);
            return title[config.titleNames[server]];
        case 5200:
            var background = config.fBackground(key);
            return background[config.backgroundNames[server]];
        case 5500:
            var SI = config.fSI(key);
            return SI[config.SINames[server]];
        default:
            var item = config.fItem(type, key);
            return item[config.itemNames[server]];
    }
}
function gItemDesc(type, key, server, config) {
    switch (type) {
        case 1001:
            return "";
        case 5100:
            var title = config.fTitle(key);
            return title[config.titleDesc[server]];
        case 5200:
            var background = config.fBackground(key);
            return background[config.backgroundDesc[server]];
        case 5500:
            var SI = config.fSI(key), string = config.fSIString(SI[config.SIString]);
            return string[config.SIStrings[server]].replace("#", SI[config.SIValue]);
        default:
            var item = config.fItem(type, key);
            return (item[config.itemDesc[server]]||"").replace(/\\n/g, "<br>");
    }
}
function gItemIntro(type, key, config) {
}
function gItemImage(type, key, server, options, config) {
    switch (type) {
        case 1001:
            var unit = config.fUnit(key);
            var folder = Math.ceil(key / 100);
            if (options.v>=78) return "s3:card/icon1/"+folder+"/"+key + (options.g?"s":options.i?"i":"");
            return options.s ? "icon/" + rarityShortNames[unit[config.unitRarity]] : "unit/icon1/" + folder + "/" + key + (options.g ? "s" : options.i ? "i" : "");
        case 5100:
            var title = config.fTitle(key);
            return "title/" + title[config.titleImages[server]];
        case 5200:
            return "background/" + key + "t";
        case 5500:
            var SI = config.fSI(key);
            return "si/" + SI[config.SIImage];
        default:
            var item = config.fItem(type, key);
            return (options.px||"") + (options.s ? item[config.itemImagesSmall[server]] || item[config.itemImagesSmall[1]] : item[config.itemImages[server]] || item[config.itemImages[1]]);
    }
}
function gItem(type, key, server, amount, options, config) {
    return $(options.d ? "<div>" : "<span>").addClass("eis-sif-item").append(
        options.v>=78 ? gItemImage(type,key,server,options,config).toJQImg(1,1,options.z) : qImg(gItemImage(type, key, server, options, config)),
        amount && (amount > 1 || options.o) ? $("<span>").addClass("eis-sif-item-amount").text(amount) : "",
    ).attr("data-type", type).attr("data-key", key).attr("data-server", server).attr("data-game", options.gx);
}
function gItemBlock(type, key, server, amount, options, config) {
    return $("<div>").addClass("eis-sif-item-block" + (options.d ? " with-desc" : "")).append(
        qImg(gItemImage(type, key, server, options, config)),
        $("<div>").addClass("eis-sif-item-block-name").text(gItemName(type, key, server, config)),
        amount ? $("<span>").addClass("eis-sif-item-block-amount").text(amount) : "",
        options.d ? $("<p>").addClass("eis-sif-item-block-desc").html(gItemDesc(type, key, server, config)) : "",
    ).attr("data-type", type).attr("data-key", key).attr("data-server", server);
}
function gItemTooltip(type, key, server, config) {
    var t = $("<div>");
    $("<h5>").text(gItemName(type, key, server, config)).appendTo(t);
    for (var i = 1; i <= 3; i++) {
        if (i == server)
            continue;
        var name = gItemName(type, key, i, config);
        if (name) {
            $("<p>").addClass("eis-sif-note").text(name).appendTo(t);
        }
    }
    var desc = gItemDesc(type, key, server, config);
    if (desc) {
        $("<p>").html(desc).appendTo(t);
    }
    for (var i = 1; i <= 3; i++) {
        if (i == server) continue;
        if (desc = gItemDesc(type, key, i, config)) {
            $("<p>").addClass("eis-sif-note").html(desc).appendTo(t);
        }
    }
    var intro = gItemIntro(type, key, config);
    if (intro) {
        $("<h6>").text("补充介绍").appendTo(t);
        $("<p>").html(intro).appendTo(t);
    }
    return t;
}
function gItemDiff(type, key, server, options, config, before, rate, amount, addClass) {
    return $('<div class="eis-sif-item-diff '+(rate>0?"obtain":"consume")+'">').append(
        gItem(type, key, server, 0, $.extend({}, options, {s:true}), config),
        '<span class="eis-sif-item-diff-before">'+before,
        '<i class="fas fa-arrow-right">',
        addClass ? '<span class="eis-sif-item-diff-after '+addClass+'" data-before='+before+' data-rate='+Math.abs(rate)+'>'
            : '<span class="eis-sif-item-diff-after">'+(before+rate*amount)+' ('+(rate>0?'+':'')+(rate*amount)+')',
    );
}

function showDialogMessage(content, callbackOK, buttonText) {
    $(content).addClass("eis-sif-dialog").dialog({modal:true, position:{of:window}, resizable:false, draggable:false, closeText:buttonText || "确定", buttons:[
        {text:buttonText || "确定", click:function(){callbackOK(); $(this).dialog("close");}},
    ], close:function(event, ui) {
        $(this).dialog("destroy");
    }});
    $(window).resize();
}
function showQuickDialogMessage(content, title, width, callbackOK, buttonText) {
    showDialogMessage($("<div>").append(content).attr("title", title).attr("data-width", width), callbackOK || $.noop, buttonText);
}
function showDialogConfirm(content, callbackOK) {
    $(content).addClass("eis-sif-dialog").dialog({modal:true, position:{of:window}, resizable:false, draggable:false, closeText:"取消", buttons:[
        {text:"确定", click:function(){callbackOK(); $(this).dialog("close");}},
        {text:"取消", click:function(){$(this).dialog("close");}},
    ], close:function(event, ui) {
        $(this).dialog("destroy");
    }});
    $(window).resize();
}
function showFullDialog(selector) {
    $(window).resize();
    $(selector).dialog("open");
}
function closeDialogIfOpened(selector) {
    $(selector+".ui-dialog-content").dialog("close");
}

function refreshPageBar(filter, stay) {
    $(".eis-sif-pagebar").filter(filter||"*").each(function() {
        var pageCount = Math.ceil($($(this).attr("data-control")).children().length / $(this).attr("data-size"));
        $(this).empty().attr("data-pagecount", pageCount).append(
            $("<span>").append($('<i class="fas fa-fast-backward"></i>')).attr("data-page", 1),
            $("<span>").append($('<i class="fas fa-angle-left"></i>')).attr("data-page", 0),
            $("<span>").addClass("pages"),
            $("<span>").append($('<i class="fas fa-angle-right"></i>')).attr("data-page", 0),
            $("<span>").append($('<i class="fas fa-fast-forward"></i>')).attr("data-page", pageCount),
        );
        for (var i = Math.min(pageCount, 5); i > 0; i--) {
            $(this).find(".pages").append($("<span>").attr("data-page", 0));
        }
        $(this).find("[data-page]").attr("onclick", "pageBarButtonClick(this)").button();
        switchPage(this, stay ? Math.min($(this).attr("data-current") || 1, pageCount) : 1);
    });
}
function pageBarButtonClick(button) {
    switchPage($(button).parents(".eis-sif-pagebar"), parseInt($(button).attr("data-page")));
}
function switchPage(bar, page) {
    $(bar).attr("data-current", page);
    var pageCount = $(bar).attr("data-pagecount"), size = $(bar).attr("data-size");
    var barStart = pageCount < 5 || page < 3 ? 1 : page > pageCount - 2 ? pageCount - 4 : page - 2;
    $(bar).find(".pages").children().each(function(index) {
        $(this).text(barStart + index).attr("data-page", barStart + index);
    });
    $(bar).find("span").has(".fa-angle-left").attr("data-page", Math.max(page - 1, 1));
    $(bar).find("span").has(".fa-angle-right").attr("data-page", Math.min(page + 1, pageCount));
    $(bar).find("span[data-page]").each(function() {
        $(this).button("option", "disabled", page == $(this).attr("data-page"));
    });
    var rangeMax = size * page, rangeMin = rangeMax - size;
    $($(bar).attr("data-control")).children().each(function(index) {
        if (index < rangeMin || index >= rangeMax) {
            $(this).hide();
        } else {
            $(this).show();
            if ($(this).hasClass("eis-sif-page-hidden")) {
                $(this).trigger("eProduce").removeClass("eis-sif-page-hidden");
            }
        }
    });
}
function switchButtonGroup(button) {
    $(button).button("disable").addClass("active");
    $(button).siblings(":not([data-disable])").button("enable").removeClass("active");
}

function readNotice(noticeID) {
    var notice = notices[noticeID] || autoNotices[noticeID];
    $("#eis-sif-dialog-notice-title").empty().append(
        '<i class="fas fa-' + (notice[0] || "bullhorn") + '"></i> ',
        notice[2],
    );
    $("#eis-sif-dialog-notice-date").text(serverDate(notice[1], 3).getUTCDateTimeFull());
    $("#eis-sif-dialog-notice-contents").empty();
    $.each(codeProcess(notice[3]).split("\\n"), function(paragraphIndex, paragraph) {
        $("<p>").html(paragraph).addClass(paragraph.substring(0, 1) == "※" ? "eis-sif-note" : "").appendTo("#eis-sif-dialog-notice-contents");
    });
    showDialogMessage("#eis-sif-dialog-notice", $.noop, "关闭");
    var readNoticeIDs = (Cookies.get("readNotices") || "").split("s"), newIDs = [];
    $.each(readNoticeIDs.concat(noticeID), function(index, id) {
        if ((notices[id] || autoNotices[id]) && newIDs.indexOf(parseInt(id)) < 0) {
            newIDs.push(parseInt(id));
        }
    });
    newIDs.sort(function(id1, id2) {
        return id1 - id2;
    });
    Cookies.set("readNotices", newIDs.join("s"), {expires:90});
    var _paq = window._paq || [];
    _paq.push(["setCustomVariable", 1, "Read Notices", newIDs.join(","), "visit"]);
    _paq.push(["trackEvent", "Notices", "Read"]);
}

function sConfigByRangeID(rangeID) {
    if (rangeID > 0) return sConfig;
    return sConfigGlobal;
}
function openSettings() {
    $("#settings-list-page, #settings-list-global").empty();
    if (typeof sConfig == "undefined") {
        $("<p>").addClass("eis-sif-note").text("本页面没有设置选项。").appendTo("#settings-list-page");
    } else {
        initSettings("#settings-list-page", sConfig);
    }
    initSettings("#settings-list-global", sConfigGlobal);
    showDialogConfirm("#settings-dialog", saveSettings);
    $("#settings-tabs").tabs("refresh");
}
function initSettings(container, config) {
    var inputTemplate = function(rangeID, key, configDefinition) {
        switch (configDefinition.t) {
            case 1:
                var options = [], current = readSetting(rangeID, key);
                $.each(configDefinition.l, function(listIndex, listItem) {
                    options.push({v:listItem[0], t:listItem[1], s:listItem[0]==current});
                });
                return qSelect(options);
        }
    };
    $.each(config.l, function(sectionIndex, configSection) {
        var $form = $('<div class="eis-sif-form">');
        $.each(configSection.l, function(configIndex, configItem) {
            $("<p>").append(
                $("<label>").attr("for","settings-"+configItem.k).text(configItem.n),
                inputTemplate(config.r, configItem.k, config.s[configItem.k]).attr("id","settings-"+configItem.k),
            ).appendTo($form);
        });
        $('<section class="eis-sif-section-noborder">').append(
            $("<h4>").text(configSection.g),
            $form,
        ).appendTo(container);
    });
}
function saveSettings() {
    var saveIter = function(config) {
        var data = store.get("sif.settings"+config.r, {});
        $.each(config.s, function(key, configDefinition) {
            var input = $("#settings-"+key), value;
            switch (configDefinition.t) {
                case 1:
                    value = $("#settings-"+key).val();
                    var found = false;
                    $.each(configDefinition.l, function(listIndex, listItem) {
                        if (listItem[0] == value) {
                            found = true;
                            return false;
                        }
                    });
                    if (!found) value = undefined;
            }
            if (value !== undefined) {
                data[key] = value;
            }
        });
        store("sif.settings"+config.r, data);
        if (config.f) config.f();
    };
    if (typeof sConfig != "undefined") saveIter(sConfig);
    saveIter(sConfigGlobal);
}
function readSetting(rangeID, key) {
    var setting = store.get("sif.settings"+rangeID, {})[key];
    return setting !== undefined ? setting : readDefaultSetting(rangeID, key);
}
function readDefaultSetting(rangeID, key) {
    return sConfigByRangeID(rangeID).s[key].d;
}
function rDict(dictName, vocName, language) {
    if (!language) language = readSetting(0, dictName);
    return dicts[dictName][vocName][language];
}
function qDict(dictName, vocName) {
    return $('<span class="eis-sif-dict" data-dict="'+dictName+'" data-voc="'+vocName+'">').text(rDict(dictName,vocName));
}
function refreshDicts() {
    $.each(dicts, function(dictName, dict) {
        var language = readSetting(0, dictName);
        $('.eis-sif-dict[data-dict="'+dictName+'"]').text(function() {
            return rDict(dictName, $(this).attr("data-voc"), language);
        });
    });
}

function codeInit() {
    XBBCODE.addTags({
        "hb":{
            openTag:function(p,c){return '<span class="eis-sif-code-hb">◼ ';},
            closeTag:function(p,c){return '</span>';},
        },
        "hi":{
            openTag:function(p,c){return '<span class="eis-sif-code-hi">';},
            closeTag:function(p,c){return '</span>';},
        },
        "time":{
            openTag:function(p,c){return '<div class="eis-sif-timetip">';},
            closeTag:function(p,c){return '</div>';},
        },
        "banner_as":{
            openTag:function(p,c){return '<div style="text-align:center"><img src="'+c+'" width=420 height=128 style="max-width:100%;height:auto"/></div>';},
            closeTag:function(p,c){return '';},
            displayContent:false,
        },
        "c11":{
            openTag:function(p,c){return '<img class="eis-sif-code-card-1" src="/vio/sif/unit/icon1/'+Math.ceil(parseInt(c)/100)+'/'+c+'.png"/>';},
            closeTag:function(p,c){return '';},
            displayContent:false,
        },
        "asmp3":{
            openTag:function(p,c){
                var comma = p.indexOf(",");
                return '<a href="/sifas/interface/mp3.php?p='+p.substring(1,comma)+'&c='+p.substring(comma+1)+'" target="_blank">';
            },
            closeTag:function(p,c){return '</a>';},
        },
    });
    sCodeInitialized = true;
}
function codeProcess(text) {
    if (!sCodeInitialized) codeInit();
    return XBBCODE.process({text:text}).html.replace(/&lt;(.+?)&gt;/g, "<$1>");
}

function refreshLimit(limitType) {
    $.getJSON("/sif/interface/limit.php", {l:limitType}, function(data) {
        $(".limit-capacity-amount[data-limit=" + limitType + "]").text(data.current + "/" + data.max);
        limitStorage[limitType] = data;
    });
}

function showPosts(flowID, posts) {
    $(".eis-sif-flow[data-flow='" + flowID + "']").empty();
    $.each(posts, function(postIndex, post) {
        $("<div>").addClass("eis-sif-post").append(
            $("<p>").addClass("eis-sif-post-info").text(serverDate(post[2], 3).getUTCDateTimeFull()),
            $("<div>").addClass("eis-sif-post-content").append(function() {
                var c = [];
                $.each(post[1].split("\n"), function(paragraphIndex, paragraph) {
                    c.push($("<p>").append(paragraph));
                });
                return c;
            }),
        ).prependTo(".eis-sif-flow[data-flow='" + flowID + "']");
    });
}
function sendPost(flowID, post) {
    $.post("/sif/interface/comments.php", {f:flowID, p:post}, function(data) {
        showQuickDialogMessage($("<p>").text("已提交留言。"), "留言", 300);
        showPosts(flowID, data.posts);
    }, "json");
}

$(window).resize(function() {
    var windowWidth = $(window).width();
    $(".ui-dialog-content").each(function() {
        var bestWidth = parseInt($(this).attr("data-width"));
        $(this).dialog("option", {
            width:windowWidth > bestWidth + 50 ? bestWidth : windowWidth - 50,
            maxHeight:$(window).height() - 50,
            minHeight:$(this).attr("data-full") ? $(window).height() - 50 : 150,
        });
    });
});
$(window).scroll(function() {
    if (!$(".eis-sif-mark-top").length)
        return;
    if ($(window).scrollTop() > $(".eis-sif-mark-top").position().top) {
        $("#eis-sif-side-top").removeClass("eis-sif-hidden");
    } else {
        $("#eis-sif-side-top").addClass("eis-sif-hidden");
    }
});

$(document).ajaxStart(function() {
    $("#eis-sif-loading").removeClass("eis-sif-hidden");
    $("#eis-sif-loading-tip").position({of:window});
});
$(document).ajaxStop(function() {
    $("#eis-sif-loading").addClass("eis-sif-hidden");
});
$(document).ajaxError(function() {
    var message = $("<div>").attr("title", "出错了").attr("data-width", 350).append(
        $("<p>").text("非常抱歉，读取数据时出错。"),
        $("<p>").addClass("eis-sif-note").text("※ 您可以重试刚才的操作。如果问题反复出现，请通过主页上的链接反馈。抱歉给您带来不便。"),
    );
    showDialogMessage(message, $.noop);
});
window.onerror = function(message, source, lineno, colno, error) {
    var dialog = $("<div>").attr("title", "出错了").attr("data-width", 400).append(
        $("<p>").text("非常抱歉，执行页面脚本时出错。"),
        $("<p>").addClass("eis-sif-note").text("错误内容：" + message),
        $("<p>").addClass("eis-sif-note").text("※ 您可以尝试刷新页面。如果问题反复出现，请通过主页告知中的链接反馈。"),
    );
    showDialogMessage(dialog, $.noop);
}

$(document).ready(function() {
    $("img[data-resource1]").attr("data-src", function() {
        return getResourcePath1($(this).attr("data-resource1"));
    });
    $(".eis-jq-tabs").each(function() {
        var options = {};
        if ($(this).attr("data-scroll")) {
            options.heightStyle = "fill";
        }
        $(this).tabs(options);
    });
    $(".eis-jq-button").button();
    $(".eis-sif-button-group").children().button().click(function() {
        switchButtonGroup(this);
    }).filter("[data-default]").click();
    $(".eis-jq-accordion").each(function() {
        var options = {heightStyle:"content"};
        if (!$(this).attr("data-expand")) {
            options.collapsible = true;
            options.active = false;
        }
        if ($(this).attr("data-immediate")) {
            options.animate = false;
        }
        $(this).accordion(options);
    });
    $(".eis-sif-fold").each(function() {
        $(this).accordion({collapsible:true, heightStyle:"content", active:$(this).attr("data-expand") ? 0 : false});
    }).on("eFold", function() {
        $(this).accordion("option", "active", false);
    });
    $(".eis-sif-dialog-init").dialog({modal:true, position:{of:window}, resizable:false, draggable:false, closeText:"关闭", closeOnEscape:false, autoOpen:false});
    $(".eis-sif-switch").click(function() {
        $(this).attr("data-switch", 1 - $(this).attr("data-switch"));
        iSwitch($(this).attr("id"));
    });
    $("#eis-sif-side-top").click(function() {
        $(window).scrollTop(0);
    })
    $(".limit-capacity-name").text(function() {
        return $("#limit-capacity-name-" + $(this).attr("data-limit")).text();
    });
    $(".eis-sif-bar .limit-capacity-amount").each(function() {
        var limitType = $(this).attr("data-limit");
        if (limitStorage.limitType)
            return;
        refreshLimit(limitType);
    });
    $(".eis-sif-flow").each(function() {
        var flowID = $(this).attr("data-flow");
        $.getJSON("/sif/interface/comments.php", {f:flowID}, function(data) {
            showPosts(flowID, data.posts);
        });
    });
    if (!$("#settings-dialog").length) {
        $(".eis-sif-header-button[title='设置']").remove();
    }
    lazyload();
    refreshDicts();
});

var sConfigGlobal = {
    r:0,
    s:{
        "58":{t:1,l:[[4,"词库 1（张力）"],[3,"词库 2（热度）"]],d:3},
    },
    l:[
        {g:"语言设置",l:[{k:"58",n:"SIFAS 用语词库"}]},
    ],
    f:function() {
        refreshDicts();
    },
};
