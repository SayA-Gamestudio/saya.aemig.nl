<?php // Open this file by going to cmd, cd to file folder, then type "php -S localhost:8000" and open your browser to http://localhost:8000/SecretGame.php
function getCode() {
    $result = 4756;
    return $result;
}

$authorized = false;
if (isset($_GET['code'])) {
    if ($_GET['code'] == getCode()) {
        $authorized = true;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Secret Page</title>
    <style>
        body {
            background-color: #f0f0f0;
            font-family: Arial, sans-serif;
            color: #333;
            overflow: hidden;
            margin: 0;
            user-select: none;
        }
        .access-granted {
            position: relative;
        }
        .access-denied {
            position: relative;
        }
        .invisible {
            display: none;
            color: white;
        }
        .visible {
            display: block;
            color: black;
        }
        .remove {
            position: relative;
        }
        h1 {
            color: #2c3e50;
        }
        #gameArea {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            margin: 0;
            overflow: hidden;
            background-color: #d3d3d3;
        }
        #btn {
            position: absolute;
            display: none;
            width: 100px;
            height: 100px;
            left: 50%;
            top: 50%;
            transform: translateX(-50%) translateY(-50%);
            background-image: url('images/placeholder.jpg');
            background-size: cover;
        }
        .helper {
            position: absolute;
            width: 30px;
            height: 30px;
            left: 50%;
            transform: translateX(-50%);
            top: 50%;
            background-image: url('images/placeholder.jpg');
            background-size: cover;
        }
        #ptstxt {
            position: absolute;
            top: 30px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 20px;
            color: #333;
        }
        #statetxt {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 20px;
            color: #333;
        }
    </style>
