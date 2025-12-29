<?php
include "../config/db.php";

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pw = hash("sha256", $_POST['password']);

    $stmt = $conn->prepare("SELECT id, name, role FROM users WHERE email=? AND password=?");
    $stmt->bind_param("ss", $email, $pw);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../user/dashboard.php");
        }
        exit;
    } else {
        echo "<script>alert('Invalid email or password'); window.location.href='../index.php';</script>";
        exit;
    }
    
    $stmt->close();
    $conn->close();
}
?>