<?php
include "../config/db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$user_id = $_SESSION['user_id'];
$title = trim($data['title']);
$subtitle = trim($data['subtitle']);
$parts = json_encode($data['parts']); // Store parts as JSON

// Validate
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

// Insert into database
$stmt = $conn->prepare("INSERT INTO songs (user_id, title, subtitle, parts) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $user_id, $title, $subtitle, $parts);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Song saved successfully!', 'song_id' => $stmt->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save song']);
}

$stmt->close();
$conn->close();
?>