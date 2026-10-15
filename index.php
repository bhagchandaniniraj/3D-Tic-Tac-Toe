<?php
// Marwadi University - 3D Tic-Tac-Toe
// AWS EC2 Server Information (IMDSv2 & IMDSv1 Safe Auto Scaling Resolution)

function fetchUrlSafe($url, $headers = array(), $method = "GET") {
    if (!function_exists('curl_init')) {
        return false;
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_CONNECTTIMEOUT => 1,
        CURLOPT_TIMEOUT        => 1,
        CURLOPT_PROXY          => "",
        CURLOPT_NOPROXY        => "*"
    ));

    $result = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status === 200 && is_string($result) && trim($result) !== "") {
        return trim($result);
    }

    return false;
}

// 1. Attempt IMDSv2 Token
$token = fetchUrlSafe(
    "http://169.254.169.254/latest/api/token",
    array("X-aws-ec2-metadata-token-ttl-seconds: 21600"),
    "PUT"
);

$imdsHeaders = $token ? array("X-aws-ec2-metadata-token: " . $token) : array();

// 2. Fetch Metadata (Compatible with IMDSv2 and IMDSv1)
function getMetadataItem($path, $headers) {
    $val = fetchUrlSafe("http://169.254.169.254/latest/meta-data/" . $path, $headers);
    return ($val !== false) ? $val : null;
}

$publicIP         = getMetadataItem("public-ipv4", $imdsHeaders);
$privateIP        = getMetadataItem("local-ipv4", $imdsHeaders);
$instanceID       = getMetadataItem("instance-id", $imdsHeaders);
$availabilityZone = getMetadataItem("placement/availability-zone", $imdsHeaders);

// 3. Auto Scaling & Local Network Fallbacks
if (empty($privateIP)) {
    if (!empty($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] !== '127.0.0.1') {
        $privateIP = $_SERVER['SERVER_ADDR'];
    } else {
        $resolved = gethostbyname(gethostname());
        $privateIP = (!empty($resolved) && $resolved !== '127.0.0.1') ? $resolved : "Local / Private Subnet";
    }
}

if (empty($publicIP)) {
    $publicIP = "Private Subnet (Behind ALB / Auto Scaling)";
}

if (empty($instanceID)) {
    $instanceID = gethostname();
}

if (empty($availabilityZone)) {
    $availabilityZone = "Auto Scaling Node";
}

$serverHostname = gethostname();
$serverTime = gmdate("Y-m-d H:i:s") . " UTC";
$year = gmdate("Y");

function cleanOutput($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>3D Tic Tac Toe | Marwadi University</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Orbitron:wght@500;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --teal: #0d7377;
    --dark-teal: #09575a;
    --light-teal: #e8f7f6;
    --bg: #f7f9fb;
    --white: #ffffff;
    --text: #20343b;
    --muted: #7c9097;
    --border: #e5edf0;
    --orange: #ed9652;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: 'Inter', sans-serif;
    background:
        radial-gradient(circle at 10% 15%, #edf9f7, transparent 30%),
        var(--bg);
    color: var(--text);
}

header {
    background: white;
    border-bottom: 1px solid var(--border);
    padding: 18px 25px;
}

.header-inner {
    max-width: 1250px;
    margin: auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.brand {
    display: flex;
    align-items: center;
    gap: 15px;
}

.logo {
    width: 55px;
    height: 65px;
}

.brand h2 {
    margin: 0;
    font-size: 19px;
    font-weight: 800;
}

.brand p {
    font-size: 11px;
    color: var(--muted);
    letter-spacing: 2px;
    margin: 5px 0 0;
}

.host-badge {
    background: var(--light-teal);
    color: var(--teal);
    padding: 10px 15px;
    border-radius: 25px;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}

main {
    max-width: 1250px;
    margin: auto;
    padding: 40px 20px;
}

.hero {
    text-align: center;
    margin-bottom: 35px;
}

.hero small {
    color: var(--teal);
    letter-spacing: 3px;
    font-weight: 800;
}

.hero h1 {
    font-family: 'Orbitron', sans-serif;
    font-size: clamp(25px, 4vw, 43px);
    margin: 15px 0;
}

.hero h1 span {
    color: var(--teal);
}

.hero p {
    color: var(--muted);
    font-size: 14px;
    line-height: 1.7;
}

.layout {
    display: grid;
    grid-template-columns: minmax(0, 1.7fr) minmax(280px, 1fr);
    gap: 25px;
    align-items: start;
}

.card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 22px;
    box-shadow: 0 14px 45px rgba(25,65,75,0.06);
    overflow: hidden;
}

.card-header {
    padding: 22px 25px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    border-bottom: 1px solid var(--border);
}

.card-header h3 {
    font-size: 15px;
    margin: 0;
}

.tag {
    background: var(--light-teal);
    color: var(--teal);
    border-radius: 8px;
    padding: 8px 11px;
    font-size: 10px;
    font-weight: 800;
    white-space: nowrap;
}

#game {
    height: 510px;
    width: 100%;
    position: relative;
    background: radial-gradient(circle, white, #f5fafb);
    cursor: grab;
    touch-action: none;
}

#game:active {
    cursor: grabbing;
}

