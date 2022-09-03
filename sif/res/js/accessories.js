commons.c.g[2901] = {
    defaultViewType:2,
    getItems:function(){return accessories;},
    filterIDs:[1,2,3], filterDefaults:{1:1,2:0,3:0},
    checkFilter:function(itemID,item,filterID,filterValue){
        switch (filterID) {
            case 1: return item[5]>0==filterValue>0;
            case 2:
                if (!filterValue) return true;
                var card = cards[item[5]];
                return card && card[0]==filterValue;
            case 3:
                if (!filterValue) return true;
                return item[6]==filterValue;
        }
    },
    optionIDs:[1], optionDefaults:{1:1},
    sortMethods:{
        1:{v:function(itemID,item){return itemID;}},
        2:{n:"Smile",a:"低",z:"高",v:function(itemID,item){var levelID=commons.r.g[2901].options[1];return accessoryExtends[itemID][0][levelID][0];}},
        3:{n:"Pure",a:"低",z:"高",v:function(itemID,item){var levelID=commons.r.g[2901].options[1];return accessoryExtends[itemID][0][levelID][1];}},
        4:{n:"Cool",a:"低",z:"高",v:function(itemID,item){var levelID=commons.r.g[2901].options[1];return accessoryExtends[itemID][0][levelID][2];}},
        5:{n:"初登场",a:"较早",z:"较新",v:function(itemID,item){return item[7];},i:true},
    }, sortDefault:[5,-1], sortIDs:[5,2,3,4,1],
    itemClick:function(itemID,item){return "showDetail("+itemID+")"},
    groupClick:function(groupId,itemId){return "showDetail("+itemId+","+groupId+")"},
    itemSearchWords:function(itemID,item){var a=[];
        a.push(item[0],item[1],item[2]);
    return a;},
    item2Group:{}, group2Items:{}, viewDisableGrouping:[1,3,4], viewInherit:{4:2},
    createViewItem:function(itemID,item,viewType){
        var extend = accessoryExtends[itemID];
        var levelID = commons.r.g[2901].options[1], level = extend[0][levelID];
        var isSpecial = item[5]>0; if (isSpecial) {
            var card = cards[item[5]];
            var $card = card ? gItem(1001,item[5],1,0,{v:78,z:true},gConfig) : "unit/0".toJQImg(1,1,true).addClass("card-unavailable");
            var $cardSkill = card ? $("<p>").append("edit/7801".toJQImg(1,1),G1C.skillSN[card[3]][card[2]].replace("#",card[4])) : null;
            var $cardSeal = card && card[5] ? gItem(3006,card[5]==2?6:4,1,0,{},gConfig).addClass("card-seal") : null;
        }
        var $item = gItem(1002,itemID,1,0,{i:1,v:78,z:true},gConfig).addClass("accessory-image");
        var $attr = qAttr({1:level[0],2:level[1],3:level[2]});
        var $timeLimited = item[8] ? $('<div class="accessory-time">').append(
            $('<span class="eis-sif-flag static">').text("限时制作"),
            item[8].toServerDate(1,1).getUTCDateMedium() + " 截止",
        ) : null;
        switch (viewType) {
            case 1: return $item;
            case 2: return $('<div class="accessory">').append($item,$attr.addClass("tiny"),$('<div class="accessory-view-2-detail">').append(
                $card&&$card.append($cardSeal),
                $('<div class="accessory-view-2-detail-effect">').append(
                    isSpecial?$cardSkill:null,
                    $("<p>").append("edit/7802".toJQImg(1,1),G1C.skillEffectSN[item[6]]),
                ),
            ),$timeLimited);
            case 3: return $('<div class="accessory">').append($item,
                isSpecial ? $('<div class="accessory-view-3-card">').append($card,$cardSkill,$cardSeal) : null,
                $('<p class="accessory-view-3-name">').text(item[0]),
                $attr,
                $('<p class="accessory-view-3-effect">').append($('<span class="accessory-level-caption">').text("Lv."+levelID),getSkillDesc(itemID,levelID)),
                $timeLimited,
            );
        }
    },
    createViewGroup:function(groupId,itemId,viewType){
        switch (viewType) {
            case 2: return $('<div class="accessory-group">').append($('<div>').append(
                ("accessory/design1/"+designs[groupId][0]).toJQImg(4,1,true).addClass("accessory-group-image"),
                $('<div class="accessory-view-2-group-effect">').append("edit/7802".toJQImg(1,1),G1C.skillEffectSN[accessories[itemId][6]]),
            ));
        }
    },
};
commons.p = {
    a:{
        member:{g:function(i){return members[i];},nzhs:2},
    },
};

var accessoryStorage={};
function loadAccessory(accessoryID, callback) {
    $.getJSON("/sif/interface/accessory.php", {a:accessoryID}, function(data) {
        accessoryStorage[accessoryID] = data;
        callback();
    });
}

