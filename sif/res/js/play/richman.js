function p1Start() {
    playReadBatch(
        {fl:"Pfl",p1r:"P1r",p1mb:"P1mb",p1mu:"P1mu",p1npb:"P1npb",p1npu:"P1npu"},
        {},
        {p1jc:"P1Jc"},
    );
    playInitZero(["p1l","p1s"]);
    playInitEmptyObject(["o1e","o1h"]);
    sealNext();
}
function p1StartDisp() {
    $("#map").empty().append(
        '<img id="map-bg" src="/sif/res/img/u/stuff/play/bg/'+CS[1]+'.jpg">',
        '<img id="map-player">',
        '<img id="map-seal" src="/vio/'+(CT[3][0][3]==2?"sifas":"sif")+"/"+gItemImage(CT[3][0][0],CT[3][0][1],3,{s:true},gConfig)+'.png">',
    ).attr("data-size",playData.p1jc.length);
    $.each(playData.p1jc, function(cellIndex, cellType) {
        qImg("campaign/stuff/richman/c"+cellType).addClass("cell cell-"+cellType).attr("data-index",cellIndex).appendTo("#map");
    });
    $("#actions-item").empty();
    for (var i=playData.p1mb; i<=playData.p1mu; i++) {
        $('<span onclick="useItem('+i+')">'+i+' 步</span>').button().appendTo("#actions-item");
    }
}
function p1IntervalNext() {
    playData.f = playData.fl;
    playState.p1p = Math.floor(Math.random()*CJ[1].length);
    o1QuestCheck();
    o1QuestGen();
}
function p1IntervalDisp() {
    actionsDisp();
}
function p1End() {
    $("#play-dialog-end-score").append('<br>最终获得特殊收集物：'+playGetPocketItem(CT[3][0][0],CT[3][0][1]));
}
function p1ExchangeFinish() {
    actionsDisp();
}

function preIntervalNext() {
    if (playData.o1e.dfs && playData.f) {
        showQuickDialogMessage("今日随机前进次数尚未消费完毕，次数宝贵，不要浪费哦！", "提示", 300);
        return;
    }
    playIntervalNext();
}
function actionsDisp() {
    $("#map-player").attr("src",CS[2]+CJ[1][playState.p1p]+".png").attr("data-index",playData.p1l);
    $("#map-seal").attr("data-index",playData.p1s);
    playFlagChange("#flag-free", playData.f, "今日剩余 #", {p:true});
    playFlagChange("#flag-item", playGetPocketItem(CT[1][0][0],CT[1][0][1]), "剩余 #", {c:"#actions-item"});
    $(".inv-seal").text(playGetPocketItem(CT[3][0][0],CT[3][0][1]));
}
function sealNext() {
    if (CI[7]==-1) {
        playData.p1s = -1;
        return;
    }
    var b = Math.ceil(playData.p1npb*playData.p1jc.length/100), u = Math.floor(playData.p1npu*playData.p1jc.length/100);
    playData.p1s = (playData.p1s + Math.floor(Math.random()*(u-b+1)+b)) % playData.p1jc.length;
}
function playerMove(steps) {
    playData.p1l += steps;
    if (playData.p1l >= playData.p1jc.length) {
        playData.p1l %= playData.p1jc.length;
        playAddItems(CT[5], "经过起点");
    }
    playAddScore(steps, "前进步数");
    switch (playData.p1jc[playData.p1l]) {
        case 3:
            playAddScore(playData.p1r, "pt 格");
            break;
        case 4:
            playAddItems(playRandom(CT[4],1), "礼物格");
            break;
        case 5:
            playAddItems(playRandom(CT[4],2), "大型礼物格");
            break;
    }
    if (playData.p1s==playData.p1l) {
        playAddItems(CT[3], "获得特殊收集物");
        sealNext();
    }
    playAddDisp("已前进 "+steps+" 步。");
    o1QuestCheck();
}
function useFree() {
    playData.f--;
    if (playData.f<=0 && playData.o1e.ffm) {
        playerMove(playData.o1e.ffm);
    } else {
        var mu = playData.o1e.mu || playData.p1mu, mb = playData.o1e.mb || playData.p1mb;
        playerMove(Math.floor(Math.random()*(mu-mb+1))+mb);
    }
    actionsDisp();
}
function useItem(steps) {
    playSubtractItems(CT[1]);
    playerMove(steps);
    actionsDisp();
}
function exchangeItem() {
    playExchangeShow(CT[2], CT[1]);
}