#game canvas {
    display: block;
    width: 100%;
    height: 100%;
}

.game-hint {
    position: absolute;
    bottom: 12px;
    width: 100%;
    text-align: center;
    font-size: 11px;
    color: #9aaeb3;
    pointer-events: none;
}

#loading {
    position: absolute;
    inset: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 20px;
    color: var(--muted);
    pointer-events: none;
}

.game-controls {
    padding: 20px 25px 25px;
    border-top: 1px solid var(--border);
}

.scoreboard {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.player {
    padding: 15px;
    border: 2px solid var(--border);
    border-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.player.active-x {
    border-color: var(--teal);
    background: #effaf9;
}

.player.active-o {
    border-color: var(--orange);
    background: #fff7ef;
}

.player b {
    font-family: 'Orbitron', sans-serif;
    font-size: 22px;
}

.player-x {
    color: var(--teal);
}

.player-o {
    color: var(--orange);
}

.status {
    margin: 16px 0;
    padding: 16px;
    background: #f3f8fa;
    border-radius: 12px;
    text-align: center;
    font-size: 13px;
    font-weight: 700;
}

.status strong {
    color: var(--teal);
}

button {
    width: 100%;
    background: var(--teal);
    border: none;
    color: white;
    padding: 16px;
    border-radius: 12px;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    transition: 0.2s;
}

button:hover {
    background: var(--dark-teal);
    transform: translateY(-2px);
}

button:focus-visible {
    outline: 3px solid #7ed7d2;
    outline-offset: 3px;
}

.sidebar {
    display: flex;
    flex-direction: column;
    gap: 22px;
}

.server-info {
    padding: 5px 24px 15px;
}

.info-row {
    padding: 16px 0;
    border-bottom: 1px solid var(--border);
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    color: var(--muted);
    font-size: 11px;
    margin-bottom: 7px;
}

.info-value {
    font-family: 'Orbitron', sans-serif;
    font-size: 13px;
    font-weight: 700;
    overflow-wrap: anywhere;
}

.info-row:first-child .info-value {
    color: var(--teal);
    font-size: 17px;
}

.info-note {
    padding: 15px 23px;
    background: var(--light-teal);
    color: #477d80;
    font-size: 11px;
    line-height: 1.8;
}

.instructions {
    padding: 22px 25px;
}

.instructions h3 {
    margin-top: 0;
    font-size: 15px;
}

.instructions p {
    color: var(--muted);
    font-size: 12px;
    line-height: 1.9;
}

.instructions b {
    color: var(--text);
}

footer {
    text-align: center;
    padding: 25px;
    color: #91a3aa;
    font-size: 11px;
    border-top: 1px solid var(--border);
}

@media (max-width: 900px) {
    .layout {
        grid-template-columns: 1fr;
    }

    #game {
        height: 470px;
    }
}

@media (max-width: 520px) {
    .brand h2 {
        font-size: 13px;
    }

    .brand p {
        font-size: 9px;
        letter-spacing: 1px;
    }

    .logo {
        width: 39px;
        height: 49px;
    }

    .host-badge {
        font-size: 10px;
        padding: 8px;
    }

    #game {
        height: 360px;
    }

    .card-header {
        padding: 17px;
    }

    .game-controls {
        padding: 16px;
    }

    .player {
        padding: 11px;
        font-size: 12px;
    }
}
</style>

