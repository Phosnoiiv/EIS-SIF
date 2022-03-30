// © 2015 Rembound.com

var Match3 = function(elementId, options) {
    var canvas = document.getElementById(elementId);
    var context = canvas.getContext("2d");
    var lastframe = 0;
    var fpstime = 0;
    var framecount = 0;
    var fps = 0;
    var drag = false;
    var level = {
        x:250, y:113, columns:8, rows:8, tilewidth:40, tileheight:40,
        tiles:[], selectedtile:{selected:false, column:0, row:0},
    };
    var clusters = [];
    var moves = [];
    var currentmove = {column1:0, row1:0, column2:0, row2:0, chain:0};
    var gamestates = {init:0, ready:1, resolve:2, loading:3};
    var gamestate = gamestates.init;
    var score = 0;
    var animationstate = 0;
    var animationtime = 0;
    var animationtimetotal = 0.3;
    var showmoves = false;
    var aibot = false;
    var gameover = false;
    var buttons = [
        {x:30, y:360, width:150, height:50, text:"退出", f:playQuitConfirm},
        {x:0, y:-99, width:0, height:0, text:"Show Moves", f:function() {
            showmoves = !showmoves;
            buttons[1].text = (showmoves?"Hide":"Show")+" Moves";
        }},
        {x:0, y:-99, width:0, height:0, text:"Enable AI Bot", f:function() {
            aibot = !aibot;
            buttons[2].text = (aibot?"Disable":"Enable")+" AI Bot";
        }},
    ];
    var stage = 0;
    var stageGoals = [];
    var stageGoalTypes = [
        ["无尽挑战"],
        ["获得 # 分", "达到 # 分"],
        ["消除^方块 # 个", "消除^方块 # 个"],
        ["消除^方块 # 个", "消除^方块 # 个"],
        ["获得 # ^", "获得 # ^"],
    ];
    var images = {}, loadingImageIds = [], isLoadingImages = false;
    var fontFamily = '"Microsoft Yahei", sans-serif';
    function init() {
        canvas.addEventListener("mousemove", onMouseMove);
        canvas.addEventListener("mousedown", onMouseDown);
        canvas.addEventListener("mouseup", onMouseUp);
        canvas.addEventListener("mouseout", onMouseOut);
        for (var i=0; i<level.columns; i++) {
            level.tiles[i] = [];
            for (var j=0; j<level.rows; j++) {
                level.tiles[i][j] = {type:0, shift:0};
            }
        }
        newGame();
        main(0);
    }
    function main(tframe) {
        if (stage==-2) return;
        window.requestAnimationFrame(main);
        load();
        update(tframe);
        render();
    }
    function load() {
        if (gamestate!=gamestates.loading || isLoadingImages) return;
        isLoadingImages = true;
        var imageId = loadingImageIds.shift();
        images[imageId] = new Image();
        images[imageId].addEventListener("load", function() {
            isLoadingImages = false;
            if (!loadingImageIds.length) {
                gamestate = gamestates.ready;
            }
        });
        images[imageId].src = options.tileImages[imageId][2].toImgPath(options.tileImages[imageId][0], options.tileImages[imageId][1]);
    }
    function update(tframe) {
        var dt = (tframe-lastframe)/1000;
        lastframe = tframe;
        updateFps(dt);
        if (gamestate==gamestates.ready) {
            if (moves.length<=0) {
                gameover = true;
            }
            if (aibot) {
                animationtime += dt;
                if (animationtime>animationtimetotal) {
                    findMoves();
                    if (moves.length>0) {
                        var move = moves[Math.floor(Math.random()*moves.length)];
                        mouseSwap(move.column1, move.row1, move.column2, move.row2);
                    } else {
                    }
                    animationtime = 0;
                }
            }
        } else if (gamestate==gamestates.resolve) {
            animationtime += dt;
            if (animationstate==0) {
                if (animationtime>animationtimetotal) {
                    findClusters();
                    if (clusters.length>0) {
                        currentmove.chain++;
                        for (var i=0; i<clusters.length; i++) {
                            if (currentmove.chain==1) {
                                score += options.stageScores[stage][0]*(clusters[i].length-2);
                            } else switch (options.stageScores[stage][1]) {
                                case 1:
                                    score += options.stageScores[stage][2];
                                    break;
                            }
                        }
                        removeClusters();
                        animationstate = 1;
                    } else {
                        gamestate = gamestates.ready;
                    }
                    animationtime = 0;
                }
            } else if (animationstate==1) {
                if (animationtime>animationtimetotal) {
                    shiftTiles();
                    animationstate = 0;
                    animationtime = 0;
                    findClusters();
                    if (clusters.length<=0) {
                        gamestate = gamestates.ready;
                    }
                }
            } else if (animationstate==2) {
                if (animationtime>animationtimetotal) {
                    swap(currentmove.column1, currentmove.row1, currentmove.column2, currentmove.row2);
                    findClusters();
                    if (clusters.length>0) {
                        animationstate = 0;
                        animationtime = 0;
                        gamestate = gamestates.resolve;
                    } else {
                        animationstate = 3;
                        animationtime = 0;
                    }
                    findMoves();
                    findClusters();
                }
            } else if (animationstate==3) {
                if (animationtime>animationtimetotal) {
                    swap(currentmove.column1, currentmove.row1, currentmove.column2, currentmove.row2);
                    gamestate = gamestates.ready;
                }
            }
            findMoves();
            findClusters();
        }
        checkStage();
    }
    function updateFps(dt) {
        if (fpstime>0.25) {
            fps = Math.round(framecount/fpstime);
            fpstime = 0;
            framecount = 0;
        }
        fpstime += dt;
        framecount++;
    }
    function drawCenterText(text, x, y, width) {
        var textdim = context.measureText(text);
        context.fillText(text, x+(width-textdim.width)/2, y);
    }
    function render() {
        drawFrame();
        drawMattribuxStage();
        drawButtons();
        var levelwidth = level.columns*level.tilewidth;
        var levelheight = level.rows*level.tileheight;
        context.fillStyle = "#000000";
        context.fillRect(level.x-4, level.y-4, levelwidth+8, levelheight+8);
        renderTiles();
        renderClusters();
        if (showmoves && clusters.length<=0 && gamestate==gamestates.ready) {
            renderMoves();
        }
        if (gameover) {
            context.fillStyle = "rgba(0, 0, 0, 0.8)";
            context.fillRect(level.x, level.y, levelwidth, levelheight);
            context.fillStyle = "#ffffff";
            context.font = "24px Verdana";
            drawCenterText("Game Over!", level.x, level.y+levelheight/2+10, levelwidth);
        }
        if (gamestate==gamestates.loading) {
            context.fillStyle = "rgba(0,0,0,0.8)";
            context.fillRect(level.x, level.y, levelwidth, levelheight);
            context.fillStyle = "#fff";
            context.font = "24px "+fontFamily;
            drawCenterText("正在加载图片资源…", level.x, level.y+levelheight/2+10, levelwidth);
        }
    }
    function drawFrame() {
        context.fillStyle = "#d0d0d0";
        context.fillRect(0, 0, canvas.width, canvas.height);
        context.fillStyle = "#e8eaec";
        context.fillRect(1, 1, canvas.width-2, canvas.height-2);
        context.fillStyle = "#303030";
        context.fillRect(0, 0, canvas.width, 65);
        context.fillStyle = "#ffffff";
        context.font = "24px Verdana";
        context.fillText(options.title, 10, 30);
        context.fillStyle = "#ffffff";
        context.font = "12px Verdana";
        context.fillText("Fps: "+fps, 13, 50);
    }
    function drawMattribuxStage() {
        context.fillStyle = "#000";
        context.font = "20px "+fontFamily;
        drawCenterText(options.stageNames[stage], 30, level.y+20, 150);
        context.font = "16px "+fontFamily;
        drawCenterText("得分", 30, level.y+50, 150);
        context.font = "24px "+fontFamily;
        drawCenterText(score, 30, level.y+80, 150);
        context.font = "16px "+fontFamily;
        context.fillText(options.stageGoals[stage][0][0]==0?"无尽挑战":"目标", 30, level.y+120);
        for (var i=0; i<stageGoals.length; i++) {
            var desc = stageGoalTypes[stageGoals[i].t][stageGoals[i].p?1:0].replace("^", stageGoals[i].n);
            if (stageGoals[i].p) {
                context.fillStyle = "#388e3c";
                context.fillText("> 已"+desc.replace("#", stageGoals[i].g), 30, level.y+140+20*i);
            } else {
                context.fillStyle = "#e64a19";
                context.fillText("> "+desc.replace("#", stageGoals[i].g-stageGoals[i].c), 30, level.y+140+20*i);
            }
        }
    }
    function drawButtons() {
        for (var i=0; i<buttons.length; i++) {
            context.fillStyle = "#000000";
            context.fillRect(buttons[i].x, buttons[i].y, buttons[i].width, buttons[i].height);
            context.fillStyle = "#ffffff";
            context.font = "18px Verdana";
            var textdim = context.measureText(buttons[i].text);
            context.fillText(buttons[i].text, buttons[i].x+(buttons[i].width-textdim.width)/2, buttons[i].y+30);
        }
    }
    function renderTiles() {
        for (var i=0; i<level.columns; i++) {
            for (var j=0; j<level.rows; j++) {
                var shift = level.tiles[i][j].shift;
                var coord = getTileCoordinate(i, j, 0, (animationtime/animationtimetotal)*shift);
                if (level.tiles[i][j].type>=0) {
                    drawMattribuxTile(coord.tilex, coord.tiley, level.tiles[i][j].design);
                }
                if (level.selectedtile.selected) {
                    if (level.selectedtile.column==i && level.selectedtile.row==j) {
                        drawTile(coord.tilex, coord.tiley, 255, 0, 0);
                    }
                }
            }
        }
        if (gamestate==gamestates.resolve && (animationstate==2 || animationstate==3)) {
            var shiftx = currentmove.column2-currentmove.column1;
            var shifty = currentmove.row2-currentmove.row1;
            var coord1 = getTileCoordinate(currentmove.column1, currentmove.row1, 0, 0);
            var coord1shift = getTileCoordinate(currentmove.column1, currentmove.row1, (animationtime/animationtimetotal)*shiftx, (animationtime/animationtimetotal)*shifty);
            var designId1 = level.tiles[currentmove.column1][currentmove.row1].design;
            var coord2 = getTileCoordinate(currentmove.column2, currentmove.row2, 0, 0);
            var coord2shift = getTileCoordinate(currentmove.column2, currentmove.row2, (animationtime/animationtimetotal)* -shiftx, (animationtime/animationtimetotal)* -shifty);
            var designId2 = level.tiles[currentmove.column2][currentmove.row2].design;
            drawTile(coord1.tilex, coord1.tiley, 0, 0, 0);
            drawTile(coord2.tilex, coord2.tiley, 0, 0, 0);
            if (animationstate==2) {
                drawMattribuxTile(coord1shift.tilex, coord1shift.tiley, designId1);
                drawMattribuxTile(coord2shift.tilex, coord2shift.tiley, designId2);
            } else {
                drawMattribuxTile(coord2shift.tilex, coord2shift.tiley, designId2);
                drawMattribuxTile(coord1shift.tilex, coord1shift.tiley, designId1);
            }
        }
    }
    function getTileCoordinate(column, row, columnoffset, rowoffset) {
        var tilex = level.x+(column+columnoffset)*level.tilewidth;
        var tiley = level.y+(row+rowoffset)*level.tileheight;
        return {tilex:tilex, tiley:tiley};
    }
    function drawTile(x, y, r, g, b) {
        context.fillStyle = "rgb("+r+","+g+","+b+")";
        context.fillRect(x+2, y+2, level.tilewidth-4, level.tileheight-4);
    }
    function drawMattribuxTile(x, y, designId) {
        var design = options.tileDesigns[designId];
        if (design[1]==1) {
            var color = options.tileColors[design[2]];
            drawTile(x, y, color[0], color[1], color[2]);
        } else if (design[1]==2) {
            if (options.tileImages[design[2]][3]) {
                var color = options.tileColors[options.tileImages[design[2]][3]];
                drawTile(x, y, color[0], color[1], color[2]);
            } else {
                drawTile(x, y, 255, 255, 255);
            }
            if (!images[design[2]]) return;
            context.drawImage(images[design[2]], x+2, y+2, level.tilewidth-4, level.tileheight-4);
        }
    }
    function renderClusters() {
        for (var i=0; i<clusters.length; i++) {
            var coord = getTileCoordinate(clusters[i].column, clusters[i].row, 0, 0);
            if (clusters[i].horizontal) {
                context.fillStyle = "#333";
                context.fillRect(coord.tilex+level.tilewidth/2, coord.tiley+level.tileheight/2-4, (clusters[i].length-1)*level.tilewidth, 8);
            } else {
                context.fillStyle = "#333";
                context.fillRect(coord.tilex+level.tilewidth/2-4, coord.tiley+level.tileheight/2, 8, (clusters[i].length-1)*level.tileheight);
            }
        }
    }
    function renderMoves() {
        for (var i=0; i<moves.length; i++) {
            var coord1 = getTileCoordinate(moves[i].column1, moves[i].row1, 0, 0);
            var coord2 = getTileCoordinate(moves[i].column2, moves[i].row2, 0, 0);
            context.strokeStyle = "#ff0000";
            context.beginPath();
            context.moveTo(coord1.tilex+level.tilewidth/2, coord1.tiley+level.tileheight/2);
            context.lineTo(coord2.tilex+level.tilewidth/2, coord2.tiley+level.tileheight/2);
            context.stroke();
        }
    }
    function newGame() {
        score = 0;
        stage = 0;
        gamestate = gamestates.ready;
        gameover = false;
        nextStage();
        createLevel();
        findMoves();
        findClusters();
    }
    function nextStage() {
        stage++;
        stageGoals = [];
        for (var i=0; i<options.stageGoals[stage].length; i++) {
            var goal = {
                t:options.stageGoals[stage][i][0], g:options.stageGoals[stage][i][1]||1,
                a:options.stageGoals[stage][i][2], n:options.stageGoals[stage][i][3],
                c:0, p:false,
            };
            stageGoals.push(goal);
        }
        for (var i=0; i<options.stageTiles[stage].length; i++) {
            var design = options.tileDesigns[options.stageTiles[stage][i]];
            if (design[1]!=2) continue;
            if (images[design[2]]) continue;
            loadingImageIds.push(design[2]);
        }
        if (loadingImageIds.length) {
            gamestate = gamestates.loading;
        }
    }
    function checkStage() {
        var allPassed = true;
        for (var i=0; i<stageGoals.length; i++) {
            if (stageGoals[i].p) continue;
            if (stageGoals[i].t==0) {
                allPassed = false;
                continue;
            }
            switch (stageGoals[i].t) {
                case 1: stageGoals[i].c = score; break;
            }
            if (stageGoals[i].c>=stageGoals[i].g) {
                stageGoals[i].p = true;
            } else {
                allPassed = false;
            }
        }
        if (allPassed && gamestate==gamestates.ready) {
            nextStage();
        }
    }
    function createLevel() {
        var done = false;
        while (!done) {
            for (var i=0; i<level.columns; i++) {
                for (var j=0; j<level.rows; j++) {
                    createMattribuxTile(i, j);
                }
            }
            resolveClusters();
            findMoves();
            if (moves.length>0) {
                done = true;
            }
        }
    }
    function createMattribuxTile(i, j) {
        level.tiles[i][j].design = options.stageTiles[stage][Math.floor(Math.random()*options.stageTiles[stage].length)];
        level.tiles[i][j].type = options.tileDesigns[level.tiles[i][j].design][0];
    }
    function resolveClusters() {
        findClusters();
        while (clusters.length>0) {
            removeClusters();
            shiftTiles();
            findClusters();
        }
    }
    function findClusters() {
        clusters = [];
        for (var j=0; j<level.rows; j++) {
            var matchlength = 1;
            for (var i=0; i<level.columns; i++) {
                var checkcluster = false;
                if (i==level.columns-1) {
                    checkcluster = true;
                } else {
                    if (level.tiles[i][j].type==level.tiles[i+1][j].type && level.tiles[i][j].type!=-1) {
                        matchlength += 1;
                    } else {
                        checkcluster = true;
                    }
                }
                if (checkcluster) {
                    if (matchlength>=3) {
                        clusters.push({column:i+1-matchlength, row:j, length:matchlength, horizontal:true});
                    }
                    matchlength = 1;
                }
            }
        }
        for (var i=0; i<level.columns; i++) {
            var matchlength = 1;
            for (var j=0; j<level.rows; j++) {
                var checkcluster = false;
                if (j==level.rows-1) {
                    checkcluster = true;
                } else {
                    if (level.tiles[i][j].type==level.tiles[i][j+1].type && level.tiles[i][j].type!=-1) {
                        matchlength += 1;
                    } else {
                        checkcluster = true;
                    }
                }
                if (checkcluster) {
                    if (matchlength>=3) {
                        clusters.push({column:i, row:j+1-matchlength, length:matchlength, horizontal:false});
                    }
                    matchlength = 1;
                }
            }
        }
    }
    function findMoves() {
        moves = [];
        for (var j=0; j<level.rows; j++) {
            for (var i=0; i<level.columns-1; i++) {
                swap(i, j, i+1, j);
                findClusters();
                swap(i, j, i+1, j);
                if (clusters.length>0) {
                    moves.push({column1:i, row1:j, column2:i+1, row2:j});
                }
            }
        }
        for (var i=0; i<level.columns; i++) {
            for (var j=0; j<level.rows-1; j++) {
                swap(i, j, i, j+1);
                findClusters();
                swap(i, j, i, j+1);
                if (clusters.length>0) {
                    moves.push({column1:i, row1:j, column2:i, row2:j+1});
                }
            }
        }
        clusters = [];
    }
    function loopClusters(func) {
        for (var i=0; i<clusters.length; i++) {
            var cluster = clusters[i];
            var coffset = 0;
            var roffset = 0;
            for (var j=0; j<cluster.length; j++) {
                func(i, cluster.column+coffset, cluster.row+roffset, cluster);
                if (cluster.horizontal) {
                    coffset++;
                } else {
                    roffset++;
                }
            }
        }
    }
    function removeClusters() {
        loopClusters(function(index, column, row, cluster) {
            for (var i=0; i<stageGoals.length; i++) {
                if (stageGoals[i].t==2 && level.tiles[column][row].type==stageGoals[i].a)
                    stageGoals[i].c++;
                if (stageGoals[i].t==3 && stageGoals[i].a.indexOf(level.tiles[column][row].design)>=0)
                    stageGoals[i].c++;
                if (stageGoals[i].t==4 && stageGoals[i].a[level.tiles[column][row].design])
                    stageGoals[i].c += stageGoals[i].a[level.tiles[column][row].design];
            }
            level.tiles[column][row].type = -1;
        });
        for (var i=0; i<level.columns; i++) {
            var shift = 0;
            for (var j=level.rows-1; j>=0; j--) {
                if (level.tiles[i][j].type==-1) {
                    shift++;
                    level.tiles[i][j].shift = 0;
                } else {
                    level.tiles[i][j].shift = shift;
                }
            }
        }
    }
    function shiftTiles() {
        for (var i=0; i<level.columns; i++) {
            for (var j=level.rows-1; j>=0; j--) {
                if (level.tiles[i][j].type==-1) {
                    createMattribuxTile(i, j);
                } else {
                    var shift = level.tiles[i][j].shift;
                    if (shift>0) {
                        swap(i, j, i, j+shift);
                    }
                }
                level.tiles[i][j].shift = 0;
            }
        }
    }
    function getMouseTile(pos) {
        var tx = Math.floor((pos.x-level.x)/level.tilewidth);
        var ty = Math.floor((pos.y-level.y)/level.tileheight);
        if (tx>=0 && tx<level.columns && ty>=0 && ty<level.rows) {
            return {valid:true, x:tx, y:ty};
        }
        return {valid:false, x:0, y:0};
    }
    function canSwap(x1, y1, x2, y2) {
        if ((Math.abs(x1-x2)==1 && y1==y2) || (Math.abs(y1-y2)==1 && x1==x2)) {
            return true;
        }
        return false;
    }
    function swap(x1, y1, x2, y2) {
        var typeswap = level.tiles[x1][y1].type;
        level.tiles[x1][y1].type = level.tiles[x2][y2].type;
        level.tiles[x2][y2].type = typeswap;
        typeswap = level.tiles[x1][y1].design;
        level.tiles[x1][y1].design = level.tiles[x2][y2].design;
        level.tiles[x2][y2].design = typeswap;
    }
    function mouseSwap(c1, r1, c2, r2) {
        currentmove = {column1:c1, row1:r1, column2:c2, row2:r2, chain:0};
        level.selectedtile.selected = false;
        animationstate = 2;
        animationtime = 0;
        gamestate = gamestates.resolve;
    }
    function onMouseMove(e) {
        var pos = getMousePos(canvas, e);
        if (drag && level.selectedtile.selected && gamestate==gamestates.ready) {
            mt = getMouseTile(pos);
            if (mt.valid) {
                if (canSwap(mt.x, mt.y, level.selectedtile.column, level.selectedtile.row)) {
                    mouseSwap(mt.x, mt.y, level.selectedtile.column, level.selectedtile.row);
                }
            }
        }
    }
    function onMouseDown(e) {
        var pos = getMousePos(canvas, e);
        if (!drag && gamestate==gamestates.ready) {
            mt = getMouseTile(pos);
            if (mt.valid) {
                var swapped = false;
                if (level.selectedtile.selected) {
                    if (mt.x==level.selectedtile.column && mt.y==level.selectedtile.row) {
                        level.selectedtile.selected = false;
                        drag = true;
                        return;
                    } else if (canSwap(mt.x, mt.y, level.selectedtile.column, level.selectedtile.row)) {
                        mouseSwap(mt.x, mt.y, level.selectedtile.column, level.selectedtile.row);
                        swapped = true;
                    }
                }
                if (!swapped) {
                    level.selectedtile.column = mt.x;
                    level.selectedtile.row = mt.y;
                    level.selectedtile.selected = true;
                }
            } else {
                level.selectedtile.selected = false;
            }
            drag = true;
        }
        for (var i=0; i<buttons.length; i++) {
            if (pos.x>=buttons[i].x && pos.x<buttons[i].x+buttons[i].width && pos.y>=buttons[i].y && pos.y<buttons[i].y+buttons[i].height) {
                buttons[i].f();
            }
        }
    }
    function onMouseUp(e) {
        drag = false;
    }
    function onMouseOut(e) {
        drag = false;
    }
    function getMousePos(canvas, e) {
        var rect = canvas.getBoundingClientRect();
        return {
            x:Math.round((e.clientX-rect.left)/(rect.right-rect.left)*canvas.width),
            y:Math.round((e.clientY-rect.top)/(rect.bottom-rect.top)*canvas.height),
        };
    }
    function getScore() {return score;}
    function end() {
        stage = -2;
    }
    function dev(command) {
        switch (command) {
            case "ai":
                buttons[2].f();
                break;
        }
    }
    return {
        init:init, end:end,
        getScore:getScore,
        _dev:dev,
    };
};
