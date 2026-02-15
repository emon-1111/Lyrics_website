<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
include "../config/db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Song - Admin</title>
    <link rel="stylesheet" href="../frontend/assets/css/create.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="page-header">
            <h1><i class="fa-solid fa-music"></i> Create New Song</h1>
            <a href="dashboard.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
        </header>

        <form id="create-song-form" method="POST" action="save_song.php" enctype="multipart/form-data">
            <div class="form-section">
                <h2>Basic Information</h2>
                
                <div class="form-group">
                    <label for="title">Song Title *</label>
                    <input type="text" id="title" name="title" required placeholder="Enter song title">
                </div>

                <div class="form-group">
                    <label for="artist">Artist *</label>
                    <input type="text" id="artist" name="artist" required placeholder="Enter artist name">
                </div>

                <div class="form-group">
                    <label for="album">Album</label>
                    <input type="text" id="album" name="album" placeholder="Enter album name (optional)">
                </div>
            </div>

            <!-- Audio Option Toggle -->
            <div class="form-section">
                <div class="audio-toggle">
                    <label class="toggle-label">
                        <input type="checkbox" id="has-audio-toggle" name="has_audio" value="1">
                        <span class="toggle-text">
                            <i class="fa-solid fa-headphones"></i> 
                            This song has audio (MP3 + LRC sync)
                        </span>
                    </label>
                    <p class="toggle-hint">Enable this if you want to upload MP3 and synced lyrics (LRC file)</p>
                </div>
            </div>

            <!-- Audio Upload Section (Hidden by default) -->
            <div id="audio-section" class="form-section" style="display: none;">
                <h2><i class="fa-solid fa-file-audio"></i> Audio Files</h2>
                
                <div class="form-group">
                    <label for="audio_file">MP3 File *</label>
                    <input type="file" id="audio_file" name="audio_file" accept=".mp3" class="file-input">
                    <p class="file-hint">Upload the song audio file (MP3 format only)</p>
                </div>

                <div class="form-group">
                    <label for="lrc_file">LRC File (Synced Lyrics) *</label>
                    <input type="file" id="lrc_file" name="lrc_file" accept=".lrc" class="file-input">
                    <p class="file-hint">Upload synchronized lyrics file (.lrc format)</p>
                    <a href="https://lrc-maker.github.io/" target="_blank" class="helper-link">
                        <i class="fa-solid fa-external-link"></i> Need help creating LRC files?
                    </a>
                </div>
            </div>

            <!-- Lyrics Section -->
            <div class="form-section">
                <h2><i class="fa-solid fa-align-left"></i> Lyrics</h2>
                <p class="section-hint" id="lyrics-hint">
                    Enter the song lyrics (one line per verse/chorus)
                </p>
                
                <div class="form-group">
                    <label for="lyrics">Song Lyrics *</label>
                    <textarea 
                        id="lyrics" 
                        name="lyrics" 
                        rows="15" 
                        required 
                        placeholder="Enter lyrics here...&#10;Line 1&#10;Line 2&#10;Line 3"
                    ></textarea>
                    <p class="lyrics-counter"><span id="line-count">0</span> lines</p>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-save"></i> Create Song
                </button>
                <button type="reset" class="btn-secondary">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
            </div>
        </form>
    </div>

    <script>
        // Toggle audio section
        const audioToggle = document.getElementById('has-audio-toggle');
        const audioSection = document.getElementById('audio-section');
        const audioFileInput = document.getElementById('audio_file');
        const lrcFileInput = document.getElementById('lrc_file');
        const lyricsHint = document.getElementById('lyrics-hint');

        audioToggle.addEventListener('change', function() {
            if (this.checked) {
                audioSection.style.display = 'block';
                audioFileInput.required = true;
                lrcFileInput.required = true;
                lyricsHint.innerHTML = '<strong>Note:</strong> When using LRC file, these lyrics are for reference only. The LRC file will be used for syncing.';
            } else {
                audioSection.style.display = 'none';
                audioFileInput.required = false;
                lrcFileInput.required = false;
                lyricsHint.textContent = 'Enter the song lyrics (one line per verse/chorus)';
            }
        });

        // Line counter for lyrics
        const lyricsTextarea = document.getElementById('lyrics');
        const lineCountSpan = document.getElementById('line-count');

        lyricsTextarea.addEventListener('input', function() {
            const lines = this.value.split('\n').filter(line => line.trim() !== '');
            lineCountSpan.textContent = lines.length;
        });

        // Form validation
        document.getElementById('create-song-form').addEventListener('submit', function(e) {
            if (audioToggle.checked) {
                if (!audioFileInput.files.length || !lrcFileInput.files.length) {
                    e.preventDefault();
                    alert('Please upload both MP3 and LRC files when audio option is enabled.');
                    return false;
                }
            }
        });
    </script>
</body>
</html>