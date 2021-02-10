function p2Start() {
    playReadBatch(
        {fl:"Pfl",p2c:"P2c",p2rb:"P2rb",p2ru:"P2ru",p2rp:"P2rp",p2rl:"P2rl",p2db:"P2db",p2du:"P2du"},
        {},
        {p2ji:"P2Ji",p2jr:"P2Jr"}
    );
    playInitNull(["p2r","p2hf","p2hi","p2hp","p2hl"]);
    playInitZero(["p2hpr"]);
    for (var i=1; i<=playData.p2c; i++) {
        playData.p2hf[i] = [];
        playData.p2hi[i] = [];
    }
}
function p2StartDisp() {
    $("#board, #history-popularity").empty();
    for (var i=1; i<=playData.p2c; i++) {
        $('<div id="stamp-'+i+'" class="stamp" data-stamp='+i+' onclick="stampSelect('+i+')">').append(
            $('<div id="stamp-reward-'+i+'" class="stamp-reward">'),
            $('<div class="stamp-img">').append(qStamp(i)),
            $('<div class="stamp-record">').append(qImg("campaign/cut/aquarium/i1"),'<span id="stamp-record-1-'+i+'">'),
            $('<div class="stamp-record">').append(qImg("campaign/cut/aquarium/i2"),'<span id="stamp-record-2-'+i+'">'),
        ).appendTo("#board");
        qStamp(i).attr("id","history-popularity-"+i).addClass("stamp-result").appendTo("#history-popularity");
    }
}
function p2IntervalNext() {
    if (playData.ts==1) {
        if (playData.tp>1) {
            if (playData.tp>2) {
                var popularityCount = [null], popularityList = [];
                for (var i=1; i<=playData.p2c; i++) {
                    popularityCount[i] = 0;
                }
                for (var i=1; i<playData.tp; i++) {
                    popularityCount[playData.p2hp[i]]++;
                }
                for (var i=1; i<=playData.p2c; i++) {
                    if (!popularityCount[i]) {
                        popularityList.push(i);
                    }
                }
                if (!popularityCount[playData.p2hl[playData.tp-2]] || !popularityList.length) {
                    playData.p2hp[playData.tp-1] = playData.p2hl[playData.tp-2];
                    while (playData.p2hp[playData.tp-1]==playData.p2hp[playData.tp-2]) {
                        playData.p2hp[playData.tp-1] = Math.floor(Math.random()*playData.p2c)+1;
                    }
                } else {
                    playData.p2hp[playData.tp-1] = popularityList[Math.floor(Math.random()*popularityList.length)];
                }
            } else {
                playData.p2hp[playData.tp-1] = 1;
            }
            playData.p2hl[playData.tp-1] = Math.floor(Math.random()*playData.p2c)+1;
            $("<p>").append("昨日的人气印章：", qStamp(playData.p2hp[playData.tp-1]).addClass("stamp-result"), "　幸运印章：", qStamp(playData.p2hl[playData.tp-1]).addClass("stamp-result")).appendTo("#play-dialog-add-info");
            if (playData.p2hf[playData.p2hp[playData.tp-1]][playData.tp-1] || playData.p2hi[playData.p2hp[playData.tp-1]][playData.tp-1]) {
                playAddScore(playData.p2rp, "昨日收集到人气印章");
            }
            if (playData.p2hf[playData.p2hl[playData.tp-1]][playData.tp-1] || playData.p2hi[playData.p2hl[playData.tp-1]][playData.tp-1]) {
                playAddScore(playData.p2rl, "昨日收集到幸运印章");
            }
            var addList = [];
            if (playData.p2db>0) {
                addList.push([CT[2][0][0], CT[2][0][1], Math.floor(Math.random()*(playData.p2du-playData.p2db+1))+playData.p2db]);
            }
            if (playData.p2ji[playData.tp]>0) {
                addList.push([CT[1][0][0], CT[1][0][1], playData.p2ji[playData.tp]]);
            }
            if (playData.p2jr[playData.tp]>0) {
                addList.push([CT[3][0][0], CT[3][0][1], playData.p2jr[playData.tp]]);
            }
            if (addList.length) {
                playAddItems(addList, "今日助力礼物");
            }
            playAddDisp("新的一天！");
        }
        playData.f = playData.fl;
        for (var i=1; i<=playData.p2c; i++) {
            playData.p2hf[i][playData.tp] = 0;
            playData.p2hi[i][playData.tp] = 0;
        }
    }
    for (var i=1; i<=playData.p2c; i++) {
        playData.p2r[i] = Math.floor(Math.random() * (playData.p2ru-playData.p2rb+1)) + playData.p2rb;
    }
    stampSelectClear();
}
function p2IntervalDisp() {
    for (var i=1; i<=playData.p2c; i++) {
        $("#stamp-reward-"+i).text(playData.p2r[i]+"pt");
    }
    $("#history-yesterday").empty();
    if (playData.tp>1) {
        $("#history-yesterday").append("昨日的人气印章：", qStamp(playData.p2hp[playData.tp-1]).addClass("stamp-result"), "　幸运印章：", qStamp(playData.p2hl[playData.tp-1]).addClass("stamp-result"));
    }
    $("#history-popularity>*").addClass("stamp-unpopular");
    for (var i=1; i<playData.tp; i++) {
        $("#history-popularity-"+playData.p2hp[i]).removeClass("stamp-unpopular");
    }
    if (playData.p2hpr<CJ[4].length) {
        var popularCount = $("#history-popularity>:not(.stamp-unpopular)").length;
        $("#history-popularity-goal").text("目标 "+(playData.p2hpr+1)+"/"+CJ[4].length+"："+popularCount+"/"+CJ[4][playData.p2hpr][0]+" 个印章成为人气印章");
        $("#button-history-popularity-reward").button("option", "disabled", popularCount<CJ[4][playData.p2hpr][0]);
    } else {
        $("#history-popularity-goal").text("已完成全部目标。");
        $("#button-history-popularity-reward").button("disable");
    }
    stampRecordDisp();
}
function p2ExchangeFinish() {
    actionsDisp();
}

