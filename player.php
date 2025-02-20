<?php

$debug = false; // Set this to false for normal use, true to debug API response if having issue's with the player

$myapi_url = ""; // Add your own deployed API URL (e.g., https://your-api.vercel.app) or with anyother API URL if you are using.
$id = $_GET['id'] ?? '';
$ep = $_GET['ep'] ?? '';
$server = $_GET['server'] ?? 'hd-1';
$type = $_GET['type'] ?? 'sub'; // sub or dub or raw by default it will be sub.

$api_url = "$myapi_url/api/v2/hianime/episode/sources?animeEpisodeId=$id&ep=$ep&server=$server&category=$type"; // Use your API's endpoint if your using another type of api or something else but if you are using the same api as mine then you can use this.
$json = file_get_contents($api_url);

if (!$json) {
    die("Error: Could not fetch API data");
}

$episode = json_decode($json, true);

if ($debug) {
    echo "<pre>";
    print_r($episode);
    echo "</pre>";
    exit;
}

if (!isset($episode['data']['sources'][0]['url'])) {
    die("Failed to Get the Video. Please come Back Later Or Refresh The Page");
}

$video = $episode['data']['sources'][0]['url'];

// Extract data from API response
$tracks = [];
$thumbnails = '';
$introStart = $episode['data']['intro']['start'] ?? null;
$introEnd = $episode['data']['intro']['end'] ?? null;
$outroStart = $episode['data']['outro']['start'] ?? null;
$outroEnd = $episode['data']['outro']['end'] ?? null;

foreach ($episode['data']['tracks'] ?? [] as $track) {
    if ($track['kind'] === 'captions') {
        $tracks[] = $track;
    } elseif ($track['kind'] === 'thumbnails') {
        $thumbnails = $track['file'] ?? '';
    }
}

// Generate chapters VTT
$vttContent = "WEBVTT\n\n";
if ($introStart !== null && $introEnd !== null) {
    $vttContent .= gmdate('H:i:s', $introStart) . ".000 --> " . gmdate('H:i:s', $introEnd) . ".000\n";
    $vttContent .= "Intro\n\n";
}
if ($outroStart !== null && $outroEnd !== null) {
    $vttContent .= gmdate('H:i:s', $outroStart) . ".000 --> " . gmdate('H:i:s', $outroEnd) . ".000\n";
    $vttContent .= "Outro\n\n";
}
$chaptersVtt = 'data:text/vtt;base64,' . base64_encode($vttContent);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anime Player</title>
    <link rel="stylesheet" href="https://cdn.vidstack.io/player/theme.css" />
    <link rel="stylesheet" href="https://cdn.vidstack.io/player/video.css" />
    <style>
        html, body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            height: 100%;
        }
        #target {
            width: 100%;
            height: 100vh;
            aspect-ratio: 16/9;
            position: relative;
        }
        #skip-buttons {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            gap: 10px;
        }
        .skip-btn {
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s;
            display: none;
        }
        .skip-btn:hover {
            background: rgba(0, 0, 0, 0.9);
        }
    </style>
</head>
<body>
    <div id="target"></div>
    <div id="skip-buttons">
        <button class="skip-btn" id="skip-intro">Skip Intro</button>
        <button class="skip-btn" id="skip-outro">Skip Outro</button>
    </div>

    <script type="module">
        import { VidstackPlayer, VidstackPlayerLayout } from 'https://cdn.vidstack.io/player';

        // Player configuration
        const player = await VidstackPlayer.create({
            target: '#target',
            title: 'Anime Player',
            src: '<?= htmlspecialchars($video, ENT_QUOTES, 'UTF-8') ?>',
            poster: '',
            layout: new VidstackPlayerLayout({
                thumbnails: '<?= htmlspecialchars($thumbnails, ENT_QUOTES, 'UTF-8') ?>'
            }),
            tracks: [
                <?php foreach ($tracks as $track): ?>
                {
                    src: '<?= htmlspecialchars($track['file'], ENT_QUOTES, 'UTF-8') ?>',
                    label: '<?= htmlspecialchars($track['label'], ENT_QUOTES, 'UTF-8') ?>',
                    kind: 'captions',
                    type: 'vtt',
                    default: <?= isset($track['default']) && $track['default'] ? 'true' : 'false' ?>,
                },
                <?php endforeach; ?>
                {
                    src: '<?= $chaptersVtt ?>',
                    kind: 'chapters',
                    type: 'vtt',
                    default: true,
                }
            ],
        });

        // Skip functionality
        const skipIntro = document.getElementById('skip-intro');
        const skipOutro = document.getElementById('skip-outro');
        
        // Get timestamps from PHP
        const introStart = <?= $introStart ?? 0 ?>;
        const introEnd = <?= $introEnd ?? 0 ?>;
        const outroStart = <?= $outroStart ?? 0 ?>;
        const outroEnd = <?= $outroEnd ?? 0 ?>;

        // Update button visibility
        player.addEventListener('timeupdate', () => {
            const currentTime = player.currentTime;
            
            // Intro handling
            if (introStart && introEnd) {
                skipIntro.style.display = (currentTime >= introStart && currentTime <= introEnd) 
                    ? 'block' : 'none';
            }
            
            // Outro handling
            if (outroStart && outroEnd) {
                skipOutro.style.display = (currentTime >= outroStart && currentTime <= outroEnd) 
                    ? 'block' : 'none';
            }
        });

        // Skip actions
        if (introEnd) {
            skipIntro.addEventListener('click', () => player.currentTime = introEnd);
        }
        if (outroEnd) {
            skipOutro.addEventListener('click', () => player.currentTime = outroEnd);
        }

        // Error handling
        player.addEventListener('error', (event) => {
            console.error("Player Error:", event.detail);
        });
    </script>
</body>
</html>