</head>

<body>

<header>
    <div class="header-inner">

        <div class="brand">

            <!-- Stylized Marwadi University shield approximation -->
            <svg class="logo" viewBox="0 0 64 74"
                 xmlns="http://www.w3.org/2000/svg"
                 role="img"
                 aria-label="Marwadi University stylized logo">

                <path
                    d="M32 3 L60 12 V39 C60 54 47 65 32 71 C17 65 4 54 4 39 V12 Z"
                    fill="#0d7377"/>

                <path
                    d="M32 9 L54 16 V39 C54 51 44 59 32 64 C20 59 10 51 10 39 V16 Z"
                    fill="none"
                    stroke="white"
                    stroke-width="1.5"/>

                <path
                    d="M15 48 V23 H25 L32 34 L39 23 H49 V48 H41 V34 L32 47 L23 34 V48 Z"
                    fill="white"/>

            </svg>

            <div>
                <h2>MARWADI UNIVERSITY</h2>
                <p>INNOVATION THROUGH LEARNING</p>
            </div>

        </div>

        <div class="host-badge">
            ● AWS EC2 HOSTED
        </div>

    </div>
</header>

<main>

<section class="hero">
    <small>INTERACTIVE 3D GAMING EXPERIENCE</small>

    <h1>3D <span>TIC-TAC-TOE</span></h1>

    <p>
        Experience the classic strategy game in three dimensions.<br>
        Compete across 27 positions and 49 possible winning lines.
    </p>
</section>

<div class="layout">

    <section class="card">

        <div class="card-header">
            <h3>Interactive 3D Game Arena</h3>
            <span class="tag">3 × 3 × 3 GRID</span>
        </div>

        <div id="game">
            <div id="loading">Loading 3D Game...</div>

            <div class="game-hint">
                Drag to rotate • Scroll to zoom • Click a cube to play
            </div>
        </div>

        <div class="game-controls">

            <div class="scoreboard">

                <div id="playerX" class="player active-x">
                    <span class="player-x">✕ Player X</span>
                    <b id="scoreX" class="player-x">0</b>
                </div>

                <div id="playerO" class="player">
                    <span class="player-o">◯ Player O</span>
                    <b id="scoreO" class="player-o">0</b>
                </div>

            </div>

            <div id="status" class="status"
                 role="status" aria-live="polite">
                <strong>Player X</strong> — Your Turn!
            </div>

            <button id="reset" type="button">
                ↻ NEW GAME
            </button>

        </div>

    </section>

    <aside class="sidebar">

        <div class="card">

            <div class="card-header">
                <h3>AWS EC2 Server Information</h3>
                <span class="tag">LIVE SERVER</span>
            </div>

            <div class="server-info">

                <div class="info-row">
                    <div class="info-label">Public IPv4 Address</div>
                    <div class="info-value">
                        <?php echo cleanOutput($publicIP); ?>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Private IPv4 Address</div>
                    <div class="info-value">
                        <?php echo cleanOutput($privateIP); ?>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">EC2 Instance ID</div>
                    <div class="info-value">
                        <?php echo cleanOutput($instanceID); ?>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Availability Zone</div>
                    <div class="info-value">
                        <?php echo cleanOutput($availabilityZone); ?>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Server Hostname</div>
                    <div class="info-value">
                        <?php echo cleanOutput($serverHostname); ?>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-label">Server Time (UTC)</div>
                    <div class="info-value">
                        <?php echo cleanOutput($serverTime); ?>
                    </div>
                </div>

            </div>

            <div class="info-note">
                🔒 EC2 information is fetched by PHP directly
                from the AWS Instance Metadata Service (IMDSv2).
                Browser-side IP detection is not used.
                Metadata will show as unavailable when the
                service or field is not accessible.
            </div>

        </div>

        <div class="card instructions">

            <h3>How to Play</h3>

            <p><b>01.</b> Two players alternate between X and O.</p>

            <p><b>02.</b> Click an empty cube to place your mark.</p>

            <p><b>03.</b> Rotate the 3D board to inspect different layers.</p>

            <p><b>04.</b> Connect 3 marks in a valid straight line to win.</p>

            <p><b>05.</b> All 49 possible 3D winning lines are supported.</p>

            <p><b>06.</b> Click New Game to play another round.</p>

        </div>

    </aside>

