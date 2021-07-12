var vd;

function refreshMakeSelect(type,num) {
    var sequentialOptions = function(min,max) {
        var options = [];
        for (var i=min; i<=max; i++) {
            options.push({v:i,t:i});
        }
        return options;
    };
    var options;
    switch (type) {
        case 1:
            options = [{v:1,t:"N"},{v:2,t:"R"},{v:3,t:"SR"},{v:5,t:"SSR"},{v:4,t:"UR"}];
            break;
        case 2:
            var rarity = $(".make-cell[data-type=1][data-num="+num+"] select").val();
            var max = game1Rarities[rarity].l;
            if (rarity==4) max = vd.u;
            options = sequentialOptions(1,max);
            break;
        case 3:
            var rarity = $(".make-cell[data-type=1][data-num="+num+"] select").val();
            if (rarity==1) options = [{v:0,t:"-"}];
            else options = sequentialOptions(1,8);
            break;
    }
    $(".make-cell[data-type="+type+"]" + (num?"[data-num="+num+"]":"")).empty().append(function() {
        return qSelect(options).attr("onchange","changedMakeSelect("+type+","+$(this).attr("data-num")+")");
    });
}
function changedMakeSelect(type,num) {
    if (type==1) {
        refreshMakeSelect(2,num);
        refreshMakeSelect(3,num);
    }
    calcMake();
}
function calcMake() {
    var sum = 0;
    for (var num=1; num<=2; num++) {
        for (var type=1; type<=3; type++) {
            var val = $(".make-cell[data-type="+type+"][data-num="+num+"] select").val();
            $.each(vd.c[type], function(costIndex, cost) {
                if (cost[0]<=val && val<=cost[1]) {
                    sum += cost[2];
                    return false;
                }
            });
        }
    }
    $.each(vd.g, function(groupID, group) {
        if (!group) return;
        if (group[0]<=sum && sum<=group[1]) {
            var list = vd.l[groupID], sumW = 0;
            $.each(list, function(rarity, weight) {
                sumW += weight;
            });
            $(".make-result").text(function() {
                var rarity = $(this).attr("data-rarity");
                if (list[rarity]) return (list[rarity]/sumW).toPercent(1);
                return "-";
            })
            return false;
        }
    });
}

$(document).ready(function() {
    vd = data[0];
    refreshMakeSelect(1);
    $(".make-cell[data-type=1] select").change();
});
