<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Song - Admin Panel</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
  <link rel="stylesheet" href="../frontend/assets/css/user.css">
  <link rel="stylesheet" href="../frontend/assets/css/create.css">
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar">
    <img src="../frontend/assets/images/transparent_logo.png" alt="Logo" class="logo">
    
    <div class="nav-item" data-page="dashboard.php">
      <i class="fa-solid fa-music icon"></i>
      <span>Songs</span>
    </div>
    
    <div class="nav-item" data-page="create_song.php">
      <i class="fa-solid fa-plus icon"></i>
      <span>Create Song</span>
    </div>
    
    <div class="nav-item" data-page="users.php">
      <i class="fa-solid fa-users icon"></i>
      <span>Users</span>
    </div>
    
    <i class="fa-solid fa-bars icon" style="cursor: pointer;"></i>
  </nav>

  <!-- Dropdown Menu -->
  <ul class="dropdown-menu" id="dropdownMenu">
    <li data-link="dashboard.php">
      <i class="fa-solid fa-music"></i>
      <span>Songs</span>
    </li>
    <li data-link="create_song.php">
      <i class="fa-solid fa-plus"></i>
      <span>Create Song</span>
    </li>
    <li data-link="users.php">
      <i class="fa-solid fa-users"></i>
      <span>Users</span>
    </li>
    <li class="logout" data-link="../auth/logout.php">
      <i class="fa-solid fa-right-from-bracket"></i>
      <span>Logout</span>
    </li>
  </ul>

  <!-- Page Content -->
  <div class="page-content create-page">
    <div class="create-container">
      <h1 style="font-size: 28px; margin-bottom: 20px;">Create New Song</h1>
      
      <form id="song-form" method="POST" action="save_song.php">
        
        <label>Song Title *</label>
        <input type="text" name="title" placeholder="Enter song title" required>
        
        <label>Subtitle (Optional)</label>
        <input type="text" name="subtitle" placeholder="Artist name or album">
        
        <div class="button-group-top">
          <button type="button" id="duplicate-part">
            <i class="fa-solid fa-copy"></i> Duplicate Part
          </button>
          <button type="button" id="add-part">
            <i class="fa-solid fa-plus"></i> Add Part
          </button>
        </div>
        
        <div id="parts-wrapper">
          <div class="part-container">
            <div class="part-header">
              <input type="text" class="part-label" placeholder="Verse" value="Verse">
              <div class="part-actions">
                <button type="button" title="Delete part">
                  <i class="fa-solid fa-x"></i>
                </button>
              </div>
            </div>
            <textarea class="part-textarea" placeholder="Enter lyrics here..."></textarea>
          </div>
        </div>
        
        <div class="button-group-bottom">
          <button type="button" id="reset-btn">
            <i class="fa-solid fa-rotate-left"></i> Reset
          </button>
          <button type="submit">
            <i class="fa-solid fa-save"></i> Save Song
          </button>
        </div>
        
      </form>
    </div>
  </div>

  <script src="../frontend/assets/js/user.js"></script>
  <script src="../frontend/assets/js/create.js"></script>
  <script>
    // Override form submission to collect parts data
    document.getElementById('song-form').addEventListener('submit', function(e) {
      const parts = [];
      document.querySelectorAll('.part-container').forEach(part => {
        const label = part.querySelector('.part-label').value.trim();
        const text = part.querySelector('.part-textarea').value.trim();
        if (label && text) {
          parts.push({ label, text });
        }
      });
      
      // Add parts as hidden input
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'parts';
      input.value = JSON.stringify(parts);
      this.appendChild(input);
    });
  </script>
</body>
</html>