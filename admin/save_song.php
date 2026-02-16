<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "error";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title']);
    $subtitle = trim($_POST['subtitle']) ?: null;
    $genre    = trim($_POST['genre']) ?: null;
    $parts    = $_POST['parts'];
    $userId   = $_SESSION['user_id'];

    if (empty($title) || empty($parts)) {
        echo "error";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO songs (user_id, title, subtitle, genre, is_public, parts) VALUES (?, ?, ?, ?, 1, ?)");
    $stmt->bind_param("issss", $userId, $title, $subtitle, $genre, $parts);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
?>