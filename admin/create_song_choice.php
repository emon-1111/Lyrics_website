<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Song - Choose Type</title>
    <link rel="stylesheet" href="../frontend/assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <style>
        .choice-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 40px 20px;
        }

        .choice-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .choice-header h1 {
            font-size: 32px;
            color: #fff;
            margin-bottom: 10px;
        }

        .choice-header p {
            color: #b5b5b5;
            font-size: 16px;
        }

        .choice-cards {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }

        .choice-card {
            background: linear-gradient(180deg, #111, #0f0f0f);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 16px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            display: block;
        }

        .choice-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255, 255, 255, 0.12);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .choice-card.lyrics-only:hover {
            border-color: #667eea;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .choice-card.with-audio:hover {
            border-color: #4ade80;
            box-shadow: 0 10px 30px rgba(74, 222, 128, 0.3);
        }

        .choice-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            transition: all 0.3s;
        }

        .choice-card.lyrics-only .choice-icon {
            color: #667eea;
        }

        .choice-card.with-audio .choice-icon {
            color: #4ade80;
        }

        .choice-card:hover .choice-icon {
            transform: scale(1.1);
        }

        .choice-card.lyrics-only:hover .choice-icon {
            background: rgba(102, 126, 234, 0.1);
        }

        .choice-card.with-audio:hover .choice-icon {
            background: rgba(74, 222, 128, 0.1);
        }

        .choice-title {
            font-size: 24px;
            color: #fff;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .choice-description {
            color: #b5b5b5;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .choice-features {
            list-style: none;
            padding: 0;
            margin: 20px 0;
            text-align: left;
        }

        .choice-features li {
            color: #d4d4d4;
            font-size: 14px;
            padding: 8px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .choice-features li i {
            color: #4ade80;
            font-size: 16px;
        }

        .choice-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }

        .badge-simple {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }

        .badge-premium {
            background: rgba(74, 222, 128, 0.1);
            color: #4ade80;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #b5b5b5;
            text-decoration: none;
            margin-bottom: 30px;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: #fff;
        }

        @media (max-width: 768px) {
            .choice-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="choice-container">
        <a href="dashboard.php" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>

        <div class="choice-header">
            <h1><i class="fa-solid fa-music"></i> Create New Song</h1>
            <p>Choose how you want to create your song</p>
        </div>

        <div class="choice-cards">
            <!-- Lyrics Only Option -->
            <a href="create_song_lyrics.php" class="choice-card lyrics-only">
                <div class="choice-icon">
                    <i class="fa-solid fa-align-left"></i>
                </div>
                <h2 class="choice-title">Lyrics Only</h2>
                <p class="choice-description">
                    Create a song with text lyrics for manual scrolling
                </p>
                <ul class="choice-features">
                    <li><i class="fa-solid fa-check"></i> Quick and simple</li>
                    <li><i class="fa-solid fa-check"></i> Text-based lyrics</li>
                    <li><i class="fa-solid fa-check"></i> Manual scrolling</li>
                    <li><i class="fa-solid fa-check"></i> Perfect for user content</li>
                </ul>
                <span class="choice-badge badge-simple">SIMPLE</span>
            </a>

            <!-- With Audio Option -->
            <a href="create_song_audio.php" class="choice-card with-audio">
                <div class="choice-icon">
                    <i class="fa-solid fa-headphones"></i>
                </div>
                <h2 class="choice-title">Lyrics + Audio</h2>
                <p class="choice-description">
                    Create a premium song with MP3 and synced lyrics
                </p>
                <ul class="choice-features">
                    <li><i class="fa-solid fa-check"></i> Upload MP3 file</li>
                    <li><i class="fa-solid fa-check"></i> Upload LRC file</li>
                    <li><i class="fa-solid fa-check"></i> Auto-scrolling lyrics</li>
                    <li><i class="fa-solid fa-check"></i> Professional playback</li>
                </ul>
                <span class="choice-badge badge-premium">PREMIUM</span>
            </a>
        </div>
    </div>
</body>
</html>