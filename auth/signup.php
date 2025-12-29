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

    // Insert new user with default role 'user'
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
    $stmt->bind_param("sss", $name, $email, $pw);
    
    if ($stmt->execute()) {
        echo "<script>alert('Account created successfully! Please login.'); window.location.href='../index.php';</script>";
    } else {
        echo "<script>alert('Error creating account: " . $conn->error . "'); window.location.href='../index.php';</script>";
    }
    
    $stmt->close();
    $conn->close();
    exit;
}
?>