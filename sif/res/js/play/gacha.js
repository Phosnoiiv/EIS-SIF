var gachaStorage={}, cardStorage={};
var c={}, playDataDefault={};
const RARITY_LIMIT=11, RARITY_SELECT=12;
var rarityListBox = [RARITY_SELECT,RARITY_LIMIT,4,5,3,2];
var gachaTypes = {
    refreshGacha:[null,0,1,1,0,0,0,1],
    shuffleResults:[null,1,1,1,1,0,1,1],
};
function loadGacha(code) {
    $.getJSON("/sif/interface/gacha.php", {c:code}, function(data) {
        gachaStorage[code] = data;
        switchGacha(code);
    });
}
function loadCards(cardIDs, callback) {
    $.post("/sif/interface/cards.php", {c:cardIDs.join()}, function(data) {
        $.each(data.cards, function(cardID, card) {
            cardStorage[cardID] = card;
        });
        callback();
    });
}
function checkGachaCardsLoaded(callback) {
    var cardIDs = [];
    $.each(c.u, function(rarity, rarityLineup) {
        $.each(rarityLineup, function(cardIndex, cardID) {
            if (cardStorage[cardID]) return;
            cardIDs.push(cardID);
        });
    });
    if (!cardIDs.length) return true;
    loadCards(cardIDs, callback);
    return false;
}
function switchPage1(page) {
    $(".page").hide();
    $("#page-"+page).show();
}
function switchGacha(code) {
    if (!gachaStorage[code]) {
        loadGacha(code);
        return;
    }
    c.c = code;
    c.l = gachas[code];
    c.r = gachaStorage[code].gacha;
    c.u = gachaStorage[code].lineup;
    c.sm = []; c.sc = [];
    $("#gacha-title, .gacha-name").text(c.l[0]);
    if (c.l[1]==3) { // BOX
        c.rb = gachaStorage[code].box;
        if (c.rb[1]) {
            if (!checkGachaCardsLoaded(function(){switchGacha(code);})) return;
            $.each(c.u[RARITY_SELECT], function(cardIndex, cardID) {
                var card = cardStorage[cardID];
                if (c.rb[1]==1) {
                    if (c.sm.indexOf(card[1])<0) {
                        c.sm.push(card[1]);
                    }
                }
            });
        }
        $("#box-remain").show();
        $("#box-reset").empty().append("gui/secretbox/Button_BoxReset_Limit-01_on-01(cs)".toJQImg(1,1).attr("onclick","resetBox()")).show();
    } else {
        $("#box-remain, #box-reset").hide();
    }
    refreshGacha();
    refreshCosts();
    closeDialogIfOpened("#dialog-list");
    switchPage1("gacha");
}
function refreshGacha() {
    var needSelect = (c.sm.length||c.sc.length) && !playData.s[c.c];
    if (needSelect) {
        $("#cost-buttons").hide();
        $("#select-button").empty().append(
            ("gui/secretbox/Button_BoxUR_Choice-"+getSelectButtonAsset()).toJQImg(1,1).attr("onclick","beginSelect()"),
        ).show();
    } else {
        $("#cost-buttons").show();
        $("#select-button").hide();
    }
    switch (c.l[1]) {
        case 3: // BOX
            var initCapacity = boxes[c.rb[0]], playedCapacity = playData.tbc[c.c] || $.extend({},playDataDefault.tbc);
            c.tb = {i:{2:initCapacity[5],3:initCapacity[4],4:initCapacity[2],5:initCapacity[3]}};
            c.tb.i[RARITY_LIMIT] = initCapacity[1];
            c.tb.i[RARITY_SELECT] = initCapacity[0];
            c.tb.rs = 0;
            $("#box-remain-list").empty();
            $.each(rarityListBox, function(rarityIndex, rarityNum) {
                if (!c.tb.i[rarityNum]) return;
                $('<div class="box-remain-rarity" data-rarity="'+rarityNum+'">').append(
                    ("gui/secretbox/Icon_Rarity"+getRarityIconAsset(rarityNum)).toJQImg(1,1),
                    $("<span>").text(rarityNum==RARITY_SELECT&&needSelect ? "-" : (c.tb.i[rarityNum]-playedCapacity[rarityNum])),
                ).appendTo("#box-remain-list");
                c.tb.rs += rarityNum==RARITY_SELECT&&needSelect ? 0 : (c.tb.i[rarityNum]-playedCapacity[rarityNum]);
            });
            $(".box-remain-sum").text(c.tb.rs);
            break;
    }
}
function refreshCosts(keepSwitch) {
    var useLoveca = false, hasTicket = false;
    $.each(costs[c.r[0]], function(costIndex, cost) {
        if (cost[0]==3001) useLoveca = true;
        else if (playGetPocketItem(cost[0],cost[1])>0) hasTicket = true;
    });
    if (useLoveca && hasTicket) {
        $("#cost-switch").show();
        if (keepSwitch) $("#cost-switch .ui-button.active").click();
        else $("#cost-switch").children(":first-child").click();
    } else {
        $("#cost-switch").hide();
        switchCosts(0);
    }
}
function switchCosts(panel) {
    $("#cost-buttons").empty();
    $.each(costs[c.r[0]], function(costIndex, cost) {
        if (panel>0 && panel!=1 && cost[0]==3001) return;
        if (panel>0 && panel==1 && cost[0]!=3001) return;
        if (cost[0]!=3001 && playGetPocketItem(cost[0],cost[1])<=0) return;
        if (c.l[1]==3) {
            if (c.tb.rs<cost[3]) return;
        }
        var optionString = "";
        $('<div class="sif-box-button">').append(
            ("gui/secretbox/Scout-01_Button-"+getCostButtonAsset(cost[3]>1,false,false,cost[0]==3001&&cost[1]==1,false)).toJQImg(1,1)
                .attr("onclick","confirmDraw("+cost[0]+","+cost[1]+","+cost[2]+","+cost[3]+",{"+optionString+"})"),
            cost[3]>1 ? $('<span class="number">').text(cost[3]) : null,
            $('<div class="cost">').append(
                gItemImage(cost[0],cost[1],1,{s:true},gConfig).toJQImg(1,1).addClass("item"),
                $('<span>').text(cost[2]),
            ),
        ).appendTo("#cost-buttons");
    });
}
function beginSelect() {
    var caption;
    if (c.sm.length) {
        caption = "请选择要设定为选择 UR 的成员";
    }
    $("#dialog-select-caption").text(caption);
    $("#dialog-select-choices").empty();
    if (c.sm.length) {
        $.each(c.sm, function(memberIndex, memberID) {
            var member = members[memberID];
            $('<span class="select-choice" data-choice='+memberID+'>').append(
                ("member/n"+member[4]).toJQImg(1,1),
            ).appendTo("#dialog-select-choices");
        });
    }
    showDialogConfirm("#dialog-select", function() {
        finishSelect($(".select-choice.selected").attr("data-choice"));
    });
    $("#dialog-select+.ui-dialog-buttonpane .ui-button:first-of-type").button("disable");
}
function finishSelect(choice) {
    playData.s[c.c] = choice;
    refreshGacha();
}
function confirmDraw(type, key, amount, count, options) {
    $(".gacha-cost-diff").empty();
    gItemDiff(type,key,1,{},gConfig,playGetPocketItem(type,key),-amount,1,"").appendTo(".gacha-cost-diff");
    showDialogConfirm("#dialog-confirm-draw", function() {
        draw(type, key, amount, count, options);
    });
}
function draw(type, key, amount, count, options) {
    playSubtractItems([[type,key,amount]]);
    $("#results").empty();
    switchPage1("result");
    var results = [];
    if (c.l[1]==3) {
        var playedCapacity = playData.tbc[c.c] || $.extend({},playDataDefault.tbc);
        for (var i=count; i>0; i--) {
            var remainCapacity = {};
            $.each(c.tb.i, function(rarityNum, rarityInitCapacity) {
                remainCapacity[rarityNum] = rarityInitCapacity - (playedCapacity[rarityNum]||0);
            });
            var decidedRarity = decideRarity(remainCapacity);
            playedCapacity[decidedRarity] = (playedCapacity[decidedRarity]||0) + 1;
            var decidedCard = (decidedRarity==RARITY_SELECT && c.rb[1]==2) ? playData.s[c.c] : decideCard(c.u[decidedRarity].slice(), decidedRarity==RARITY_SELECT && c.rb[1]==1 ? playData.s[c.c] : null);
            results.push([decidedCard]);
        }
        playData.tbc[c.c] = playedCapacity;
    }
    if (gachaTypes.shuffleResults[c.l[1]]) results = results.shuffle();
    $.each(results, function(resultIndex, result) {
        gItem(1001,result[0],1,1,{v:78},gConfig).appendTo("#results");
    });
    if (gachaTypes.refreshGacha[c.l[1]]) refreshGacha();
    refreshCosts(true);
}
function decideRarity(rates) {
    var sum = 0;
    $.each(rates, function(rarity, rate) {
        sum += rate;
    });
    var rand = Math.random() * sum, result;
    $.each(rates, function(rarity, rate) {
        result = rarity;
        rand -= rate;
        if (rand<0) return false;
    });
    return result;
}
function decideCard(cardIDs, selectedMemberID) {
    if (selectedMemberID) {
        for (var i=cardIDs.length-1; i>=0; i--) if (cardStorage[cardIDs[i]][1]!=selectedMemberID) cardIDs.splice(i,1);
    }
    return cardIDs[Math.floor(Math.random()*cardIDs.length)];
}
function closeResult() {
    switchPage1("gacha");
}
function resetBox() {
    delete playData.tbc[c.c];
    delete playData.s[c.c];
    refreshGacha();
    refreshCosts(true);
}
function switchListProject(project) {
    if (project==0) {
        $(".gacha-list-item").show();
        return;
    }
    $(".gacha-list-item[data-project!="+project+"]").hide();
    $(".gacha-list-item[data-project="+project+"]").show();
}
function getCostButtonAsset(isMulti, isDisabled, isFriend, isPaid, isNarrow) {
    return isMulti ? "02(cs)" : "01(cs)";
}
function getRarityIconAsset(rarity) {
    if (rarity==RARITY_SELECT) return "ChoiceUR-01(cs)";
    if (rarity==RARITY_LIMIT) return "LimitUR-01";
    return [null,null,"R","SR","UR","SSR"][rarity]+"-01";
}
function getSelectButtonAsset() {
    switch (c.l[1]) {
        case 3: return "01(cs)";
    }
}

