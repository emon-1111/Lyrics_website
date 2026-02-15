<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
include "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $artist = trim($_POST['artist']);
    $album = trim($_POST['album']);
    $lyrics = trim($_POST['lyrics']);
    $has_audio = isset($_POST['has_audio']) ? 1 : 0;
    $created_by = $_SESSION['user_id'];
    
    $audio_filename = null;
    $lrc_filename = null;
    
    // Handle file uploads if audio is enabled
    if ($has_audio) {
        // Create upload directories if they don't exist
        $audio_dir = "../uploads/audio/";
        $lyrics_dir = "../uploads/lyrics/";
        
        if (!is_dir($audio_dir)) mkdir($audio_dir, 0777, true);
        if (!is_dir($lyrics_dir)) mkdir($lyrics_dir, 0777, true);
        
        // Upload MP3 file
        if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === 0) {
            $audio_ext = pathinfo($_FILES['audio_file']['name'], PATHINFO_EXTENSION);
            if (strtolower($audio_ext) === 'mp3') {
                $audio_filename = uniqid('song_') . '.mp3';
                $audio_path = $audio_dir . $audio_filename;
                
                if (!move_uploaded_file($_FILES['audio_file']['tmp_name'], $audio_path)) {
                    echo "<script>alert('Error uploading MP3 file'); window.history.back();</script>";
                    exit;
                }
            } else {
                echo "<script>alert('Only MP3 files are allowed'); window.history.back();</script>";
                exit;
            }
        }
        
        // Upload LRC file
        if (isset($_FILES['lrc_file']) && $_FILES['lrc_file']['error'] === 0) {
            $lrc_ext = pathinfo($_FILES['lrc_file']['name'], PATHINFO_EXTENSION);
            if (strtolower($lrc_ext) === 'lrc') {
                $lrc_filename = uniqid('lyrics_') . '.lrc';
                $lrc_path = $lyrics_dir . $lrc_filename;
                
                if (!move_uploaded_file($_FILES['lrc_file']['tmp_name'], $lrc_path)) {
                    echo "<script>alert('Error uploading LRC file'); window.history.back();</script>";
                    exit;
                }
            } else {
                echo "<script>alert('Only LRC files are allowed'); window.history.back();</script>";
                exit;
            }
        }
        
        // Validate both files were uploaded
        if (!$audio_filename || !$lrc_filename) {
            echo "<script>alert('Both MP3 and LRC files are required when audio is enabled'); window.history.back();</script>";
            exit;
        }
    }
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO songs (title, artist, album, lyrics, created_by, has_audio, audio_file, lrc_file, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssiiss", $title, $artist, $album, $lyrics, $created_by, $has_audio, $audio_filename, $lrc_filename);
    
    if ($stmt->execute()) {
        echo "<script>alert('Song created successfully!'); window.location.href='song.php';</script>";
    } else {
        echo "<script>alert('Error creating song: " . $stmt->error . "'); window.history.back();</script>";
    }
    
    $stmt->close();
    $conn->close();
}
?>