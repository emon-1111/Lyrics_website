<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Search - LyricScroll</title>
<link rel="stylesheet" href="../frontend/assets/css/user.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<nav class="navbar">
  <div class="nav-item logo-container">
    <img src="../frontend/assets/images/transparent_logo.png" class="logo" alt="Logo">
  </div>

  <div class="nav-item" data-page="dashboard.php">
    <i class="fa-solid fa-home icon"></i><span>Dashboard</span>
  </div>

  <div class="nav-item" data-page="song.php">
    <i class="fa-solid fa-music icon"></i><span>Songs</span>
  </div>

  <div class="nav-item" data-page="setlist.php">
    <i class="fa-solid fa-list icon"></i><span>Setlists</span>
  </div>

  <div class="nav-item" data-page="create.php">
    <i class="fa-solid fa-plus icon"></i><span>Create</span>
  </div>

  <div class="nav-item active" data-page="search.php">
    <i class="fa-solid fa-magnifying-glass icon"></i><span>Search</span>
  </div>

  <div class="nav-item" id="menuBtn">
    <i class="fa-solid fa-bars icon"></i>
  </div>
</nav>

<ul class="dropdown-menu" id="dropdownMenu">
  <li data-link="dashboard.php"><i class="fa-solid fa-home"></i> Dashboard</li>
  <li data-link="song.php"><i class="fa-solid fa-music"></i> Songs</li>
  <li data-link="setlist.php"><i class="fa-solid fa-list"></i> Setlists</li>
  <li data-link="search.php"><i class="fa-solid fa-magnifying-glass"></i> Search</li>
  <hr>
  <li data-link="create.php"><i class="fa-solid fa-plus"></i> Create Song</li>
  <hr>
  <li class="logout" data-link="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</li>
</ul>

<div class="overlay" id="overlay"></div>

<section class="page-content dashboard">
  <div class="panel">
    <h2>Search</h2>
    <p>Find songs and setlists</p>
    <div class="btn-group">
      <button>Advanced Search</button>
      <button>Filters</button>
    </div>
  </div>
</section>

<script src="../frontend/assets/js/user.js"></script>
</body>
</html>