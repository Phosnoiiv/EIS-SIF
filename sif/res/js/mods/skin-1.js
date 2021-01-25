$(document).ready(function() {
    $("body[data-id='home'] #top h1").replaceWith("<h1><span class='skin-1-home-title-part1'>EIS-</span><span class='skin-1-home-title-part2'>SIF</span></h1>");
    $("body[data-id='home'] #top h2").replaceWith("<h2><span class='skin-1-home-title-part1'>一些资料和</span><span class='skin-1-home-title-part2'>实验性页面</span></h2>")
    $("body[data-id='home'] #eis-sif-container>.eis-sif-section").remove();
});
