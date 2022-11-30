let skin8NoticeIds = []
$(document).ready(function() {
    skin8NoticeIds = (SD.skin_8_notice || '').split(',')
    if (typeof refreshNotices === 'function') refreshNotices()
})

function hookNoticeDialog(noticeId) {
    if (skin8NoticeIds.indexOf(noticeId+'') >= 0) {
        $('#eis-sif-dialog-notice').dialog('option', 'classes', { 'ui-dialog': 'skin-8-notice-dialog' })
    }
}
function hookHomeNoticeItem(noticeId, $li) {
    if (skin8NoticeIds.indexOf(noticeId) >= 0) {
        $li.addClass('skin-8-notice-item')
    }
    return $li
}
