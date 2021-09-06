var serverTimezone = {
    "1": 32400,
    "2": 0,
    "3": 28800,
};
var serverName = {
    "1": "日语版",
    "2": "国际版",
    "3": "简体字版",
};
var serverNameAShort = [null, "JP", "WW", "CN"];

function serverDate(timestamp, server) {
    return new Date((timestamp + serverTimezone[server]) * 1000);
}
function echo2Digits(num) {
    return num < 10 ? "0" + num : num;
}
Number.prototype.toPercent = function(precision) {
    return (this * 100).toFixed(precision) + "%";
}
Number.prototype.toPeriod = function(short) {
    if (this > 86400) {
        return Math.floor(this / 86400) + " 天" + (short ? "" : " " + Math.floor(this % 86400 / 3600) + " 小时");
    } else if (this > 3600) {
        return Math.floor(this / 3600) + " 小时" + (short ? "" : " " + Math.floor(this % 3600 / 60) + " 分钟");
    } else if (this > 0) {
        return Math.floor(this / 60) + " 分钟";
    } else {
        return "0 分钟";
    }
}
Date.prototype.getUTCDateShort = function() {
    return echo2Digits(this.getUTCMonth() + 1) + "/" + echo2Digits(this.getUTCDate());
}
Date.prototype.getUTCDateMedium = function() {
    return (this.getUTCFullYear() == new Date().getUTCFullYear() ? "" : this.getUTCFullYear() + "/")
        + echo2Digits(this.getUTCMonth() + 1) + "/" + echo2Digits(this.getUTCDate());
}
Date.prototype.getUTCDateFull = function() {
    return this.getUTCFullYear() + "/" + this.getUTCDateShort();
}
Date.prototype.getUTCDateTime = function() {
    return echo2Digits(this.getUTCMonth() + 1) + "/" + echo2Digits(this.getUTCDate()) + " "
        + echo2Digits(this.getUTCHours()) + ":" + echo2Digits(this.getUTCMinutes());
}
Date.prototype.getUTCDateTimeFull = function() {
    return this.getUTCFullYear() + "/" + this.getUTCDateTime();
}

function img(file, desc) {
    return $("<img>").attr("src", "/vio/sif/" + file + ".png").attr("title", desc).attr("alt", desc);
}

function calendar(ref, year, month, arg) {
    $(ref).empty();
    var dateBegin = new Date(year, month - 1);
    var dayCount = new Date(year, month, 0).getDate();
    var tdTemplate = $("<td>").addClass(arg.classDefault);
    $.each(arg.components, function(_, component) {
        $("<div>").addClass(component).appendTo(tdTemplate);
    });
    for (var d = 0; d < dayCount;) {
        var tr = $("<tr>"), i = 0;
        for (; d == 0 && i < dateBegin.getDay(); i++) {
            $("<td>").addClass(arg.classBlank).appendTo(tr);
        }
        for (; d < dayCount && i < 7; i++) {
            var td = tdTemplate.clone().attr("id", arg.dateIdPrefix + (++d));
            td.addClass(birthdays[month][d] && birthdays[month][d][1]<=year ? "eis-sif-bg-1 member-" + birthdays[month][d][0] : "");
            td.children(arg.dateClass).text(d);
            td.appendTo(tr);
        }
        for (; d == dayCount && i < 7; i++) {
            $("<td>").addClass(arg.classBlank).appendTo(tr);
        }
        tr.appendTo(ref);
    }
}

var countdownEnabled = false;
function enableCountdown() {
    if (countdownEnabled)
        return;
    setInterval(function() {
        $(".eis-sif-countdown").each(function() {
            var d = $(this).attr("data-time") - Date.now() / 1000;
            var short = $(this).attr("data-countdown-short");
            var classTarget = $(this).attr("data-countdown-parent") ? $(this).parent() : $(this);
            if (d > 0) {
                $(this).text(d.toPeriod(short));
                if (d < 86400) {
                    classTarget.addClass("nearend");
                }
            } else {
                $(this).text($(this).attr("data-text-ended") || "0 分钟");
                classTarget.removeClass("nearend").addClass("ended");
            }
        })
        $(".eis-sif-countup").each(function() {
            var timeDiff = Date.now() / 1000 - $(this).attr("data-time");
            $(this).text(timeDiff.toPeriod(true));
        });
    }, 1000);
    countdownEnabled = true;
}

function trackLang(lang) {
    $(".eis-sif-track").text(function() {
        var track = tracks[$(this).attr("data-track")];
        $(this).text(track[lang] || track[1]);
    });
}

$(window).resize(function() {
    $(".eis-jq-dialog").each(function() {
        $(this).dialog("option", "maxHeight", $(window).height() - 50);
    });
});

$(document).ready(function() {
    $(".eis-jq-dialog").each(function() {
        $(this).dialog({autoOpen:false, modal:true, closeText:"关闭"});
    });
    $(".eis-sif-track").each(function() {
        var track = tracks[$(this).attr("data-track")];
        $(this).addClass("attribute-" + track[0]).text(track[1]);
    });
    if ($(".eis-sif-countdown").size()) {
        enableCountdown();
    }
    $(window).resize();
});
