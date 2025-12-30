<?php
include "../config/db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$song_id = intval($data['id']);
$user_id = $_SESSION['user_id'];

// Delete song (only if it belongs to the user)
$stmt = $conn->prepare("DELETE FROM songs WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $song_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Song deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete song']);
}

$stmt->close();
$conn->close();
?>