</div>

</main>

<footer>
    &copy; <?php echo cleanOutput($year); ?>
    Marwadi University |
    AWS EC2 3D Tic-Tac-Toe Project
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/0.160.1/three.min.js"></script>

<script>
(function() {
    "use strict";

    const container = document.getElementById("game");
    const loading = document.getElementById("loading");
    const status = document.getElementById("status");

    if (typeof THREE === "undefined") {
        loading.textContent =
            "Three.js failed to load. Please check your internet connection.";
        return;
    }

    // Game state
    let board = new Array(27).fill(null);
    let current = "X";
    let gameOver = false;

    let scores = {
        X: 0,
        O: 0
    };

    const cells = [];
    const placedMarks = [];

    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0xf9fcfd);

    const camera = new THREE.PerspectiveCamera(
        42, 1, 0.1, 100
    );

    const renderer = new THREE.WebGLRenderer({
        antialias: true
    });

    renderer.setPixelRatio(
        Math.min(window.devicePixelRatio || 1, 2)
    );

    renderer.outputColorSpace = THREE.SRGBColorSpace;

    container.insertBefore(renderer.domElement, loading);
    loading.style.display = "none";

    // Lighting
    scene.add(new THREE.AmbientLight(0xffffff, 2));

    const directionalLight = new THREE.DirectionalLight(
        0xffffff, 2.5
    );

    directionalLight.position.set(6, 9, 8);
    scene.add(directionalLight);

    const fillLight = new THREE.DirectionalLight(
        0xaee5e2, 1
    );

    fillLight.position.set(-6, -4, -5);
    scene.add(fillLight);

    const gameGroup = new THREE.Group();
    scene.add(gameGroup);

    const spacing = 1.65;
    const cellSize = 1.08;

    function cellIndex(x, y, z) {
        return x + y * 3 + z * 9;
    }

    function cellPosition(x, y, z) {
        return new THREE.Vector3(
            (x - 1) * spacing,
            (y - 1) * spacing,
            (z - 1) * spacing
        );
    }

    // Generate 27 cube cells
    for (let z = 0; z < 3; z++) {
        for (let y = 0; y < 3; y++) {
            for (let x = 0; x < 3; x++) {

                const geometry = new THREE.BoxGeometry(
                    cellSize, cellSize, cellSize
                );

                const material = new THREE.MeshPhysicalMaterial({
                    color: 0xe9f3f4,
                    transparent: true,
                    opacity: 0.24,
                    roughness: 0.3,
                    depthWrite: false
                });

                const cube = new THREE.Mesh(
                    geometry, material
                );

                cube.position.copy(
                    cellPosition(x, y, z)
                );

                cube.userData.index = cellIndex(x, y, z);

                const edges = new THREE.LineSegments(
                    new THREE.EdgesGeometry(geometry),
                    new THREE.LineBasicMaterial({
                        color: 0xb9d2d5,
                        transparent: true,
                        opacity: 0.9
                    })
                );

                cube.add(edges);
                gameGroup.add(cube);
                cells.push(cube);
            }
        }
    }

    // All 49 possible 3D winning lines.
    function generateWinningLines() {
        const lines = [];
        const unique = new Set();

        for (let dx = -1; dx <= 1; dx++) {
            for (let dy = -1; dy <= 1; dy++) {
                for (let dz = -1; dz <= 1; dz++) {

                    if (dx === 0 && dy === 0 && dz === 0) {
                        continue;
                    }

                    for (let x = 0; x < 3; x++) {
                        for (let y = 0; y < 3; y++) {
                            for (let z = 0; z < 3; z++) {

                                const line = [];
                                let valid = true;

                                for (let k = 0; k < 3; k++) {
                                    const nx = x + k * dx;
                                    const ny = y + k * dy;
                                    const nz = z + k * dz;

                                    if (
                                        nx < 0 || nx > 2 ||
                                        ny < 0 || ny > 2 ||
                                        nz < 0 || nz > 2
                                    ) {
                                        valid = false;
                                        break;
                                    }

                                    line.push(
                                        cellIndex(nx, ny, nz)
                                    );
                                }

                                if (!valid) continue;

                                const key = [...line]
                                    .sort((a, b) => a - b)
                                    .join("-");

                                if (!unique.has(key)) {
                                    unique.add(key);
                                    lines.push(line);
                                }
                            }
                        }
                    }
                }
            }
        }

        return lines;
    }

    const winningLines = generateWinningLines();

    function checkWinner() {
        for (const line of winningLines) {
            const [a, b, c] = line;

            if (
                board[a] !== null &&
                board[a] === board[b] &&
                board[b] === board[c]
            ) {
                return line;
            }
        }

        return null;
    }

    // Create 3D X or O marks
    function makeMark(player, position) {
        const mark = new THREE.Group();

        const color = player === "X"
            ? 0x0d7377
            : 0xed9652;

        const material = new THREE.MeshStandardMaterial({
            color: color,
            metalness: 0.15,
            roughness: 0.3
        });

        if (player === "X") {
            const barGeometry = new THREE.CylinderGeometry(
                0.11, 0.11, 0.95, 16
            );

            const bar1 = new THREE.Mesh(
                barGeometry, material
            );

            const bar2 = new THREE.Mesh(
                barGeometry, material
            );

            bar1.rotation.z = Math.PI / 4;
            bar2.rotation.z = -Math.PI / 4;

            mark.add(bar1);
            mark.add(bar2);

        } else {
            const geometry = new THREE.TorusGeometry(
                0.36, 0.11, 14, 48
            );

            const ring = new THREE.Mesh(
                geometry, material
            );

            mark.add(ring);
        }

        mark.position.copy(position);
        mark.scale.setScalar(0.01);
        gameGroup.add(mark);

        const started = performance.now();

        function popIn(time) {
            if (!mark.parent) return;

            const progress = Math.min(
                (time - started) / 300, 1
            );

            const eased = 1 - Math.pow(
                1 - progress, 3
            );

            mark.scale.setScalar(
                Math.max(0.01, eased)
            );

            if (progress < 1) {
                requestAnimationFrame(popIn);
            }
        }

        requestAnimationFrame(popIn);

        placedMarks.push(mark);
    }

    let winLineMesh = null;

    function highlightWin(line) {
        line.forEach(i => {
            const cube = cells[i];
            cube.children[0].material.color.setHex(
                0x0d7377
            );
            cube.children[0].material.opacity = 1;
        });

        const points = line.map(
            i => cells[i].position.clone()
        );

        let maxDistance = -1;
        let start = points[0];
        let end = points[2];

        for (let a = 0; a < 3; a++) {
            for (let b = a + 1; b < 3; b++) {
                const d = points[a].distanceToSquared(
                    points[b]
                );

                if (d > maxDistance) {
                    maxDistance = d;
                    start = points[a];
                    end = points[b];
                }
            }
        }

        const direction = new THREE.Vector3()
            .subVectors(end, start);

        const geometry = new THREE.CylinderGeometry(
            0.04,
            0.04,
            direction.length(),
            12
        );

        const material = new THREE.MeshBasicMaterial({
            color: 0x24b69a,
            transparent: true,
            opacity: 0.9
        });

        winLineMesh = new THREE.Mesh(
            geometry, material
        );

        winLineMesh.position.addVectors(
            start, end
        ).multiplyScalar(0.5);

        winLineMesh.quaternion.setFromUnitVectors(
            new THREE.Vector3(0, 1, 0),
            direction.normalize()
        );

        gameGroup.add(winLineMesh);
    }

    function updatePlayers() {
        document.getElementById("scoreX").textContent =
            scores.X;

        document.getElementById("scoreO").textContent =
            scores.O;

        document.getElementById("playerX")
            .classList.toggle(
                "active-x",
                !gameOver && current === "X"
            );

        document.getElementById("playerO")
            .classList.toggle(
                "active-o",
                !gameOver && current === "O"
            );
    }

    function placeMark(i) {
        if (gameOver || board[i] !== null) {
            return;
        }

        board[i] = current;

        makeMark(
            current,
            cells[i].position.clone()
        );

        const winningLine = checkWinner();

        if (winningLine) {
            gameOver = true;
            scores[current]++;

            status.innerHTML =
                "<strong>Player " + current +
                " Wins!</strong> Congratulations!";

            highlightWin(winningLine);

        } else if (board.every(
            cell => cell !== null
        )) {
            gameOver = true;

            status.innerHTML =
                "<strong>Draw!</strong> No more moves available.";

        } else {
            current = current === "X" ? "O" : "X";

            status.innerHTML =
                "<strong>Player " + current +
                "</strong> — Your Turn!";
        }

        updatePlayers();
    }

    // Mouse picking
    const raycaster = new THREE.Raycaster();
    const mouse = new THREE.Vector2();

    function pick(clientX, clientY) {
        const rect = renderer.domElement.getBoundingClientRect();

        mouse.x =
            ((clientX - rect.left) / rect.width) * 2 - 1;

        mouse.y =
            -((clientY - rect.top) / rect.height) * 2 + 1;

        raycaster.setFromCamera(mouse, camera);

        const intersections =
            raycaster.intersectObjects(cells, false);

        const selected = intersections.find(
            hit => board[hit.object.userData.index] === null
        );

        if (selected) {
            placeMark(selected.object.userData.index);
        }
    }

    // Camera rotation and zoom
    let azimuth = 0.65;
    let elevation = 0.50;
    let radius = 16;

    function updateCamera() {
        elevation = Math.max(
            -1.4,
            Math.min(1.4, elevation)
        );

        radius = Math.max(
            8,
            Math.min(25, radius)
        );

        camera.position.set(
            radius * Math.cos(elevation) * Math.sin(azimuth),
            radius * Math.sin(elevation),
            radius * Math.cos(elevation) * Math.cos(azimuth)
        );

        camera.lookAt(0, 0, 0);
    }

    updateCamera();

    const pointers = new Map();

    let dragging = false;
    let moved = false;
    let lastX = 0;
    let lastY = 0;
    let startX = 0;
    let startY = 0;
    let pinchDistance = null;

    renderer.domElement.addEventListener(
        "pointerdown",
        function(e) {
            renderer.domElement.setPointerCapture(
                e.pointerId
            );

            pointers.set(e.pointerId, {
                x: e.clientX,
                y: e.clientY
            });

            if (pointers.size === 1) {
                dragging = true;
                moved = false;
                startX = lastX = e.clientX;
                startY = lastY = e.clientY;
            } else {
                dragging = false;
                moved = true;

                if (pointers.size === 2) {
                    const p = [...pointers.values()];

                    pinchDistance = Math.hypot(
                        p[0].x - p[1].x,
                        p[0].y - p[1].y
                    );
                }
            }
        }
    );

    renderer.domElement.addEventListener(
        "pointermove",
        function(e) {
            if (!pointers.has(e.pointerId)) {
                return;
            }

            pointers.set(e.pointerId, {
                x: e.clientX,
                y: e.clientY
            });

            if (pointers.size === 2) {
                const p = [...pointers.values()];

                const distance = Math.hypot(
                    p[0].x - p[1].x,
                    p[0].y - p[1].y
                );

                if (pinchDistance && distance > 0) {
                    radius *= pinchDistance / distance;
                    updateCamera();
                }

                pinchDistance = distance;
                return;
            }

            if (!dragging) return;

            const dx = e.clientX - lastX;
            const dy = e.clientY - lastY;

            if (
                Math.hypot(
                    e.clientX - startX,
                    e.clientY - startY
                ) > 5
            ) {
                moved = true;
            }

            if (moved) {
                azimuth -= dx * 0.007;
                elevation += dy * 0.007;
                updateCamera();
            }

            lastX = e.clientX;
            lastY = e.clientY;
        }
    );

    function finishPointer(e) {
        const wasSingle = pointers.size === 1;
        const wasMoved = moved;

        pointers.delete(e.pointerId);
        dragging = false;

        if (pointers.size < 2) {
            pinchDistance = null;
        }

        if (
            e.type === "pointerup" &&
            wasSingle &&
            !wasMoved
        ) {
            pick(e.clientX, e.clientY);
        }
    }

    renderer.domElement.addEventListener(
        "pointerup", finishPointer
    );

    renderer.domElement.addEventListener(
        "pointercancel", finishPointer
    );

    renderer.domElement.addEventListener(
        "wheel",
        function(e) {
            e.preventDefault();

            radius += e.deltaY * 0.012;
            updateCamera();
        },
        { passive: false }
    );

    // New game
    document.getElementById("reset")
        .addEventListener("click", function() {

            board = new Array(27).fill(null);
            current = "X";
            gameOver = false;

            placedMarks.forEach(function(mark) {
                gameGroup.remove(mark);

                mark.traverse(function(child) {
                    if (child.geometry) {
                        child.geometry.dispose();
                    }
                    if (child.material) {
                        child.material.dispose();
                    }
                });
            });

            placedMarks.length = 0;

            if (winLineMesh) {
                gameGroup.remove(winLineMesh);
                winLineMesh.geometry.dispose();
                winLineMesh.material.dispose();
                winLineMesh = null;
            }

            cells.forEach(function(cube) {
                cube.children[0].material.color.setHex(
                    0xb9d2d5
                );
                cube.children[0].material.opacity = 0.9;
            });

            status.innerHTML =
                "<strong>Player X</strong> — Your Turn!";

            updatePlayers();
        });

    // Responsive canvas
    function resize() {
        const width = container.clientWidth;
        const height = container.clientHeight;

        if (!width || !height) return;

        camera.aspect = width / height;
        camera.updateProjectionMatrix();

        renderer.setSize(width, height, false);
    }

    window.addEventListener("resize", resize);

    if (typeof ResizeObserver !== "undefined") {
        new ResizeObserver(resize).observe(container);
    }

    resize();

    // Continuous Three.js rendering
    function animate() {
        requestAnimationFrame(animate);
        renderer.render(scene, camera);
    }

    animate();
    updatePlayers();

})();
</script>

</body>
</html>