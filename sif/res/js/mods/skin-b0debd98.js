$(document).ready(function() {
    function createTitle(text) {
        let letters = [], isOdd = false
        for (const letter of text) {
            letters.push($("<span class='skin-7-title-color-" + ((isOdd = !isOdd) ? 1 : 2) + "'>").text(letter))
        }
        return letters
    }
    $("<h1>").append(createTitle("EIS-SIF")).replaceAll("body[data-id='home'] #top h1")
    $("body[data-id='home'] #top h2").text("3.5th Anniversary!")
});
