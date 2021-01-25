function list() {
    $.each(versions, function(vid, version) {
        if (!version)
            return;
        var versionMajor = version[0].match(/\d+\.\d+/)[0];
        var isMajor = version[0].replace(/（.*）/g, "") == versionMajor;
        var div = $("<div>").addClass(isMajor ? "version-major" : "version-minor").append(
            $(isMajor ? "<h3>" : "<h4>").append(
                version[0],
                $("<span>").addClass("version-date").append(
                    '<i class="fas fa-calendar-day"></i> ',
                    new Date(version[1] * 86400000).getUTCDateMedium(),
                ),
            ),
        );
        var list = $("<ul>");
        if ($.isArray(version[2])) {
        $.each(version[2], function(_, history) {
            var types = ['', '新增', '更新', '修复'];
            var li = $("<li>").text(types[history[0]] + "：" + history[2]);
            if (history[1] == 1) {
                li.addClass("minor");
            } else if (history[1] == 3) {
                li.addClass("major");
            }
            li.appendTo(list);
        });
        } else {
            $.each(version[2].split("**"), function(logIndex, log) {
                var li = $("<li>");
                $("<li>").html(log.replace(new RegExp("※ ((?!(※|</li>)).)+", "g"), '<p class="eis-sif-note">$&</p>')).appendTo(list);
            });
        }
        list.appendTo(div);
        if (isMajor) {
            $("#versions").prepend(div);
        } else if ($(".version-major").length > 1) {
            $($(".version-major")[1]).before(div);
        } else {
            $("#versions").append(div);
        }
    });
}

$(document).ready(function() {
    list();
});
