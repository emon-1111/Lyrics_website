<?php
include "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$data        = json_decode(file_get_contents('php://input'), true);
$user_id     = $_SESSION['user_id'];
$playlist_id = intval($data['playlist_id'] ?? 0);
$name        = trim($data['name'] ?? '');

if (!$playlist_id || empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Only rename own playlists that are NOT default
$stmt = $conn->prepare("UPDATE playlists SET name=? WHERE id=? AND user_id=? AND is_default=0");
$stmt->bind_param("sii", $name, $playlist_id, $user_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Rename failed or not authorized']);
}

$stmt->close();
$conn->close();
?>