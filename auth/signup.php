<?php
include "../config/db.php";

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pw = hash("sha256", $_POST['password']);
    $genres = isset($_POST['genres']) ? $_POST['genres'] : [];

    // Validate genre selection
    if (empty($genres)) {
        header("Location: ../index.php?error=" . urlencode("Please select at least one music genre."));
        exit;
    }

    // Check duplicate email
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        header("Location: ../index.php?error=" . urlencode("Email already exists"));
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Convert genres array to JSON string for storage
        $genresJson = json_encode($genres);
        
        // Insert new user with default role 'user' and genres
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, favorite_genres) VALUES (?, ?, ?, 'user', ?)");
        $stmt->bind_param("ssss", $name, $email, $pw, $genresJson);
        $stmt->execute();
        
        // Get the new user's ID
        $new_user_id = $stmt->insert_id;
        
        // Create default "Favorites" playlist for the new user
        $stmt = $conn->prepare("INSERT INTO playlists (user_id, name, is_default) VALUES (?, 'Favorites', 1)");
        $stmt->bind_param("i", $new_user_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        header("Location: ../index.php?success=" . urlencode("Account created successfully! Please login."));
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        header("Location: ../index.php?error=" . urlencode("Error creating account: " . $e->getMessage()));
    }
    
    $stmt->close();
    $conn->close();
    exit;
}
?>