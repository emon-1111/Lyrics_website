<?php
include "../config/db.php";

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pw = hash("sha256", $_POST['password']);

    // Check duplicate email
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<script>alert('Email already exists'); window.location.href='../index.php';</script>";
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert new user with default role 'user'
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->bind_param("sss", $name, $email, $pw);
        $stmt->execute();
        
        // Get the new user's ID
        $new_user_id = $stmt->insert_id;
        
        // Create default "Favorites" playlist for the new user
        $stmt = $conn->prepare("INSERT INTO playlists (user_id, name, is_default) VALUES (?, 'Favorites', 1)");
        $stmt->bind_param("i", $new_user_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        echo "<script>alert('Account created successfully! Please login.'); window.location.href='../index.php';</script>";
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo "<script>alert('Error creating account: " . $e->getMessage() . "'); window.location.href='../index.php';</script>";
    }
    
    $stmt->close();
    $conn->close();
    exit;
}
?>