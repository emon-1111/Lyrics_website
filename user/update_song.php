<?php
include "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$data     = json_decode(file_get_contents('php://input'), true);
$user_id  = $_SESSION['user_id'];
$song_id  = intval($data['id'] ?? 0);
$title    = trim($data['title'] ?? '');
$subtitle = trim($data['subtitle'] ?? '');
$genre    = trim($data['genre'] ?? '');
$parts    = json_encode($data['parts'] ?? []);

if (!$song_id || empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Only update OWN songs that are NOT public (admin songs are public = 1)
$stmt = $conn->prepare("UPDATE songs SET title=?, subtitle=?, genre=?, parts=? WHERE id=? AND user_id=? AND is_public=0");
$stmt->bind_param("ssssii", $title, $subtitle, $genre, $parts, $song_id, $user_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed or not authorized']);
}

$stmt->close();
$conn->close();
?>