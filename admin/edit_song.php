<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$admin_id = $_SESSION['user_id'];
$song_id  = intval($_GET['id'] ?? 0);

if (!$song_id) { header("Location: dashboard.php"); exit; }

// Admin can only edit their OWN public songs
$stmt = $conn->prepare("SELECT * FROM songs WHERE id = ? AND user_id = ? AND is_public = 1");
$stmt->bind_param("ii", $song_id, $admin_id);
$stmt->execute();
$song = $stmt->get_result()->fetch_assoc();

if (!$song) { header("Location: dashboard.php"); exit; }

$parts = json_decode($song['parts'], true) ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Song - Admin Panel</title>
  <link rel="icon" type="image/png" href="../favicon.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
  <link rel="stylesheet" href="../frontend/assets/css/user.css">
  <link rel="stylesheet" href="../frontend/assets/css/create.css">
  <style>
    .custom-alert { position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%) scale(0.7); background: var(--card); border: 1px solid var(--line); border-radius: 16px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); z-index: 10000; min-width: 400px; opacity: 0; pointer-events: none; transition: all 0.3s ease; }
    .custom-alert.show { opacity: 1; transform: translate(-50%,-50%) scale(1); pointer-events: all; }
    .alert-content { text-align: center; }
    .alert-icon { font-size: 48px; margin-bottom: 20px; }
    .alert-icon.success { color: #4ade80; }
    .alert-icon.error { color: #ff4d4d; }
    .alert-title { font-size: 24px; font-weight: 600; margin-bottom: 10px; color: var(--text); }
    .alert-message { font-size: 16px; color: var(--dim); margin-bottom: 25px; }
    .alert-btn { background: var(--bar); border: 1px solid var(--line); color: var(--text); padding: 12px 30px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500; transition: 0.2s; }
    .alert-btn:hover { background: var(--line); }
    .alert-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
    .alert-overlay.show { opacity: 1; pointer-events: all; }
    .edit-badge { display: inline-block; background: #f59e0b; color: #000; font-size: 11px; padding: 3px 8px; border-radius: 4px; font-weight: 600; margin-left: 10px; vertical-align: middle; }
    .public-note { background: rgba(74,222,128,0.1); border: 1px solid rgba(74,222,128,0.3); border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #4ade80; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
  </style>
</head>
<body>

  <nav class="navbar">
    <img src="../frontend/assets/images/transparent_logo.png" alt="Logo" class="logo">
    <div class="nav-item" data-page="dashboard.php"><i class="fa-solid fa-music icon"></i><span>Songs</span></div>
    <div class="nav-item" data-page="create_song.php"><i class="fa-solid fa-plus icon"></i><span>Create Song</span></div>
    <div class="nav-item" data-page="users.php"><i class="fa-solid fa-users icon"></i><span>Users</span></div>
    <i class="fa-solid fa-bars icon" style="cursor:pointer;"></i>
  </nav>

  <ul class="dropdown-menu" id="dropdownMenu">
    <li data-link="dashboard.php"><i class="fa-solid fa-music"></i><span>Songs</span></li>
    <li data-link="create_song.php"><i class="fa-solid fa-plus"></i><span>Create Song</span></li>
    <li data-link="users.php"><i class="fa-solid fa-users"></i><span>Users</span></li>
    <li class="logout" data-link="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></li>
  </ul>

  <div class="page-content create-page">
    <div class="create-container">
      <h1 style="font-size:28px;margin-bottom:20px;">
        Edit Song <span class="edit-badge">ADMIN EDIT</span>
      </h1>
      <div class="public-note">
        <i class="fa-solid fa-globe"></i> Changes will be saved and remain public to all users.
      </div>

      <form id="edit-form">
        <input type="hidden" id="songId" value="<?php echo $song_id; ?>">

        <label>Song Title *</label>
        <input type="text" id="songTitle" placeholder="Enter song title" value="<?php echo htmlspecialchars($song['title']); ?>" required>

        <label>Subtitle (Optional)</label>
        <input type="text" id="songSubtitle" placeholder="Artist name or album" value="<?php echo htmlspecialchars($song['subtitle'] ?? ''); ?>">

        <label>Genre (Optional)</label>
        <div class="select-wrapper">
          <select id="songGenre">
            <option value="">— Select a genre —</option>
            <?php foreach (['Pop','Rock','Hip-Hop','Classical','Jazz','Country'] as $g): ?>
              <option value="<?php echo $g; ?>" <?php echo ($song['genre'] ?? '') === $g ? 'selected' : ''; ?>><?php echo $g; ?></option>
            <?php endforeach; ?>
          </select>
          <i class="fa-solid fa-chevron-down select-arrow"></i>
        </div>

        <div class="button-group-top">
          <button type="button" id="duplicate-part"><i class="fa-solid fa-copy"></i> Duplicate Part</button>
          <button type="button" id="add-part"><i class="fa-solid fa-plus"></i> Add Part</button>
        </div>

        <div id="parts-wrapper">
          <?php if (!empty($parts)): ?>
            <?php foreach ($parts as $part): ?>
            <div class="part-container">
              <div class="part-header">
                <input type="text" class="part-label" placeholder="Verse" value="<?php echo htmlspecialchars($part['label'] ?? $part['name'] ?? ''); ?>">
                <div class="part-actions">
                  <button type="button" class="duplicate-section-btn" title="Duplicate this section"><i class="fa-solid fa-copy"></i></button>
                  <button type="button" title="Delete part"><i class="fa-solid fa-x"></i></button>
                </div>
              </div>
              <textarea class="part-textarea" placeholder="Enter lyrics here..."><?php echo htmlspecialchars($part['text'] ?? $part['lyrics'] ?? ''); ?></textarea>
            </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="part-container">
              <div class="part-header">
                <input type="text" class="part-label" placeholder="Verse" value="Verse">
                <div class="part-actions">
                  <button type="button" class="duplicate-section-btn" title="Duplicate this section"><i class="fa-solid fa-copy"></i></button>
                  <button type="button" title="Delete part"><i class="fa-solid fa-x"></i></button>
                </div>
              </div>
              <textarea class="part-textarea" placeholder="Enter lyrics here..."></textarea>
            </div>
          <?php endif; ?>
        </div>

        <div class="button-group-bottom">
          <button type="button" onclick="window.location.href='dashboard.php'"><i class="fa-solid fa-arrow-left"></i> Cancel</button>
          <button type="submit"><i class="fa-solid fa-floppy-disk"></i> Update & Push</button>
        </div>
      </form>
    </div>
  </div>

  <div class="alert-overlay" id="alertOverlay"></div>
  <div class="custom-alert" id="customAlert">
    <div class="alert-content">
      <div class="alert-icon" id="alertIcon"><i class="fa-solid fa-circle-check"></i></div>
      <div class="alert-title" id="alertTitle">Success!</div>
      <div class="alert-message" id="alertMessage"></div>
      <button class="alert-btn" onclick="closeAlert()">OK</button>
    </div>
  </div>

  <script src="../frontend/assets/js/user.js"></script>
  <script src="../frontend/assets/js/create.js"></script>
  <script>
    function showAlert(type, title, message, redirect = null) {
      const alert = document.getElementById('customAlert');
      document.getElementById('alertTitle').textContent = title;
      document.getElementById('alertMessage').textContent = message;
      const icon = document.getElementById('alertIcon');
      if (type === 'success') { icon.className = 'alert-icon success'; icon.innerHTML = '<i class="fa-solid fa-circle-check"></i>'; }
      else { icon.className = 'alert-icon error'; icon.innerHTML = '<i class="fa-solid fa-circle-xmark"></i>'; }
      document.getElementById('alertOverlay').classList.add('show');
      alert.classList.add('show');
      if (redirect) alert.dataset.redirect = redirect;
    }
    function closeAlert() {
      const alert = document.getElementById('customAlert');
      document.getElementById('alertOverlay').classList.remove('show');
      alert.classList.remove('show');
      if (alert.dataset.redirect) setTimeout(() => { window.location.href = alert.dataset.redirect; }, 300);
    }

    document.getElementById('edit-form').addEventListener('submit', async function(e) {
      e.preventDefault();
      const id       = document.getElementById('songId').value;
      const title    = document.getElementById('songTitle').value.trim();
      const subtitle = document.getElementById('songSubtitle').value.trim();
      const genre    = document.getElementById('songGenre').value.trim();
      const parts    = [];
      document.querySelectorAll('.part-container').forEach(p => {
        const label = p.querySelector('.part-label').value.trim();
        const text  = p.querySelector('.part-textarea').value.trim();
        if (label && text) parts.push({ label, text });
      });
      if (!title) { showAlert('error', 'Error', 'Please enter a song title'); return; }
      if (!parts.length) { showAlert('error', 'Error', 'Please add at least one part with lyrics'); return; }
      try {
        const res = await fetch('update_song.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ id, title, subtitle, genre, parts }) });
        const result = await res.json();
        if (result.success) showAlert('success', 'Updated!', 'Song updated and pushed to users!', 'dashboard.php');
        else showAlert('error', 'Error', result.message || 'Failed to update song');
      } catch { showAlert('error', 'Error', 'Network error. Please try again.'); }
    });
  </script>
</body>
</html>