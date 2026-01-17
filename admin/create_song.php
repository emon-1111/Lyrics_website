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
  <style>
    /* Custom Alert Box */
    .custom-alert {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) scale(0.7);
      background: var(--card);
      border: 1px solid var(--line);
      border-radius: 16px;
      padding: 30px;
      box-shadow: 0 10px 40px rgba(0,0,0,0.5);
      z-index: 10000;
      min-width: 400px;
      opacity: 0;
      pointer-events: none;
      transition: all 0.3s ease;
    }
    .custom-alert.show {
      opacity: 1;
      transform: translate(-50%, -50%) scale(1);
      pointer-events: all;
    }
    .alert-content {
      text-align: center;
    }
    .alert-icon {
      font-size: 48px;
      margin-bottom: 20px;
    }
    .alert-icon.success {
      color: #4ade80;
    }
    .alert-icon.error {
      color: #ff4d4d;
    }
    .alert-title {
      font-size: 24px;
      font-weight: 600;
      margin-bottom: 10px;
      color: var(--text);
    }
    .alert-message {
      font-size: 16px;
      color: var(--dim);
      margin-bottom: 25px;
    }
    .alert-btn {
      background: var(--bar);
      border: 1px solid var(--line);
      color: var(--text);
      padding: 12px 30px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      transition: 0.2s;
    }
    .alert-btn:hover {
      background: var(--line);
    }
    .alert-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.7);
      z-index: 9999;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s ease;
    }
    .alert-overlay.show {
      opacity: 1;
      pointer-events: all;
    }
  </style>
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

  <!-- Custom Alert Box -->
  <div class="alert-overlay" id="alertOverlay"></div>
  <div class="custom-alert" id="customAlert">
    <div class="alert-content">
      <div class="alert-icon" id="alertIcon">
        <i class="fa-solid fa-circle-check"></i>
      </div>
      <div class="alert-title" id="alertTitle">Success!</div>
      <div class="alert-message" id="alertMessage">Song created successfully!</div>
      <button class="alert-btn" onclick="closeAlert()">OK</button>
    </div>
  </div>

  <script src="../frontend/assets/js/user.js"></script>
  <script src="../frontend/assets/js/create.js"></script>
  <script>
    function showAlert(type, title, message, redirect = null) {
      const alert = document.getElementById('customAlert');
      const overlay = document.getElementById('alertOverlay');
      const icon = document.getElementById('alertIcon');
      const alertTitle = document.getElementById('alertTitle');
      const alertMessage = document.getElementById('alertMessage');

      // Set content
      alertTitle.textContent = title;
      alertMessage.textContent = message;

      // Set icon
      if (type === 'success') {
        icon.className = 'alert-icon success';
        icon.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
      } else {
        icon.className = 'alert-icon error';
        icon.innerHTML = '<i class="fa-solid fa-circle-xmark"></i>';
      }

      // Show alert
      overlay.classList.add('show');
      alert.classList.add('show');

      // Store redirect URL if provided
      if (redirect) {
        alert.dataset.redirect = redirect;
      }
    }

    function closeAlert() {
      const alert = document.getElementById('customAlert');
      const overlay = document.getElementById('alertOverlay');
      
      overlay.classList.remove('show');
      alert.classList.remove('show');

      // Redirect if URL is stored
      if (alert.dataset.redirect) {
        setTimeout(() => {
          window.location.href = alert.dataset.redirect;
        }, 300);
      }
    }

    // Override form submission to collect parts data
    document.getElementById('song-form').addEventListener('submit', function(e) {
      e.preventDefault();

      const parts = [];
      document.querySelectorAll('.part-container').forEach(part => {
        const label = part.querySelector('.part-label').value.trim();
        const text = part.querySelector('.part-textarea').value.trim();
        if (label && text) {
          parts.push({ label, text });
        }
      });

      if (parts.length === 0) {
        showAlert('error', 'Error', 'Please add at least one song part with lyrics');
        return;
      }

      // Create FormData
      const formData = new FormData(this);
      formData.append('parts', JSON.stringify(parts));

      // Submit via fetch
      fetch('save_song.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        // Check if success
        if (data.includes('success')) {
          showAlert('success', 'Success!', 'Song created successfully and is now public!', 'dashboard.php');
        } else {
          showAlert('error', 'Error', 'Failed to create song. Please try again.');
        }
      })
      .catch(error => {
        showAlert('error', 'Error', 'Network error. Please try again.');
      });
    });
  </script>
</body>
</html>