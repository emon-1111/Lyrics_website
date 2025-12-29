<?php
include "../config/db.php";
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
}
?>

<h1>Admin Dashboard</h1>
<a href="../auth/logout.php">Logout</a>
