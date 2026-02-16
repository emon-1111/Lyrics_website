<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data     = json_decode(file_get_contents('php://input'), true);
$admin_id = $_SESSION['user_id'];
$song_id  = intval($data['id'] ?? 0);
$title    = trim($data['title'] ?? '');
$subtitle = trim($data['subtitle'] ?? '');
$genre    = trim($data['genre'] ?? '');
$parts    = json_encode($data['parts'] ?? []);

if (!$song_id || empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Only edit admin's OWN public songs — cannot touch user songs
$stmt = $conn->prepare("UPDATE songs SET title=?, subtitle=?, genre=?, parts=?, is_public=1 WHERE id=? AND user_id=? AND is_public=1");
$stmt->bind_param("ssssii", $title, $subtitle, $genre, $parts, $song_id, $admin_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed or not authorized']);
}

$stmt->close();
$conn->close();
?>