</head>
<body>
    <noscript>
        Please turn on JavaScript to continue
        Zet JavaScript aan om verder te gaan
    </noscript>

    <?php if ($authorized): ?>
        <div id="gameArea" class="access-granted visible">
            <h2 id="statetxt"></h2>
            <h2 id="ptstxt">Points: 0</h2>
            <button id="btn"></button>
        </div>
        <h1 class="access-granted remove visible">Welcome to the Secret Page!</h1>
        <p class="access-granted remove visible">You got access to the NFC game. Congrats!</p>
    <?php else: ?>
        <h1 class="access-denied visible">Access Denied</h1>
        <p class="access-denied visible">You are not authorized to view this page.</p>
    <?php endif; ?>

    <?php if ($authorized): ?>
    <script>
        const debugTools = false;
        const allText = document.querySelectorAll('.access-granted');
        allText.forEach(element => {
            element.classList.remove('invisible');
            element.classList.add('visible');
            if (element.classList.contains('remove')) {
                setTimeout(() => {element.remove();}, 10000); // 10 seconds
            }
        });
        const gameArea = document.getElementById('gameArea');
        const btn = document.getElementById('btn');
        btn.style.display = 'block';
        let angleRad = 0;

        const stateText = document.getElementById('statetxt');
        const pointsText = document.getElementById('ptstxt');
        let points = 0;

        const moveCost = 20;
        const jumpCost = 50;

        let moveInterval = null;
        let jumpInterval = null;
        let directionInterval = null;

        let state = 'idle';

        const helpers = [];

        function btnClick() {
            addPoints();
        }
        btn.addEventListener('mouseover', () => {
            btn.style.cursor = 'pointer';
        });
        btn.addEventListener('click', btnClick);

        function randomBTNPos() {
            const randomX = Math.random() * (window.innerWidth - 100);
            const randomY = Math.random() * (window.innerHeight - 100);
            btn.style.left = randomX + 'px';
            btn.style.top = randomY + 'px';
        }

        function randomDirection() {
            // Generate a random angle in degrees (0 to 360)
            const angleDeg = Math.random() * 360;
            
            // Convert to radians for movement calculations
            angleRad = angleDeg * Math.PI / 180;

            // Rotate sprite to face movement direction
            btn.style.transform = `rotate(${angleDeg}deg)`;
        }

        function moveBTN() {
            // Define movement distance
            const speed = 2; // pixels
            
            // Calculate new position
            const currentLeft = parseFloat(getComputedStyle(btn).left);
            const currentTop = parseFloat(getComputedStyle(btn).top);
            let bounced = false;
            
            if (speed + currentLeft > window.innerWidth - 100) {
                angleRad = Math.PI - angleRad; // Reflect
                bounced = true;
            } else if (currentLeft < 0) {
                angleRad = Math.PI - angleRad; // Reflect
                bounced = true;
            }
            if (speed + currentTop > window.innerHeight - 100) {
                angleRad = -angleRad; // Reflect
                bounced = true;
            } else if (currentTop < 0) {
                angleRad = -angleRad; // Reflect
                bounced = true;
            }
            const newLeft = currentLeft + Math.cos(angleRad) * speed;
            const newTop = currentTop + Math.sin(angleRad) * speed;
            
            // Move sprite to new position
            btn.style.left = `${newLeft}px`;
            btn.style.top = `${newTop}px`;

            if (bounced) {
                btn.style.transform = `rotate(${angleRad * 180 / Math.PI}deg)`;
            }
        }

        function clearIntervals() {
            if (moveInterval) {
                clearInterval(moveInterval);
                moveInterval = null;
            }
            if (jumpInterval) {
                clearInterval(jumpInterval);
                jumpInterval = null;
            }
            if (directionInterval) {
                clearInterval(directionInterval);
                directionInterval = null;
            }
        }

        function spawnHelper() {
            const helper = document.createElement('div');
            helper.className = 'helper';
            helper.style.left = `${gameArea.offsetWidth/2}px`;
            helper.style.top = `${gameArea.offsetHeight/2}px`;
            gameArea.appendChild(helper);
            helpers.push(helper);

            const speed = Math.random() + 1; // Random speed between 1 and 2
            const moveInterval = setInterval(() => {
                const helperRect = helper.getBoundingClientRect();
                const btnRect = btn.getBoundingClientRect();

                // Center positions
                const helperX = helperRect.left + helperRect.width / 2;
                const helperY = helperRect.top + helperRect.height / 2;
                const btnX = btnRect.left + btnRect.width / 2;
                const btnY = btnRect.top + btnRect.height / 2;

                // Vector from helper to button
                const dx = btnX - helperX;
                const dy = btnY - helperY;
                const dist = Math.sqrt(dx * dx + dy * dy);

                // Move a step toward the button
                const stepX = (dx / dist) * speed;
                const stepY = (dy / dist) * speed;

                // Update helper position (relative to parent)
                const newLeft = parseFloat(helper.style.left) + stepX;
                const newTop = parseFloat(helper.style.top) + stepY;
                helper.style.left = `${newLeft}px`;
                helper.style.top = `${newTop}px`;
            }, 16);

            const pointInterval = setInterval(() => {
                const helperRect = helper.getBoundingClientRect();
                const btnRect = btn.getBoundingClientRect();

                // Check for collision
                if (helperRect.left < btnRect.right &&
                    helperRect.right > btnRect.left &&
                    helperRect.top < btnRect.bottom &&
                    helperRect.bottom > btnRect.top) {
                    addPoints();
                }
            }, 1000);
        }

        function addPoints() {
            points++;
            updatePoints();
            if (debugTools) {
                updateStateText();
            }
            if (points >= moveCost && points < jumpCost && state !== 'move') {
                state = 'move';
                moveInterval = setInterval(() => {moveBTN();}, 16);
                directionInterval = setInterval(() => {randomDirection();}, 5000);
            }
            if (points >= jumpCost && state !== 'jump') {
                clearIntervals();
                state = 'jump';
                jumpInterval = setInterval(() => {randomBTNPos(); btn.style.transform = `rotate(${0}deg)`;}, 1500);
                spawnHelper();
            }
        }

        function updatePoints() {
            pointsText.innerHTML = `Points: ${points}`;
        }

        function updateStateText() {
            if (state === 'move') {
                stateText.innerHTML = `State: move`;
            } else if (state === 'jump') {
                stateText.innerHTML = 'State: jump';
            } else {
                stateText.innerHTML = `State: idle`;
            }
        }

        if (debugTools) {
            const keyPresses = [];
            function keyPressHandler() {
                keyPresses.push(event.key);
                console.log(keyPresses);
                if (keyPresses.length >= 5 && keyPresses.slice(-5).every(k => k === '.')) {
                    keyPresses.length = 0;
                }
                if (keyPresses.includes('d') && keyPresses.includes('e') && keyPresses.includes('b') && keyPresses.includes('u') && keyPresses.includes('g')) {
                    keyPresses.length = 0;
                    document.removeEventListener('keydown', keyPressHandler);
                    points += 1000;
                    updatePoints();
                    updateStateText();
                }
            }
            document.addEventListener('keydown', keyPressHandler);
        }
    </script>
    <?php endif; ?>
</body>
</html>
