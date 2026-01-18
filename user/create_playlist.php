<?php
include "../config/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$name = trim($data['name'] ?? '');

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Playlist name is required']);
    exit;
}

if (strlen($name) > 50) {
    echo json_encode(['success' => false, 'message' => 'Playlist name is too long (max 50 characters)']);
    exit;
}

// Check if playlist name already exists for this user
$stmt = $conn->prepare("SELECT id FROM playlists WHERE user_id = ? AND name = ?");
$stmt->bind_param("is", $user_id, $name);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'A playlist with this name already exists']);
    exit;
}

// Create new playlist
$stmt = $conn->prepare("INSERT INTO playlists (user_id, name, is_default) VALUES (?, ?, 0)");
$stmt->bind_param("is", $user_id, $name);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'playlist_id' => $stmt->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create playlist']);
}

$stmt->close();
$conn->close();
?>