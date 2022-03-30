var match3;
function p3Start() {
    playData.tpl = 1;
    match3 = Match3("mattribux-main", {
        title:CS[1],
        tileDesigns:CJ[1], tileColors:CJ[2], tileImages:CJ[3],
        stageNames:playReadJ("P3Jsn"), stageTiles:playReadJ("P3Jst"), stageScores:playReadJ("P3Jss"), stageGoals:playReadJ("P3Jsg"),
    });
    match3.init();
}
function p3End() {
    $("#play-dialog-end-score").text("得分："+match3.getScore());
    match3.end();
}

var pConfig = {
    m:{
        s:{Cn:2,P3Jsn:4,P3Jst:5,P3Jss:6,P3Jsg:7},
        e1:{Cn:3,P3Jsn:8,P3Jst:9,P3Jss:10,P3Jsg:11},
    },
    fStart:p3Start, fStartDisp:$.noop,
    fIntervalNext:$.noop, fIntervalDisp:$.noop,
    fEnd:p3End,
};
