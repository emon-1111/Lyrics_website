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
  <title>Create Song - With Audio</title>
  <link rel="icon" type="image/png" href="../favicon.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
  <link rel="stylesheet" href="../frontend/assets/css/user.css">
  <link rel="stylesheet" href="../frontend/assets/css/create.css">
  <style>
    /* Audio File Upload Styles */
    .audio-upload-section {
      background: rgba(74, 222, 128, 0.05);
      border: 2px solid rgba(74, 222, 128, 0.2);
      border-radius: 12px;
      padding: 20px;
      margin: 20px 0;
    }
    
    .audio-upload-section h3 {
      color: #4ade80;
      margin: 0 0 15px 0;
      font-size: 18px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .file-upload-wrapper {
      margin-bottom: 15px;
    }

    .file-upload-wrapper label {
      display: block;
      margin-bottom: 8px;
      color: var(--text);
      font-size: 14px;
    }

    .file-input-container {
      position: relative;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .file-input-container input[type="file"] {
      flex: 1;
      padding: 12px;
      background: rgba(255, 255, 255, 0.05);
      border: 2px dashed rgba(255, 255, 255, 0.2);
      border-radius: 8px;
      color: var(--text);
      cursor: pointer;
      transition: all 0.3s;
    }

    .file-input-container input[type="file"]:hover {
      border-color: #4ade80;
      background: rgba(74, 222, 128, 0.1);
    }

    .file-hint {
      font-size: 12px;
      color: var(--dim);
      margin-top: 5px;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .helper-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: #4ade80;
      text-decoration: none;
      font-size: 13px;
      margin-top: 8px;
      transition: all 0.2s;
    }

    .helper-link:hover {
      text-decoration: underline;
    }

    .info-note {
      background: rgba(102, 126, 234, 0.1);
      border-left: 4px solid #667eea;
      padding: 12px 15px;
      border-radius: 6px;
      margin-bottom: 20px;
      font-size: 13px;
      color: var(--dim);
      line-height: 1.5;
    }

    .info-note strong {
      color: #667eea;
    }

    .premium-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(74, 222, 128, 0.1);
      color: #4ade80;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      margin-left: 10px;
    }

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
    
    <div class="nav-item" data-page="create_song_choice.php">
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
    <li data-link="create_song_choice.php">
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
      <h1 style="font-size: 28px; margin-bottom: 20px;">
        Create New Song (With Audio)
        <span class="premium-badge">
          <i class="fa-solid fa-star"></i> PREMIUM
        </span>
      </h1>
      
      <form id="song-form" method="POST" action="save_song.php" enctype="multipart/form-data">
        
        <label>Song Title *</label>
        <input type="text" name="title" placeholder="Enter song title" required>
        
        <label>Subtitle (Optional)</label>
        <input type="text" name="subtitle" placeholder="Artist name or album">

        <!-- Audio Upload Section -->
        <div class="audio-upload-section">
          <h3>
            <i class="fa-solid fa-headphones"></i> Audio Files
          </h3>

          <div class="info-note">
            <strong>Note:</strong> Both MP3 and LRC files are required. 
            Get LRC files from <a href="https://megalobiz.com/search/all" target="_blank" style="color: #4ade80;">Megalobiz</a> 
            or <a href="https://lrc-maker.github.io/" target="_blank" style="color: #4ade80;">create your own</a>.
          </div>

          <div class="file-upload-wrapper">
            <label><strong>MP3 File *</strong></label>
            <div class="file-input-container">
              <input type="file" name="audio_file" accept=".mp3" required>
            </div>
            <p class="file-hint">
              <i class="fa-solid fa-circle-info"></i> Upload song audio (MP3 format, max 10MB)
            </p>
          </div>

          <div class="file-upload-wrapper">
            <label><strong>LRC File (Synced Lyrics) *</strong></label>
            <div class="file-input-container">
              <input type="file" name="lrc_file" accept=".lrc" required>
            </div>
            <p class="file-hint">
              <i class="fa-solid fa-circle-info"></i> Upload synchronized lyrics (.lrc format)
            </p>
            <a href="https://lrc-maker.github.io/" target="_blank" class="helper-link">
              <i class="fa-solid fa-external-link"></i> Create LRC file online
            </a>
          </div>
        </div>

        <!-- Lyrics Section (Reference) -->
        <label>Lyrics (Reference) *</label>
        <p style="font-size: 13px; color: var(--dim); margin: -5px 0 10px 0;">
          These lyrics are for backup/reference. The LRC file will be used for synced display.
        </p>
        
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
            <textarea class="part-textarea" placeholder="Enter lyrics here (for reference)..."></textarea>
          </div>
        </div>

        <!-- Hidden field to mark as audio song -->
        <input type="hidden" name="has_audio" value="1">
        
        <div class="button-group-bottom">
          <button type="button" id="reset-btn">
            <i class="fa-solid fa-rotate-left"></i> Reset
          </button>
          <button type="submit">
            <i class="fa-solid fa-save"></i> Save Song with Audio
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

      alertTitle.textContent = title;
      alertMessage.textContent = message;

      if (type === 'success') {
        icon.className = 'alert-icon success';
        icon.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
      } else {
        icon.className = 'alert-icon error';
        icon.innerHTML = '<i class="fa-solid fa-circle-xmark"></i>';
      }

      overlay.classList.add('show');
      alert.classList.add('show');

      if (redirect) {
        alert.dataset.redirect = redirect;
      }
    }

    function closeAlert() {
      const alert = document.getElementById('customAlert');
      const overlay = document.getElementById('alertOverlay');
      
      overlay.classList.remove('show');
      alert.classList.remove('show');

      if (alert.dataset.redirect) {
        setTimeout(() => {
          window.location.href = alert.dataset.redirect;
        }, 300);
      }
    }

    // File validation
    const audioFile = document.querySelector('input[name="audio_file"]');
    const lrcFile = document.querySelector('input[name="lrc_file"]');

    audioFile.addEventListener('change', function() {
      if (this.files.length > 0) {
        const fileSize = this.files[0].size / 1024 / 1024; // MB
        if (fileSize > 10) {
          showAlert('error', 'Error', 'MP3 file size should not exceed 10MB');
          this.value = '';
        }
      }
    });

    // Override form submission
    document.getElementById('song-form').addEventListener('submit', function(e) {
      e.preventDefault();

      // Validate files
      if (!audioFile.files.length) {
        showAlert('error', 'Error', 'Please upload an MP3 file');
        return;
      }
      if (!lrcFile.files.length) {
        showAlert('error', 'Error', 'Please upload an LRC file');
        return;
      }

      // Collect parts
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

      // Submit
      fetch('save_song.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        if (data.includes('success')) {
          showAlert('success', 'Success!', 'Song with audio created successfully!', 'dashboard.php');
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