function o1QuestGen() {
    if (playData.o1q) return;
    availableQuests = [];
    $.each(CJ[4], function(questKey, quest) {
        if (playData.o1h[questKey]) return;
        if (quest.t.p && playData.tp!=quest.t.p) return;
        availableQuests.push(questKey);
    });
    if (!availableQuests.length) return;
    playData.o1q = playRandom(availableQuests,1)[0];
    var quest = CJ[4][playData.o1q];
    if (quest.q.sn||quest.q.sx) playData.o1s = playData.s;
    if (quest.q.lp) playData.o1p = playData.tp;
    playData.o1e = quest.e;
    o1QuestGenDisp();
    o1QuestDialogGeneral("new");
}
function o1QuestGenDisp() {
    var quest = CJ[4][playData.o1q];
    $(".dialog-quest-conditions, .dialog-quest-limits, .dialog-quest-rewards").empty();
    if (quest.q.sn) o1QuestConditionAdd("至少获得 # 活动点数！", quest.q.sn);
    if (quest.q.sx) o1QuestConditionAdd("期间内不要获得多于 # 活动点数！", quest.q.sx);
    if (quest.q.lp) o1QuestLimitAdd("天数限制", quest.q.lp);
    $.each(quest.r, function(itemIndex, itemArray) {
        gItem(itemArray[0], itemArray[1], 3, itemArray[2], {px:itemArray[3]==2?"sifas/":""}, gConfig).appendTo(".dialog-quest-rewards");
    });
}
function o1QuestConditionAdd(pattern, number) {
    $("<p>").html(pattern.replace("#", "<b>"+number+"</b>")).appendTo(".dialog-quest-conditions");
}
function o1QuestLimitAdd(prefix, number) {
    $("<span>").html(prefix+"：<b>"+number+"</b>").appendTo(".dialog-quest-limits");
}
function o1QuestCheck() {
    if (!playData.o1q) return;
    var quest = CJ[4][playData.o1q];
    if (quest.q.sn && playData.s>=playData.o1s+quest.q.sn) o1QuestSucceed();
    if (quest.q.sx && playData.s>=playData.o1s+quest.q.sx) o1QuestFail("期间内获得的活动点数已超出限制。");
    if (quest.q.lp && playData.tp>=playData.o1p+quest.q.lp) {
        if (quest.q.sn) o1QuestFail("已超出天数限制。");
        if (quest.q.sx) o1QuestSucceed();
    }
}
function o1QuestSucceed() {
    var quest = CJ[4][playData.o1q];
    playAddItemsSilent(quest.r);
    o1QuestDialogGeneral("succeed");
    o1QuestClear();
}
function o1QuestFail(message) {
    o1QuestDialogGeneral("fail", $("<p>").html(message));
    o1QuestClear();
}
function o1QuestClear() {
    playData.o1q = null;
    playData.o1e = {};
}
function o1QuestDialogGeneral(category, message) {
    $("#dialog-quest-general-message").empty().append(message);
    $(".dialog-quest-general-text").text(function() {
        return $(this).attr("data-"+category);
    });
    showDialogMessage("#dialog-quest-general", $.noop);
}

$(document).ready(function() {
    $(".icon-item").attr("src", "/vio/"+(CT[1][0][3]==2?"sifas":"sif")+"/"+gItemImage(CT[1][0][0],CT[1][0][1],3,{s:true},gConfig)+".png");
});

var pConfig = {
    m:{
        s:{Ctpl:1,CTi:6,Pfl:3,P1r:6,P1mb:4,P1mu:5,P1npb:7,P1npu:8,P1Jc:2},
        f:{Ctpl:2,CTi:7,Pfl:3,P1r:6,P1mb:4,P1mu:5,P1npb:7,P1npu:8,P1Jc:2},
        c:{P1npb:7,P1npu:8,P1Jc:2},
    },
    mo:{f:9,c:10},
    s:{
        "Ctpl":{b:1,n:"天数"},
        "Pfl":{n:"每日随机前进次数"},
        "P1mb":{n:"每次最小前进步数"},"P1mu":{n:"每次最大前进步数"},
        "P1r":{n:"pt 格奖励活动点数"},
    },
    c:[
        {g:"常用设置",l:[{k:"Ctpl",vi:1},{k:"Pfl",vi:3}]},
        {g:"初始道具",ci:3},
        {g:"高级设置",l:[{k:"P1mb",vi:4},{k:"P1mu",vi:5},{k:"P1r",vi:6}]},
    ],
    fStart:p1Start, fStartDisp:p1StartDisp,
    fIntervalNext:p1IntervalNext, fIntervalDisp:p1IntervalDisp,
    fEnd:p1End,
    fExchangeFinish:p1ExchangeFinish,
};