function showDetail(accessoryID, designId) {
    if (!accessoryStorage[accessoryID]) {
        loadAccessory(accessoryID, function(){showDetail(accessoryID,designId);});
        return;
    }
    var accessory = accessories[accessoryID], accessoryLevels = accessoryStorage[accessoryID];
    var isSpecial = accessory[5]>0; if (isSpecial) {
        var cardID = accessory[5], card = cards[cardID];
    }
    $("#dialog-accessory-switch").empty();
    if (designId) {
        $.each(designs[designId][1], function(index, designAccessoryId) {
            var designCardId = accessories[designAccessoryId][5], designCard = cards[designCardId];
            var $switch = $('<div class="accessory-switch">').append(
                gItem(1002,designAccessoryId,1,0,{i:1,v:78},gConfig),
                designCard ? gItem(1001,designCardId,1,0,{v:78},gConfig) : "unit/0".toJQImg(1,1),
            );
            if (designAccessoryId==accessoryID) {
                $switch.addClass("current");
            } else {
                $switch.attr("onclick", "switchDetail("+designAccessoryId+","+designId+")");
            }
            $switch.appendTo("#dialog-accessory-switch");
        });
    }
    $("#dialog-accessory-title").empty().append(
        gItem(1002,accessoryID,1,0,{i:1,v:78},gConfig).addClass("accessory-image"),
        $('<h3 id="dialog-accessory-title-name">').text(accessory[0]),
        $('<p id="dialog-accessory-title-name-translations">').text(accessory[1]+"　"+accessory[2]),
        isSpecial ? card ? $('<div id="dialog-accessory-card">').append(
            gItem(1001,cardID,1,0,{v:78},gConfig),
            card[1] ? null : gItem(1001,cardID,1,0,{i:true,v:78},gConfig),
            G1C.skillSN[card[3]][card[2]].replace("#",card[4]),
        ) : $('<div id="dialog-accessory-card">').append(
            "unit/0".toJQImg(1,1).addClass("dialog-accessory-card-unavailable"),
        ) : null,
    ),
    $("#accessory-levels").empty();
    $.each(accessoryLevels, function(levelID, level) {
        $('<div class="accessory-level">').append(
            $('<span class="accessory-level-caption">').text("Lv."+levelID),
            levelID<=8 ? qAttr({1:level[0],2:level[1],3:level[2]}) : null,
            getSkillDesc(accessoryID, levelID),
        ).appendTo("#accessory-levels");
    });

    showDialogMessage("#dialog-accessory", $.noop);
    var _paq = window._paq || [];
    _paq.push(["setCustomVariable", 1, "Accessory ID", accessoryID, "page"]);
    _paq.push(["trackEvent", "G1-Accessories", "Show"]);
}
function switchDetail(accessoryId, designId) {
    $("#dialog-accessory").dialog("close");
    showDetail(accessoryId, designId);
}
function getSkillDesc(accessoryID, levelID) {
    var accessory = accessories[accessoryID], extend = accessoryExtends[accessoryID], level = extend[0][levelID] || accessoryStorage[accessoryID][levelID];
    var args = {a:true,l:levelID,tv:level[7],et:accessory[6],r:level[5],eD:level[4],ev:level[3]};
    if (level[6]) args.evm = 10;
    if (extend[1]) {
        var append = extend[1];
        if (append.e) args.eT = append.e;
        if (append.t) args.tt = append.t;
        if (append.te) args.te = append.te;
    }
    return eisG1.descSkill(args);
}
function qAttr(values) {
    var $div = $('<div class="sif-attr-score-group">');
    $.each(values, function(attributeID, value) {
        $('<span class="sif-attr-score" data-attribute='+attributeID+'>').text(value).appendTo($div);
    });
    return $div;
}

$(document).ready(function() {
    $.each(designs, function(designId, design) {
        commons.c.g[2901].group2Items[designId] = design[1];
        $.each(design[1], function(accessoryIndex, accessoryId) {
            commons.c.g[2901].item2Group[accessoryId] = +designId;
        });
    });
    $.each(members, function(memberId, member) {
        $("<option>").attr("value",memberId).text(member[2]).appendTo("#filter-members");
    });
    var hideEffectIds = [];
    if (SD.g1_accessory_effect_hide) {
        hideEffectIds = SD.g1_accessory_effect_hide.split(",");
    }
    $.each(G1C.skillEffectSN, function(effectId, effect) {
        if (hideEffectIds.indexOf(""+effectId)>=0) return;
        $("<option>").attr("value",effectId).text(effect).appendTo("#filter-effects");
    });
    $("#filter-members").change(function() {
        changeGalleryFilter("#g-main",2,+$(this).val());
    });
    $("#filter-effects").change(function() {
        changeGalleryFilter("#g-main",3,+$(this).val());
    });
    initGallery("#g-main");
    recoverGallery("#g-main");
});

var gConfig = $.extend({}, gConfigDefault, {
    itemImages:[null,0,0,0],
    accessoryImage:3,
    fUnit:function(cardID){return null;},
});
