serverTimezone[2] = 32400;

var rarityNamesD = [null, "R", "SR", "UR"];
var difficultyNamesCD = [null, "初级", "中级", "上级", "上级＋"];
var memberGroupNamesCT = [null, " μ's ", " Aqours ", "虹咲学园学园偶像同好会"];

var eventTypeNames = [null, "剧情", "交换所", "SBL", "DLP"];
var missionTermNames = [null, "每日", "每周"];
var songRouteStrings = [null, "# 章", "每周#", "绊 # 级", "长期限时", "限时", "仅剧情", "仅活动", "仅高难度", "常驻"];
var songDailyNames = [null, "一", "二", "三", "四", "五", "六", "日", "一四六", "二五日", "三六日"];

var songRouteOrders = [null, 1, 5, 6, 3, 7, 9, 8, 4, 2];

function qASImg(path, desc, lazy) {
    path = "/vio/" + (path.substring(0, 4) == "sif/" ? "" : "sifas/") + path;
    if (path.substr(path.length - 8, 8) == '.png.jpg') {
        path = path.substr(0, path.length - 4);
    } else if (path.substr(path.length - 4, 4) != '.jpg') {
        path = path + ".png";
    }
    return $("<img>").addClass(lazy ? "lazyload" : "").attr(lazy ? "data-src" : "src", path).attr("title", desc).attr("alt", desc);
}

var gConfigDefault = {
    fItem:function(type,key){return items[type][key];},
    fItemString:function(index){return itemStrings[index];},
    fEmblem:function(key){return emblems[key];},
};
function gItemName(type, key, server, config) {
    switch (type) {
        case 15:
            var emblem = config.fEmblem(key);
            return emblem[config.emblemNames[server]];
        default:
            var item = config.fItem(type, key);
            return item[config.itemNames[server]];
    }
}
function gItemDesc(type, key, server, config) {
    switch (type) {
        case 15:
            var emblem = config.fEmblem(key);
            return emblem[config.emblemDesc[server]];
        default:
            var item = config.fItem(type, key);
            return (config.itemDescStrings ? config.fItemString(item[config.itemDescStrings[server]]) : item[config.itemDesc[server]]).replace(/\\n/g, "<br>");
    }
}
function gItemIntro(type, key, config) {
    switch (type) {
        case 15:
            var emblem = config.fEmblem(key);
            return emblem[config.emblemIntro];
        default:
            var item = config.fItem(type, key);
            return (config.itemIntroString ? config.fItemString(item[config.itemIntroString]) : item[config.itemIntro]).replace(/\\n/g, "<br>");
    }
}
function gItemImage(type, key, server, options, config) {
    switch (type) {
        case 15:
            var emblem = config.fEmblem(key);
            return "sifas/emblem/" + emblem[config.emblemImages[server]];
        default:
            var item = config.fItem(type, key);
            return "sifas/" + (options.s ? item[config.itemImagesSmall[server]] || item[config.itemImagesSmall[1]] : item[config.itemImages[server]] || item[config.itemImages[1]]);
    }
}

function aEffect(category, lang, type, value) {
    try {
        var r = wordEffects[type][lang+4*category-5].replace("#", value).replace("%", value / 100 + "%");
    } catch (error) {
        throw "读取技能效果错误（参数：" + category + "," + lang + "," + type + "," + value + "）";
    }
    if (r.indexOf("*") >= 0) {
        r = r.replace("*", wordEffectsExtra[type][value][category-1][lang-1]);
    }
    return r;
}
function aNoteName(lang, type, value) {
    return aEffect(1, lang, type, value);
}
var aDescConditionPrefixes = ["条件：", "Condition: ", "条件：", "條件："];
var aDescTimePrefixes = ["時間：", "Time: ", "时间：", "時間："];
var aDescTargetPrefixes = ["対象：", "Affects: ", "对象: ", "適用對象："];
var aDescWavePrefixes = ["【特殊効果】", "[Special Effect] ", "【特殊效果】", "【特殊效果】"];
function aNoteDesc(lang, type, value, finishType, finishValue, target, gimmickType) {
    var r = wordFinishes[finishType][0][lang-1].replace("#", finishValue).replace("^", aEffect(2, lang, type, value));
    r += "<br>" + aDescConditionPrefixes[lang-1] + wordNotes[gimmickType][lang-1];
    if (target != 58) r += "<br>" + aDescTargetPrefixes[lang-1] + wordTargets[target][lang-1];
    return r;
}
function aWaveDesc(lang, type, value, finishType, finishValue, target, waveType) {
    if (waveType == 255)
        return aDescWavePrefixes[lang-1] + aEffect(3, lang, 1, 0);
    var r = aDescWavePrefixes[lang-1] + aEffect(3, lang, type, value);
    r += "<br>" + aDescTimePrefixes[lang-1] + wordFinishes[finishType][waveType][lang-1].replace("#", finishValue);
    if (target != 58) r += "<br>" + aDescTargetPrefixes[lang-1] + wordTargets[target][lang-1];
    return r;
}
