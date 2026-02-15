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
    <title>Create Song - Lyrics Only</title>
    <link rel="stylesheet" href="../frontend/assets/css/create.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="page-header">
            <h1><i class="fa-solid fa-align-left"></i> Create Song (Lyrics Only)</h1>
            <a href="create_song_choice.php" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> Back to Options
            </a>
        </header>

        <form method="POST" action="save_song.php">
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

            <div class="form-section">
                <h2><i class="fa-solid fa-align-left"></i> Lyrics</h2>
                <p class="section-hint">Enter the song lyrics (one line per verse/chorus)</p>
                
                <div class="form-group">
                    <label for="lyrics">Song Lyrics *</label>
                    <textarea 
                        id="lyrics" 
                        name="lyrics" 
                        rows="20" 
                        required 
                        placeholder="Enter lyrics here...&#10;Line 1&#10;Line 2&#10;Line 3"
                    ></textarea>
                    <p class="lyrics-counter"><span id="line-count">0</span> lines</p>
                </div>
            </div>

            <!-- Hidden field to indicate this is lyrics-only -->
            <input type="hidden" name="has_audio" value="0">

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
        // Line counter for lyrics
        const lyricsTextarea = document.getElementById('lyrics');
        const lineCountSpan = document.getElementById('line-count');

        lyricsTextarea.addEventListener('input', function() {
            const lines = this.value.split('\n').filter(line => line.trim() !== '');
            lineCountSpan.textContent = lines.length;
        });
    </script>
</body>
</html>