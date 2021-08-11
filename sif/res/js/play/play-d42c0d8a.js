var playModeNames = {s:"标准模式", f:"自由模式", c:"自定义模式"};
var playData, playState;

function playReadI(code) {
    return CI[pConfig.m[playData.m][code]] || (playData.m=="c"?parseInt($("#play-customize-code-"+code).val())||0:0);
}
function playReadJ(code) {
    return CJ[pConfig.m[playData.m][code]] || [];
}
function playReadBatch(I, S, J) {
    $.each(I, function(Ikey, Iref) {
        playData[Ikey] = playReadI(Iref);
    });
    $.each(J, function(Jkey, Jref) {
        playData[Jkey] = playReadJ(Jref);
    });
}
function playInitNull(N) {
    $.each(N, function(Nindex, Nkey) {
        playData[Nkey] = [null];
    });
}
function playInitEmptyObject(O) {
    $.each(O, function(Oindex, Okey) {
        playData[Okey] = {};
    });
}
function playInitZero(Z) {
    $.each(Z, function(Zindex, Zkey) {
        playData[Zkey] = 0;
    });
}
function playStart() {
    playData = {};
    playData.m = $("#play-mode-buttons .active").attr("data-mode");
    playData.i = {};
    if (playData.m=="c") {
        var validated = true, reason;
        $.each(pConfig.s, function(code, definition) {
            var min = definition.b || CI[definition.bi] || 0, max = definition.u || CI[definition.ui];
            var value = $("#play-customize-code-"+code).val();
            switch (definition.t) {
                default:
                    value = parseInt(value);
                    if (value<min || (max&&value>max)) {
                        validated = false;
                        reason = "［"+definition.n+"］"+"参数设置无效。";
                        return false;
                    }
            }
        });
        if (!validated) {
            showQuickDialogMessage('<p>'+reason+'</p>', "自定义设置", 300);
            return;
        }
        $(".play-customize-item").each(function() {
            playAddItemsSilent([[$(this).attr("data-type"), $(this).attr("data-key"), parseInt($(this).val())]]);
        });
    }
    playReadBatch({tpl:"Ctpl",tsl:"Ctsl"}, {}, {});
    playInitZero(["s","tp","ts"]);
    playAddItemsSilent(CT[pConfig.m[playData.m].CTi]);
    playState = {};
    pConfig.fStart();
    $("#play-mode").hide();
    $("#play-main").show();
    playStartDisp();
    playIntervalNext();
}
function playStartDisp() {
    $(".play-disp-mode").text(playModeNames[playData.m]);
    $(".play-disp-score").text(playData.s);
    pConfig.fStartDisp();
}
function playIntervalNext() {
    playData.ts++;
    if (playData.ts>playData.tsl) {
        playData.ts = 1;
    }
    if (playData.ts==1) {
        playData.tp++;
    }
    pConfig.fIntervalNext();
    if (playData.tp>playData.tpl) {
        playEnd();
        if ($("#play-dialog-add").hasClass("ui-dialog-content")) {
            $("#play-dialog-add").dialog("moveToTop");
        }
    }
    playIntervalDisp();
}
function playIntervalDisp() {
    $(".play-disp-interval-primary").text(playData.tp);
    $(".play-disp-interval-sub").text(playData.ts+"/"+playData.tsl);
    var primaryRemain = playData.tpl - playData.tp;
    $(".play-flag-interval-primary").text("剩余 "+primaryRemain+" 天");
    pConfig.fIntervalDisp();
}
function playQuitConfirm() {
    showDialogConfirm("#play-dialog-quit-confirm", playEnd);
}
function playEnd() {
    $("#play-dialog-end-score").text("最终活动点数："+playData.s);
    pConfig.fEnd();
    showDialogMessage("#play-dialog-end", $.noop);
    $("#play-main").hide();
    $("#play-mode").show();
}
function playAddScore(score, text) {
    playData.s += score;
    $(".play-disp-score").text(playData.s);
    $("#play-dialog-add-score").show();
    $('<li><span class="play-dialog-add-score-detail-score">+'+score+'</span><span class="play-dialog-add-score-detail-text">'+text+'</span></li>').appendTo("#play-dialog-add-score-details");
}
function playGetPocketItem(type, key) {
    return playData.i[type+"-"+key] || 0;
}
function playRandom(list, count) {
    var r = [];
    for (var i=count; i>0; i--) {
        r.push(list[Math.floor(Math.random()*list.length)]);
    }
    return r;
}
function playSubtractItems(list) {
    $.each(list, function(itemIndex, itemArray) {
        var before = playGetPocketItem(itemArray[0], itemArray[1]);
        gItemDiff(itemArray[0], itemArray[1], 3, {px:itemArray[3]==2?"sifas/":""}, gConfig, before, -1, itemArray[2]).appendTo("#play-dialog-add-diff");
        playData.i[itemArray[0]+"-"+itemArray[1]] = before - itemArray[2];
    });
}
function playAddItemsSilent(list) {
    $.each(list, function(itemIndex, itemArray) {
        playData.i[itemArray[0]+"-"+itemArray[1]] = playGetPocketItem(itemArray[0], itemArray[1]) + itemArray[2];
    });
}
function playAddItems(list, text) {
    var $section = $('<section><h4 class="eis-sif-dialog-section-header">'+text+'</h4></section>');
    $.each(list, function(itemIndex, itemArray) {
        playData.i[itemArray[0]+"-"+itemArray[1]] = playGetPocketItem(itemArray[0], itemArray[1]) + itemArray[2];
        gItemBlock(itemArray[0], itemArray[1], 3, itemArray[2], {d:true, px:itemArray[3]==2?"sifas/":""}, gConfig).appendTo($section);
    });
    $("#play-dialog-add-container").append($section);
}
function playAddDisp(info) {
    $("<p>").text(info).prependTo("#play-dialog-add-info");
    showDialogMessage("#play-dialog-add", function(){
        $("#play-dialog-add-score").hide();
        $("#play-dialog-add-info, #play-dialog-add-diff, #play-dialog-add-score-details, #play-dialog-add-container").empty();
    });
}
function playExchangeShow(consumeItems, obtainItems) {
    $("#play-dialog-exchange-img, #play-dialog-exchange-diff").empty();
    $.each(consumeItems, function(itemIndex, itemArray) {
        gItem(itemArray[0], itemArray[1], 3, 0, {px:itemArray[3]==2?"sifas/":""}, gConfig).appendTo("#play-dialog-exchange-img");
        gItemDiff(itemArray[0], itemArray[1], 3, {gx:itemArray[3], px:itemArray[3]==2?"sifas/":""}, gConfig, playGetPocketItem(itemArray[0], itemArray[1]), -itemArray[2], 0, "play-dialog-exchange-dynamic").appendTo("#play-dialog-exchange-diff");
    });
    $('<i class="fas fa-long-arrow-alt-right">').appendTo("#play-dialog-exchange-img");
    $.each(obtainItems, function(itemIndex, itemArray) {
        gItem(itemArray[0], itemArray[1], 3, 0, [], gConfig).appendTo("#play-dialog-exchange-img");
        gItemDiff(itemArray[0], itemArray[1], 3, {px:itemArray[3]==2?"sifas/":""}, gConfig, playGetPocketItem(itemArray[0], itemArray[1]), itemArray[2], 0, "play-dialog-exchange-dynamic").appendTo("#play-dialog-exchange-diff");
    });
    $("#play-dialog-exchange-amount").val(1);
    playExchangeInput();
    showDialogConfirm("#play-dialog-exchange", playExchangeFinish);
}
function playExchangeInput() {
    $(".play-dialog-exchange-dynamic").text(function() {
        var diff = ($(this).parent().hasClass("obtain")?1:-1) * $("#play-dialog-exchange-amount").val() * $(this).attr("data-rate");
        return (parseInt($(this).attr("data-before"))+diff) + " (" + (diff>0?"+":"") + diff + ")";
    });
}
function playExchangeFinish() {
    var amount = parseInt($("#play-dialog-exchange-amount").val());
    if (amount<=0) {
        showQuickDialogMessage('<p>输入的交换数量无效。未交换道具。</p>', "道具交换", 300);
        return;
    }
    var validated = true;
    $("#play-dialog-exchange-diff .eis-sif-item-diff.consume").each(function() {
        var item = $(this).find(".eis-sif-item"), type = $(item).attr("data-type"), key = $(item).attr("data-key");
        var rate = $(this).find(".eis-sif-item-diff-after").attr("data-rate");
        if (playGetPocketItem(type, key)-rate*amount<0) {
            validated = false;
            return false;
        }
    });
    if (!validated) {
        showQuickDialogMessage('<p>持有道具数无法满足输入的交换数量。未交换道具。</p>', "道具交换", 300);
        return;
    }
    var addList = [];
    $("#play-dialog-exchange-diff .eis-sif-item-diff").each(function() {
        var item = $(this).find(".eis-sif-item"), type = $(item).attr("data-type"), key = $(item).attr("data-key"), game = $(item).attr("data-game");
        var rate = $(this).find(".eis-sif-item-diff-after").attr("data-rate");
        if ($(this).hasClass("obtain")) {
            addList.push([type, key, rate*amount]);
        } else {
            playSubtractItems([[type, key, rate*amount, game]]);
        }
    });
    playAddItems(addList, "交换获得");
    pConfig.fExchangeFinish();
    playAddDisp("已完成交换。");
}
function playFlagChange(flag, value, pattern, buttonOption) {
    if (value) {
        $(flag).removeClass("disabled");
    } else {
        $(flag).addClass("disabled");
    }
    $(flag).text(pattern.replace("#", value));
    if (buttonOption.p) {
        $(flag).parent().button("option", "disabled", !value);
    }
    if (buttonOption.c) {
        $(buttonOption.c).children().each(function() {
            $(this).button("option", "disabled", !value);
        });
    }
}

