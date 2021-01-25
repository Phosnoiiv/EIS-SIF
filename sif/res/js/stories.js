function produce() {
    var count = [null, 0, 0, 0];
    $.each(stories, function(cid, chapter) {
        var h = $("<h4>").attr("id", "chapter-" + cid);
        var empty = true;
        for (var i = 0; i <= 2; i++) {
            if (chapter[i]) {
                $("<span>").text(chapter[i]).appendTo(h);
                empty = false;
            }
        }
        if (empty) {
            $("<span>").text("特别剧情").appendTo(h);
        }
        $("#stories").prepend(h);
        var storyContainer = $("<div>");
        $.each(chapter[3], function(_, story) {
            var div = $("<div>").addClass("story");
            var divName = $("<div>").addClass("name");
            for (var i = 0; i <= 2; i++) {
                if (story[i]) {
                    $("<p>").text(story[i]).appendTo(divName);
                }
            }
            var divGet = $("<div>").addClass("get");
            for (var i = 1; i <= 3; i++) {
                var text = serverName[i] + "：";
                if (story[2 + i].length) {
                    text += outputGet(i, story[2 + i][0]);
                    for (var j = 1; j < story[2 + i].length; j++) {
                        text += "<br>";
                        for (var k = serverName[i].length; k >= 0; k--) {
                            text += "　";
                        }
                        text += outputGet(i, story[2 + i][j]);
                    }
                    count[i]++;
                } else {
                    text += "暂未提供此剧情";
                }
                $("<p>").html(text).appendTo(divGet);
            }
            $("<div>").append(divName, divGet).appendTo(div);
            div.appendTo(storyContainer);
        });
        $("#chapter-" + cid).after(storyContainer);
    });
    var text = "";
    for (var i = 1; i <= 3; i++) {
        text += serverName[i] + " " + count[i] + " 话，";
    }
    $("#summary").text(text.substr(0, text.length - 1));
}
function outputGet(server, get) {
    var methods = ["阅读前话剧情", "定位", "登录", "完成课题"];
    var r = new Date((get[1] + serverTimezone[server]) * 1000).getUTCDateTimeFull();
    if (get[2]) {
        r += "～" + new Date((get[2] + serverTimezone[server]) * 1000).getUTCDateTime() + " 期间";
    } else {
        r += " 起";
    }
    r += "通过" + methods[get[0]] + "解锁";
    if (get[3]) {
        r += "（" + get[3] + "）";
    }
    return r;
}

$(document).ready(function() {
    produce();
});
