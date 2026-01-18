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

if ($playlist_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid playlist ID']);
    exit;
}

// Check if playlist exists and belongs to user, and is not default
$stmt = $conn->prepare("SELECT is_default FROM playlists WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $playlist_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Playlist not found']);
    exit;
}

$playlist = $result->fetch_assoc();
if ($playlist['is_default']) {
    echo json_encode(['success' => false, 'message' => 'Cannot delete default playlist']);
    exit;
}

// Delete playlist (cascade will delete playlist_songs)
$stmt = $conn->prepare("DELETE FROM playlists WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $playlist_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete playlist']);
}

$stmt->close();
$conn->close();
?>