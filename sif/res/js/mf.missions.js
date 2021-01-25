function produce() {
    if (inAprilFools) {
        items[3006][2][1] = "../sifas/exchange/g0.png";
        items[8000][1][1] = "../sifas/item/l1.png";
    }
    $.each(missions, function(id, level) {
        if (!level)
            return;
        var section = $("<div>").addClass("level");
        $("<h3>").text("等级 " + id).appendTo(section);
        var panelContainer = $("<div>").addClass("panel-container");
        var container = $("<div>").addClass("mission-container");
        $("<p>").text(level[2].length == 1 ? "固定任务：" : "随机任务：").appendTo(container);
        $.each(level[2], function(_, mission) {
            var block = $("<div>").addClass("mission");
            $("<div>").addClass("rate").text(mission[0] * 100 + "%").appendTo(block);
            var desc = "", current;
            $.each(mission[3], function(__, cid) {
                var condition = conditions[cid];
                var rank = ["", "S", "A 以上", "B 以上", "C 以上"];
                switch (condition[0]) {
                    case 2:
                        current = "得分 " + rank[condition[1][0]];
                        break;
                    case 4:
                        current = "连击 " + rank[condition[1][0]];
                        break;
                    case 5:
                        var result = ["", "PERFECT", "GREAT 以上", "GOOD 以上"];
                        current = result[condition[1][0]] + " " + condition[1][2] + " 个以上";
                        break;
                    case 8:
                        current = "剩余体力 " + condition[1][0] + " 以上";
                        break;
                    case 9:
                        var difficulty = ["", "EASY 以上", "NORMAL 以上", "HARD 以上", "EXPERT"];
                        current = "选择 " + condition[1][2] + " 曲";
                        if (condition[1][2] < 3) {
                            current += "以上";
                        }
                        current += " " + difficulty[condition[1][1]];
                        break;
                    case 10:
                        current = "选择 " + condition[1][0] + " 曲";
                        if (condition[1][0] < 3) {
                            current += "以上";
                        }
                        break;
                }
                desc += current + "，"
            });
            desc += "FES 成功";
            $("<p>").text(desc).appendTo(block);
            var chance = $("<span>");
            if (mission[1] > 0) {
                chance.text("挑战次数：" + mission[1] + " 次");
            } else {
                chance.text("不限次数").addClass("unlimited");
            }
            var time = $("<span>");
            if (mission[2] > 0) {
                time.text("挑战限时：" + mission[2] / 3600 + " 小时").addClass("time");
                block.addClass("limited");
            } else {
                time.text("不限时").addClass("unlimited");
            }
            block.append(chance).append(time).appendTo(container);
        });
        $("<div>").addClass("panel-main").append(container).appendTo(panelContainer);
        var side = $("<div>").addClass("panel-side");
        side = addReward(side, "首次完成报酬", rewards[level[0]]);
        side = addReward(side, "例行报酬", rewards[level[1]]);
        panelContainer.append(side).appendTo(section);
        $("#list").append(section);
    });
}
function addReward(container, title, group) {
    $("<p>").text(title + "：").appendTo(container);
    $.each(group, function(_, reward) {
        var image, name;
        switch (reward[0]) {
            case 1:
                image = "bonus/s/mf" + reward[1] + "c.png";
                var names = {"2":"提升经验值", "8":"完美支援", "10":"提升报酬概率"};
                name = names[reward[1]];
                break;
            case 1001: // Unit
                image = "unit/" + reward[1] + ".png";
                var rarities = [null, "N", "R", "SR", "UR", "SSR"];
                name = rarities[units[reward[1]][1]] + " " + members[units[reward[1]][0]];
                break;
            case 3000: // G
                image = inAprilFools ? "../sifas/item/g.png" : "item/g.png";
                name = "金币";
                break;
            case 3001: // Loveca
                image = inAprilFools ? "../sifas/item/s.png" : "item/l.png";
                name = "Loveca";
                break;
            case 3002: // Friend pt
                image = inAprilFools ? "../sifas/item/e.png" : "item/f.png";
                name = "友情点";
                break;
            case 3006: // Seal
            case 8000: // Recovery item
                image = items[reward[0]][reward[1]][1];
                name = items[reward[0]][reward[1]][0];
                break;
        }
        var item = $("<div>").addClass("reward");
        $("<img>").attr("src", "/vio/sif/" + image).appendTo(item);
        $("<span>").addClass("name").text(name).appendTo(item);
        if (reward[0] >= 1000 && reward[2] > 1) {
            $("<span>").addClass("amount").text("×" + reward[2]).appendTo(item);
        }
        $("<span>").addClass("rate").text(Math.round(reward[3] * 1000) / 10 + "%").appendTo(item);
        item.appendTo(container);
    });
    return container;
}

$(document).ready(function() {
    produce();
})
