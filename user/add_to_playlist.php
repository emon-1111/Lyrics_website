<?php
include "../config/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$playlist_id = intval($data['playlist_id'] ?? 0);
$song_id = intval($data['song_id'] ?? 0);

if ($playlist_id <= 0 || $song_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid playlist or song ID']);
    exit;
}

// Verify playlist belongs to user
$stmt = $conn->prepare("SELECT id FROM playlists WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $playlist_id, $user_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Playlist not found']);
    exit;
}

// Check if song already in playlist
$stmt = $conn->prepare("SELECT id FROM playlist_songs WHERE playlist_id = ? AND song_id = ?");
$stmt->bind_param("ii", $playlist_id, $song_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Song already in this playlist']);
    exit;
}

// Add song to playlist
$stmt = $conn->prepare("INSERT INTO playlist_songs (playlist_id, song_id) VALUES (?, ?)");
$stmt->bind_param("ii", $playlist_id, $song_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add song to playlist']);
}

$stmt->close();
$conn->close();
?>