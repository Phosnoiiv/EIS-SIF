$(document).ready(function() {
    const lyrics = [
        'Keep on dreaming!',
        '夢のカタチには 決まりなんてないんだと',
        '自由に走りだせば 道ができちゃうよ',
        '誰も通ったことない だからこそ Try!',
        'ココロが想う 本当の願い大切にね',
        'どこまでだって走れるって',
        'どこまでだって 虹の色キラキラとまらない',
        'また立ちあがったら 次なる始まりさ',
        '近道ないけれど 寄り道楽しく',
        'それぞれの夢を叶えるんだ',
        'したいコトしよう 素敵が欲しいんだよ',
        'Go!! どこまでだって！',
    ]
    $('body[data-id="home"] #top h2').text(lyrics[Math.floor(Math.random() * lyrics.length)])
})
