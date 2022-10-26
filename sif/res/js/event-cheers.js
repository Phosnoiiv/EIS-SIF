$(document).ready(function() {
    eisIF2.setV1('SIFAlbumIcon', function(seriesId) {
        return '/vio/sif/series/v2/' + Math.ceil(seriesId / 100) + '/' + seriesId + '.png'
    })
    eisIF2.setV1('EventYellView_events', events)
    eisIF2.mountEventYellView('#v2-container')
})
