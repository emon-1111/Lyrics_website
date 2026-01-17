<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $subtitle = trim($_POST['subtitle']) ?: null;
    $parts = $_POST['parts'];
    $userId = $_SESSION['user_id'];
    
    if (empty($title) || empty($parts)) {
        echo "<script>alert('Title and song parts are required'); window.history.back();</script>";
        exit;
    }
    
    // Admin songs are PUBLIC (is_public = 1)
    $stmt = $conn->prepare("INSERT INTO songs (user_id, title, subtitle, is_public, parts) VALUES (?, ?, ?, 1, ?)");
    $stmt->bind_param("isss", $userId, $title, $subtitle, $parts);
    
    if ($stmt->execute()) {
        echo "<script>alert('Song created successfully and is now public!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Error creating song: " . $conn->error . "'); window.history.back();</script>";
    }
    
    $stmt->close();
    $conn->close();
}
?>