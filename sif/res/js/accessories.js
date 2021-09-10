const COL_ACCESSORY_FROM_LT = 6, COL_ACCESSORY_LEVELS = 7, COL_ACCESSORY_APPEND = 8;
commons.c.g[2901] = {
    defaultViewType:1,
    getItems:function(){return accessories;},
    filterIDs:[1], filterDefaults:{1:1},
    checkFilter:function(itemID,item,filterID,filterValue){
        switch (filterID) {
            case 1: return item[5]>0==filterValue>0;
        }
    },
    optionIDs:[1], optionDefaults:{1:1},
    sortMethods:{
        1:{v:function(itemID,item){return itemID;}},
        2:{n:"Smile",a:"低",z:"高",v:function(itemID,item){var levelID=commons.r.g[2901].options[1];return item[COL_ACCESSORY_LEVELS][levelID][0];}},
        3:{n:"Pure",a:"低",z:"高",v:function(itemID,item){var levelID=commons.r.g[2901].options[1];return item[COL_ACCESSORY_LEVELS][levelID][1];}},
        4:{n:"Cool",a:"低",z:"高",v:function(itemID,item){var levelID=commons.r.g[2901].options[1];return item[COL_ACCESSORY_LEVELS][levelID][2];}},
    }, sortDefault:[1,1],
    itemClick:function(itemID,item){return "showDetail("+itemID+")"},
    itemSearchWords:function(itemID,item){var a=[];
        a.push(item[0],item[1],item[2]);
    return a;},
    createViewItem:function(itemID,item,viewType){
        var levelID = commons.r.g[2901].options[1], level = item[COL_ACCESSORY_LEVELS][levelID];
        var isSpecial = item[5]>0; if (isSpecial) {
            var card = cards[item[5]];
            var $card = gItem(1001,item[5],1,0,{v:78,z:true},gConfig);
            var $cardSkill = $("<p>").append("edit/7801".toJQImg(1,1),G1C.skillSN[card[3]][card[2]].replace("#",card[4]));
        }
        var $item = gItem(1002,itemID,1,0,{i:1,v:78,z:true},gConfig).addClass("accessory-image");
        var $attr = qAttr({1:level[0],2:level[1],3:level[2]});
        switch (viewType) {
            case 1: return $item;
            case 2: return $('<div class="accessory">').append($item,$attr.addClass("tiny"),$('<div class="accessory-view-2-detail">').append(
                $card,
                $('<div class="accessory-view-2-detail-effect">').append(
                    isSpecial?$cardSkill:null,
                    $("<p>").append("edit/7802".toJQImg(1,1),G1C.skillEffectSN[item[COL_ACCESSORY_FROM_LT]]),
                ),
            ));
            case 3: return $('<div class="accessory">').append($item,
                isSpecial ? $('<div class="accessory-view-3-card">').append($card,$cardSkill) : null,
                $('<p class="accessory-view-3-name">').text(item[0]),
                $attr,
                $('<p class="accessory-view-3-effect">').append($('<span class="accessory-level-caption">').text("Lv."+levelID),getSkillDesc(itemID,levelID)),
            );
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

function showDetail(accessoryID) {
    if (!accessoryStorage[accessoryID]) {
        loadAccessory(accessoryID, function(){showDetail(accessoryID);});
        return;
    }
    var accessory = accessories[accessoryID], accessoryLevels = accessoryStorage[accessoryID];
    var isSpecial = accessory[5]>0; if (isSpecial) {
        var cardID = accessory[5], card = cards[cardID];
    }
    $("#dialog-accessory-title").empty().append(
        gItem(1002,accessoryID,1,0,{i:1,v:78},gConfig).addClass("accessory-image"),
        $('<h3 id="dialog-accessory-title-name">').text(accessory[0]),
        $('<p id="dialog-accessory-title-name-translations">').text(accessory[1]+"　"+accessory[2]),
        isSpecial ? $('<div id="dialog-accessory-card">').append(
            gItem(1001,cardID,1,0,{v:78},gConfig),
            card[1] ? null : gItem(1001,cardID,1,0,{i:true,v:78},gConfig),
            G1C.skillSN[card[3]][card[2]].replace("#",card[4]),
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
function getSkillDesc(accessoryID, levelID) {
    var accessory = accessories[accessoryID], level = accessory[COL_ACCESSORY_LEVELS][levelID] || accessoryStorage[accessoryID][levelID];
    var arg = {};
    if (accessory[COL_ACCESSORY_APPEND]) {
        var append = accessory[COL_ACCESSORY_APPEND];
        arg.e = append.e;
    }
    return g1SkillDesc(-1,accessory[COL_ACCESSORY_FROM_LT],0,level[5],level[4],level[3],arg);
}
function qAttr(values) {
    var $div = $('<div class="sif-attr-score-group">');
    $.each(values, function(attributeID, value) {
        $('<span class="sif-attr-score" data-attribute='+attributeID+'>').text(value).appendTo($div);
    });
    return $div;
}

$(document).ready(function() {
    initGallery("#g-main");
    recoverGallery("#g-main");
});

var gConfig = $.extend({}, gConfigDefault, {
    accessoryImage:3,
    fUnit:function(cardID){return null;},
});