function stampSelect(stampID) {
    if ($("#stamp-"+stampID).hasClass("selected")) {
        stampSelectClear();
    } else {
        $(".stamp").removeClass("selected").addClass("unselected");
        $("#stamp-"+stampID).removeClass("unselected").addClass("selected");
        actionsDisp();
    }
}
function stampSelectClear() {
    $(".stamp").removeClass("selected unselected");
    $("#button-free").button("disable");
    actionsDisp();
}
function stampRecordDisp() {
    for (var i=1; i<=playData.p2c; i++) {
        $("#stamp-record-1-"+i).text(playData.p2hf[i][playData.tp]);
        $("#stamp-record-2-"+i).text(playData.p2hi[i][playData.tp]);
    }
}
function getStampSelected() {
    return $(".stamp.selected").attr("data-stamp");
}
function flagSwitch(flag, value, pattern, switchParentButton) {
    if (value) {
        $(flag).removeClass("disabled");
    } else {
        $(flag).addClass("disabled");
    }
    if (pattern) {
        $(flag).text(pattern.replace("#", value));
    }
    if (switchParentButton) {
        $(flag).parent().button("option", "disabled", !value);
    }
}
function actionsDisp() {
    var stampID = getStampSelected();
    if (stampID) {
        $("#actions-disabled").hide();
    } else {
        $("#actions-disabled").show();
    }
    $("#button-free").button("option", "disabled", !playData.f || !stampID || playData.p2hf[stampID][playData.tp]>0);
    flagSwitch("#flag-free", playData.f, "今日剩余 #");
    flagSwitch("#flag-item", playData.i[CT[1][0][0]+"-"+CT[1][0][1]]||0, "剩余 #", true);
    flagSwitch("#flag-refresh", playData.i[CT[3][0][0]+"-"+CT[3][0][1]]||0, "剩余 #", true);
}
function useFree() {
    var stampID = getStampSelected();
    playData.f--;
    playData.p2hf[stampID][playData.tp]++;
    playAddScore(playData.p2r[stampID], "收集印章");
    if (!playData.f) {
        playAddItems([CT[4][Math.floor(Math.random()*CT[4].length)]], "活跃度奖励");
    }
    stampRecordDisp();
    actionsDisp();
    playAddDisp("已收集印章。");
}
function useItem() {
    var stampID = getStampSelected();
    playSubtractItems(CT[1]);
    playData.p2hi[stampID][playData.tp]++;
    playAddScore(playData.p2r[stampID], "收集印章");
    stampRecordDisp();
    actionsDisp();
    playAddDisp("已收集印章。");
}
function useRefresh() {
    var stampID = getStampSelected();
    playSubtractItems(CT[3]);
    playData.p2r[stampID] = Math.floor(Math.random() * (playData.p2ru-playData.p2rb+1)) + playData.p2rb;
    p2IntervalDisp();
    actionsDisp();
    playAddDisp("已刷新所选印章的活动点数。");
}
function exchangeItem() {
    playExchangeShow(CT[2], CT[1]);
}
function receiveHistoryPopularityReward() {
    playAddItems(CJ[4][playData.p2hpr][1], "人气印章收藏奖励");
    playData.p2hpr++;
    playAddDisp("已领取奖励。");
    p2IntervalDisp();
}

function qStamp(stampID) {
    return $('<img src="'+CS[1]+CJ[1][stampID-1]+'.png">');
}

var pConfig = {
    m:{
        s:{Ctpl:1,Ctsl:4,CTi:5,Pfl:3,P2c:5,P2rb:6,P2ru:7,P2rp:8,P2rl:9,P2db:10,P2du:11,P2Ji:2,P2Jr:3},
        f:{Ctpl:2,Ctsl:4,CTi:6,Pfl:3,P2c:5,P2rb:6,P2ru:7,P2rp:8,P2rl:9},
        c:{Ctsl:4},
    },
    s:{
        "Ctpl":{b:2,n:"天数"},
        "Pfl":{n:"每日金币数"},
        "P2c":{b:1,ui:5,n:"印章种类数"},
        "P2rb":{n:"印章最小活动点数"},"P2ru":{n:"印章最大活动点数"},"P2rp":{n:"人气印章奖励活动点数"},"P2rl":{n:"幸运印章奖励活动点数"},
    },
    c:[
        {g:"常用设置",l:[{k:"Ctpl",vi:1},{k:"Pfl",vi:3}]},
        {g:"初始道具",ci:5},
        {g:"高级设置",l:[{k:"P2c",vu:true},{k:"P2rb",vi:6},{k:"P2ru",vi:7},{k:"P2rp",vi:8},{k:"P2rl",vi:9}]},
    ],
    fStart:p2Start, fStartDisp:p2StartDisp,
    fIntervalNext:p2IntervalNext, fIntervalDisp:p2IntervalDisp,
    fExchangeFinish:p2ExchangeFinish,
};