$(document).ready(function() {
    playData = {i:{},s:{},tbc:{}};
    playDataDefault = {tbc:{2:0,3:0,4:0,5:0}};
    playDataDefault.tbc[RARITY_LIMIT] = 0;
    playDataDefault.tbc[RARITY_SELECT] = 0;
    playAddItemsSilent(initItems);
    $.each(gachas, function(code, gacha) {
        $('<div class="gacha-list-item" onclick="switchGacha('+code+')" data-project='+gacha[2]+'>').append(
            ("gacha/banner/"+gacha[3]).toJQImg(3,1,true),
            $('<span class="gacha-list-item-name">').text(gacha[0]),
        ).appendTo("#gacha-list");
    });
    $("#dialog-select-choices").on("click", ".select-choice", function() {
        $(".select-choice").removeClass("selected").addClass("unselected");
        $(this).removeClass("unselected").addClass("selected");
        $("#dialog-select+.ui-dialog-buttonpane .ui-button:first-of-type").button("enable");
    });
    lazyload();
    switchPage1("");
    showFullDialog("#dialog-list");
});

var gConfig = $.extend({}, gConfigDefault, {
    itemNames:[null,0,1,2], itemDesc:[null,9,10,11], itemIntro:12, itemImages:[null,3,5,7], itemImagesSmall:[null,4,6,8],
    fUnit:function(cardID){return cardStorage[cardID];},
});
