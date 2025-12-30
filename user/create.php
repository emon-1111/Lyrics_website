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
<title>Create Song - LyricScroll</title>
<link rel="stylesheet" href="../frontend/assets/css/create.css">
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

  <div class="nav-item active" data-page="create.php">
    <i class="fa-solid fa-plus icon"></i><span>Create</span>
  </div>

  <div class="nav-item" data-page="search.php">
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

<section class="page-content create-page">
  <div class="create-container">
    <div class="button-group-top">
      <button id="save-btn">Save</button>
      <button id="reset-btn">Reset</button>
    </div>

    <label>Title</label>
    <input type="text" placeholder="New Song">

    <label>Sub-Title</label>
    <input type="text" placeholder="Sub Title">

    <label>Song ID</label>
    <input type="text" placeholder="ID Automatically Inserted" disabled>

    <div id="parts-wrapper">
      <div class="part-container">
        <div class="part-header">
          <input type="text" placeholder="Verse">

          <!-- Info box trigger -->
          <span class="info-box-toggle">
            <i class="fa-solid fa-circle-info"></i>
            <div class="info-box">
              Verse: Main section<br>
              Chorus: Catchy repeated part<br>
              Bridge: Connector<br>
              Hook: Memorable phrase<br>
              Pre-Chorus: Build-up<br>
              Outro: Ending
            </div>
          </span>

          <div class="part-actions">
            <button title="Delete Part"><i class="fa-solid fa-x"></i></button>
          </div>
        </div>
        <textarea class="part-textarea" placeholder="Enter lyrics here..."></textarea>
      </div>
    </div>

    <div class="button-group-bottom">
      <button id="duplicate-part">Duplicate Part</button>
      <button id="add-part">Add Empty Part</button>
    </div>
  </div>
</section>

<script src="../frontend/assets/js/user.js"></script>
<script src="../frontend/assets/js/create.js"></script>
<script>
// Save song functionality
document.getElementById('save-btn').addEventListener('click', async () => {
  const title = document.querySelector('input[placeholder="New Song"]').value;
  const subtitle = document.querySelector('input[placeholder="Sub Title"]').value;
  
  // Collect all parts
  const parts = [];
  document.querySelectorAll('.part-container').forEach(part => {
    const name = part.querySelector('input[type="text"]').value;
    const lyrics = part.querySelector('textarea').value;
    if (name && lyrics) {
      parts.push({ name, lyrics });
    }
  });
  
  if (!title) {
    alert('Please enter a song title');
    return;
  }
  
  if (parts.length === 0) {
    alert('Please add at least one part with lyrics');
    return;
  }
  
  try {
    const response = await fetch('../user/save_song.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ title, subtitle, parts })
    });
    
    const result = await response.json();
    
    if (result.success) {
      alert(result.message);
      window.location.href = 'song.php';
    } else {
      alert(result.message);
    }
  } catch (error) {
    alert('Error saving song: ' + error.message);
  }
});
</script>
</body>
</html>