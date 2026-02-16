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

// Verify song belongs to this admin and is public
$check = $conn->prepare("SELECT id FROM songs WHERE id = ? AND user_id = ? AND is_public = 1");
$check->bind_param("ii", $song_id, $admin_id);
$check->execute();
$exists = $check->get_result()->fetch_assoc();

if (!$exists) {
    echo json_encode(['success' => false, 'message' => 'Song not found or not authorized']);
    exit;
}

// Update — no affected_rows check, execute() success is enough
$stmt = $conn->prepare("UPDATE songs SET title=?, subtitle=?, genre=?, parts=?, is_public=1 WHERE id=? AND user_id=?");
$stmt->bind_param("ssssii", $title, $subtitle, $genre, $parts, $song_id, $admin_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>