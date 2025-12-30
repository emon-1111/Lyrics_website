<?php
session_start(); // Add this to start sessions

$host = "localhost";
$user = "root";
$pass = "";
$db   = "auth_system";
$port = 3307; // Your XAMPP MySQL port

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>