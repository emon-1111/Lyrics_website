<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
    <title>Create Song - With Audio</title>
    <link rel="stylesheet" href="../frontend/assets/css/create.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <style>
        .audio-section {
            background: rgba(74, 222, 128, 0.05);
            border: 2px solid rgba(74, 222, 128, 0.2);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .audio-section h2 {
            color: #4ade80;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .file-input-wrapper {
            position: relative;
            margin-bottom: 20px;
        }
        
        .file-input {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px dashed rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: #fff;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-input:hover {
            border-color: #4ade80;
            background: rgba(74, 222, 128, 0.1);
        }
        
        .file-hint {
            font-size: 13px;
            color: #b5b5b5;
            margin-top: 8px;
            display: block;
        }
        
        .helper-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #4ade80;
            text-decoration: none;
            font-size: 14px;
            margin-top: 10px;
            transition: all 0.2s;
        }
        
        .helper-link:hover {
            text-decoration: underline;
            gap: 8px;
        }
        
        .premium-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(74, 222, 128, 0.1);
            color: #4ade80;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-left: 15px;
        }
        
        .form-section.lyrics-ref {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .info-box {
            background: rgba(102, 126, 234, 0.1);
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .info-box p {
            color: #d4d4d4;
            margin: 0;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .info-box strong {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="page-header">
            <h1>
                <i class="fa-solid fa-headphones"></i> Create Song (With Audio)
                <span class="premium-badge">
                    <i class="fa-solid fa-star"></i> PREMIUM
                </span>
            </h1>
            <a href="create_song_choice.php" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Back to Options
            </a>
        </header>

        <form method="POST" action="save_song.php" enctype="multipart/form-data">
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

            <!-- Audio Upload Section -->
            <div class="audio-section">
                <h2>
                    <i class="fa-solid fa-file-audio"></i> Audio Files
                </h2>
                
                <div class="info-box">
                    <p>
                        <strong>Note:</strong> Both MP3 and LRC files are required for synced playback. 
                        Need help? <a href="https://megalobiz.com/search/all" target="_blank" style="color: #4ade80;">Download LRC files here</a> 
                        or <a href="https://lrc-maker.github.io/" target="_blank" style="color: #4ade80;">create your own</a>.
                    </p>
                </div>

                <div class="file-input-wrapper">
                    <label for="audio_file">
                        <strong>MP3 File *</strong>
                    </label>
                    <input 
                        type="file" 
                        id="audio_file" 
                        name="audio_file" 
                        accept=".mp3" 
                        class="file-input" 
                        required
                    >
                    <span class="file-hint">
                        <i class="fa-solid fa-circle-info"></i> Upload the song audio file (MP3 format, max 10MB)
                    </span>
                </div>

                <div class="file-input-wrapper">
                    <label for="lrc_file">
                        <strong>LRC File (Synced Lyrics) *</strong>
                    </label>
                    <input 
                        type="file" 
                        id="lrc_file" 
                        name="lrc_file" 
                        accept=".lrc" 
                        class="file-input" 
                        required
                    >
                    <span class="file-hint">
                        <i class="fa-solid fa-circle-info"></i> Upload synchronized lyrics file (.lrc format)
                    </span>
                    <a href="https://lrc-maker.github.io/" target="_blank" class="helper-link">
                        <i class="fa-solid fa-external-link"></i> Create LRC file online
                    </a>
                </div>
            </div>

            <!-- Lyrics Section (Reference) -->
            <div class="form-section lyrics-ref">
                <h2><i class="fa-solid fa-align-left"></i> Lyrics (Reference)</h2>
                <p class="section-hint">
                    These lyrics are for reference only. The LRC file will be used for synced display.
                </p>
                
                <div class="form-group">
                    <label for="lyrics">Song Lyrics *</label>
                    <textarea 
                        id="lyrics" 
                        name="lyrics" 
                        rows="15" 
                        required 
                        placeholder="Enter lyrics here (for backup/reference)...&#10;Line 1&#10;Line 2&#10;Line 3"
                    ></textarea>
                    <p class="lyrics-counter"><span id="line-count">0</span> lines</p>
                </div>
            </div>

            <!-- Hidden field to indicate this has audio -->
            <input type="hidden" name="has_audio" value="1">

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-save"></i> Create Song with Audio
                </button>
                <button type="reset" class="btn-secondary">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
            </div>
        </form>
    </div>

    <script>
        // Line counter for lyrics
        const lyricsTextarea = document.getElementById('lyrics');
        const lineCountSpan = document.getElementById('line-count');

        lyricsTextarea.addEventListener('input', function() {
            const lines = this.value.split('\n').filter(line => line.trim() !== '');
            lineCountSpan.textContent = lines.length;
        });

        // File upload validation
        const audioFile = document.getElementById('audio_file');
        const lrcFile = document.getElementById('lrc_file');

        audioFile.addEventListener('change', function() {
            if (this.files.length > 0) {
                const fileSize = this.files[0].size / 1024 / 1024; // MB
                if (fileSize > 10) {
                    alert('MP3 file size should not exceed 10MB');
                    this.value = '';
                }
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!audioFile.files.length) {
                e.preventDefault();
                alert('Please upload an MP3 file');
                return false;
            }
            if (!lrcFile.files.length) {
                e.preventDefault();
                alert('Please upload an LRC file');
                return false;
            }
        });
    </script>
</body>
</html>