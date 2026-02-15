<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
include "../config/db.php";

$song_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT * FROM songs WHERE id = ?");
$stmt->bind_param("i", $song_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Song not found'); window.location.href='song.php';</script>";
    exit;
}

$song = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($song['title']); ?> - View Song</title>
    <link rel="stylesheet" href="../frontend/assets/css/create.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <style>
        .song-view-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .song-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            border-radius: 12px;
            color: white;
            margin-bottom: 30px;
        }
        
        .song-header h1 {
            margin: 0 0 10px 0;
            font-size: 32px;
        }
        
        .song-meta {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-top: 15px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .audio-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        /* Audio Player Styles */
        .audio-player-container {
            background: #1a1a1a;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .audio-controls {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .play-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #4ade80;
            border: none;
            color: #000;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .play-btn:hover {
            background: #22c55e;
            transform: scale(1.05);
        }
        
        .time-display {
            color: #b5b5b5;
            font-size: 14px;
            min-width: 100px;
        }
        
        .progress-bar-container {
            flex: 1;
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            cursor: pointer;
            position: relative;
        }
        
        .progress-bar {
            height: 100%;
            background: #4ade80;
            border-radius: 3px;
            width: 0%;
            transition: width 0.1s;
        }
        
        .volume-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .volume-slider {
            width: 80px;
        }
        
        /* Lyrics Display */
        .lyrics-container {
            background: #f5f5f5;
            border-radius: 12px;
            padding: 30px;
            max-height: 500px;
            overflow-y: auto;
        }
        
        .lyrics-container.synced {
            background: #1a1a1a;
        }
        
        .lyric-line {
            padding: 12px;
            margin: 8px 0;
            border-radius: 8px;
            transition: all 0.3s;
            color: #333;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .lyrics-container.synced .lyric-line {
            color: #666;
        }
        
        .lyric-line.active {
            background: #4ade80;
            color: #000;
            font-weight: 600;
            transform: scale(1.02);
        }
        
        .static-lyrics {
            white-space: pre-wrap;
            color: #333;
            line-height: 1.8;
            font-size: 16px;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            color: #333;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            margin-bottom: 20px;
            transition: all 0.2s;
        }
        
        .back-btn:hover {
            background: #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="song-view-container">
        <a href="song.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i> Back to Songs
        </a>

        <div class="song-header">
            <h1><?php echo htmlspecialchars($song['title']); ?></h1>
            <div class="song-meta">
                <div class="meta-item">
                    <i class="fa-solid fa-user"></i>
                    <span><?php echo htmlspecialchars($song['artist']); ?></span>
                </div>
                <?php if ($song['album']): ?>
                <div class="meta-item">
                    <i class="fa-solid fa-compact-disc"></i>
                    <span><?php echo htmlspecialchars($song['album']); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($song['has_audio']): ?>
                <div class="audio-badge">
                    <i class="fa-solid fa-headphones"></i> Audio Available
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($song['has_audio']): ?>
        <!-- Audio Player -->
        <div class="audio-player-container">
            <div class="audio-controls">
                <button class="play-btn" id="playBtn">
                    <i class="fa-solid fa-play"></i>
                </button>
                <span class="time-display">
                    <span id="currentTime">0:00</span> / <span id="duration">0:00</span>
                </span>
                <div class="progress-bar-container" id="progressContainer">
                    <div class="progress-bar" id="progressBar"></div>
                </div>
                <div class="volume-control">
                    <i class="fa-solid fa-volume-up"></i>
                    <input type="range" min="0" max="100" value="80" class="volume-slider" id="volumeSlider">
                </div>
            </div>
            <audio id="audioPlayer" preload="metadata">
                <source src="../uploads/audio/<?php echo htmlspecialchars($song['audio_file']); ?>" type="audio/mpeg">
            </audio>
        </div>

        <!-- Synced Lyrics -->
        <div class="lyrics-container synced" id="lyricsContainer">
            <!-- Lyrics will be populated by JavaScript -->
        </div>
        <?php else: ?>
        <!-- Static Lyrics -->
        <div class="lyrics-container">
            <pre class="static-lyrics"><?php echo htmlspecialchars($song['lyrics']); ?></pre>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($song['has_audio']): ?>
    <script>
        const audioPlayer = document.getElementById('audioPlayer');
        const playBtn = document.getElementById('playBtn');
        const currentTimeEl = document.getElementById('currentTime');
        const durationEl = document.getElementById('duration');
        const progressBar = document.getElementById('progressBar');
        const progressContainer = document.getElementById('progressContainer');
        const volumeSlider = document.getElementById('volumeSlider');
        const lyricsContainer = document.getElementById('lyricsContainer');
        
        let lrcData = [];
        let currentLineIndex = -1;

        // Load LRC file
        fetch('../uploads/lyrics/<?php echo htmlspecialchars($song['lrc_file']); ?>')
            .then(response => response.text())
            .then(lrcContent => {
                lrcData = parseLRC(lrcContent);
                displayLyrics();
            })
            .catch(error => {
                console.error('Error loading LRC file:', error);
                lyricsContainer.innerHTML = '<p style="color: #ff6b6b;">Error loading synced lyrics</p>';
            });

        // Parse LRC format
        function parseLRC(lrcText) {
            const lines = lrcText.split('\n');
            const parsed = [];
            
            lines.forEach(line => {
                const match = line.match(/\[(\d{2}):(\d{2})\.(\d{2,3})\](.*)/);
                if (match) {
                    const minutes = parseInt(match[1]);
                    const seconds = parseInt(match[2]);
                    const milliseconds = parseInt(match[3].padEnd(3, '0'));
                    const text = match[4].trim();
                    
                    const timeInSeconds = minutes * 60 + seconds + milliseconds / 1000;
                    
                    if (text) {
                        parsed.push({ time: timeInSeconds, text: text });
                    }
                }
            });
            
            return parsed.sort((a, b) => a.time - b.time);
        }

        // Display lyrics
        function displayLyrics() {
            lyricsContainer.innerHTML = '';
            lrcData.forEach((line, index) => {
                const lineEl = document.createElement('div');
                lineEl.className = 'lyric-line';
                lineEl.textContent = line.text;
                lineEl.dataset.index = index;
                lyricsContainer.appendChild(lineEl);
            });
        }

        // Play/Pause
        playBtn.addEventListener('click', () => {
            if (audioPlayer.paused) {
                audioPlayer.play();
                playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
            } else {
                audioPlayer.pause();
                playBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
            }
        });

        // Update duration
        audioPlayer.addEventListener('loadedmetadata', () => {
            durationEl.textContent = formatTime(audioPlayer.duration);
        });

        // Update time and sync lyrics
        audioPlayer.addEventListener('timeupdate', () => {
            const currentTime = audioPlayer.currentTime;
            const duration = audioPlayer.duration;
            
            // Update time display
            currentTimeEl.textContent = formatTime(currentTime);
            
            // Update progress bar
            const progressPercent = (currentTime / duration) * 100;
            progressBar.style.width = progressPercent + '%';
            
            // Sync lyrics
            syncLyrics(currentTime);
        });

        // Sync lyrics with audio
        function syncLyrics(currentTime) {
            let activeIndex = -1;
            
            for (let i = 0; i < lrcData.length; i++) {
                if (currentTime >= lrcData[i].time) {
                    activeIndex = i;
                } else {
                    break;
                }
            }
            
            if (activeIndex !== currentLineIndex) {
                // Remove previous active
                const prevActive = lyricsContainer.querySelector('.lyric-line.active');
                if (prevActive) {
                    prevActive.classList.remove('active');
                }
                
                // Add new active
                if (activeIndex >= 0) {
                    const activeLine = lyricsContainer.querySelector(`[data-index="${activeIndex}"]`);
                    if (activeLine) {
                        activeLine.classList.add('active');
                        // Auto scroll
                        activeLine.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
                
                currentLineIndex = activeIndex;
            }
        }

        // Seek
        progressContainer.addEventListener('click', (e) => {
            const rect = progressContainer.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const width = rect.width;
            const percentage = clickX / width;
            audioPlayer.currentTime = percentage * audioPlayer.duration;
        });

        // Volume
        volumeSlider.addEventListener('input', (e) => {
            audioPlayer.volume = e.target.value / 100;
        });
        audioPlayer.volume = 0.8;

        // Format time helper
        function formatTime(seconds) {
            if (isNaN(seconds)) return '0:00';
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        }

        // Auto play next line hint
        audioPlayer.addEventListener('ended', () => {
            playBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
        });
    </script>
    <?php endif; ?>
</body>
</html>
