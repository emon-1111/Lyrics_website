<?php
include "../config/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['song_id'])) {
    $songId = intval($_POST['song_id']);
    
    $stmt = $conn->prepare("DELETE FROM songs WHERE id = ?");
    $stmt->bind_param("i", $songId);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Song deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Song not found or already deleted']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();
?>