$(document).ready(function() {
    if (typeof pConfig == "undefined") return;
    if (pConfig.m) {
        var buttonTemplate = function(code, text) {
            return $("<span>").text(text).attr("data-mode",code).button().click(function() {
                switchButtonGroup(this);
                $(".play-mode-desc").hide().filter("[data-mode="+code+"]").show();
                if (code=="c") $("#play-customize").show(); else $("#play-customize").hide();
            });
        }
        $.each(["s","f","c"], function(modeIndex, modeCode) {
            if (pConfig.m[modeCode] && !(pConfig.mo&&pConfig.mo[modeCode]&&!CI[pConfig.mo[modeCode]])) {
                buttonTemplate(modeCode, playModeNames[modeCode]).appendTo("#play-mode-buttons");
            }
        });
        $("#play-mode-buttons :first-child").click();
    }
    var inputTemplate = function(ref, definition) {
        var min = definition.b || CI[definition.bi] || 0, max = definition.u || CI[definition.ui];
        var value = ref.d || CI[ref.vi] || (ref.vu?max:min);
        switch (definition.t) {
            default:
                return $('<input type="number">').attr("min",min).attr("max",max).val(value);
        }
    }
    $.each(pConfig.c, function(sectionIndex, section) {
        var $form = $('<div class="eis-sif-form">');
        if (section.ci) {
            $.each(CJ[section.ci], function(itemIndex, item) {
                $("<p>").append(
                    $('<label for="play-customize-item-'+item[0]+'-'+item[1]+'">').text(gItemName(item[0], item[1], 3, gConfig)),
                    $('<input id="play-customize-item-'+item[0]+'-'+item[1]+'" class="play-customize-item" data-type='+item[0]+' data-key='+item[1]+' type="number" min=0>').val(0),
                ).appendTo($form);
            });
        } else {
            $.each(section.l, function(refIndex, ref) {
                var definition = pConfig.s[ref.k];
                $("<p>").append(
                    $('<label for="play-customize-code-'+ref.k+'">').text(definition.n),
                    inputTemplate(ref, definition).attr("id","play-customize-code-"+ref.k),
                ).appendTo($form);
            });
        }
        $("#play-customize").append(
            $("<h5>").text(section.g),
            $("<div>").append($form),
        );
    });
    $("#play-customize").accordion("refresh").accordion("option","active",0);
    $("#play-main, #play-dialog-add-score").hide();
});

var gConfig = $.extend({}, gConfigDefault, {
    itemNames:[null,null,null,0], itemDesc:[null,null,null,3], itemImages:[null,null,null,1], itemImagesSmall:[null,null,null,2],
});
