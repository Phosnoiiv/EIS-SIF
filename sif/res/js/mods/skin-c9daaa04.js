$(document).ready(function() {
    function deco(i, d, h) {
        var $img = qGeneralImg(i, d);
        $img.clone().addClass("skin-4-home-deco").appendTo("body[data-id='home'] #top");
        $img.clone().addClass("skin-4-header-deco").appendTo(h);
    }
    if (SD.skin_4_i) {
        deco(SD.skin_4_i, SD.skin_4_d, "#eis-sif-header");
    } else if (SD.skin_4_si) {
        deco(SD.skin_4_si, SD.skin_4_sd, "body:not(.eis-sifas) #eis-sif-header");
    } else if (SD.skin_4_ai) {
        deco(SD.skin_4_ai, SD.skin_4_ad, "body.eis-sifas #eis-sif-header");
    }
});
