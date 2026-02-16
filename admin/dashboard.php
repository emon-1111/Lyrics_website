<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='user'")->fetch_assoc()['count'];
$totalSongs = $conn->query("SELECT COUNT(*) as count FROM songs")->fetch_assoc()['count'];

$admin_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT s.id, s.title, s.subtitle, s.is_public, s.created_at, u.name as creator_name, u.email as creator_email
    FROM songs s
    LEFT JOIN users u ON s.user_id = u.id
    WHERE s.user_id = ? AND s.is_public = 1
    ORDER BY s.created_at DESC
");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - LyricScroll</title>
  <link rel="icon" type="image/png" href="../favicon.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
  <link rel="stylesheet" href="../frontend/assets/css/user.css">
  <style>
    .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px; }
    .stat-card { background: var(--card); padding: 24px; border-radius: 16px; border: 1px solid var(--line); text-align: center; }
    .stat-number { font-size: 36px; font-weight: 700; color: var(--text); margin-bottom: 8px; }
    .stat-label { font-size: 14px; color: var(--dim); text-transform: uppercase; letter-spacing: 0.5px; }
    .songs-section { max-width: 1200px; margin: 0 auto; }
    .section-title { font-size: 24px; margin-bottom: 20px; color: var(--text); }
    .song-card { background: var(--card); border: 1px solid var(--line); border-radius: 12px; padding: 20px; margin-bottom: 15px; transition: 0.2s; }
    .song-card:hover { background: #1a1a1a; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
    .song-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
    .song-info h3 { margin: 0 0 6px 0; font-size: 20px; color: var(--text); }
    .song-info p { margin: 0; font-size: 14px; color: var(--dim); }
    .song-meta { display: flex; gap: 20px; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--line); flex-wrap: wrap; }
    .meta-item { display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--dim); }
    .song-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .action-btn { padding: 8px 16px; border-radius: 8px; border: 1px solid var(--line); background: var(--bar); color: var(--text); cursor: pointer; font-size: 13px; transition: 0.2s; display: flex; align-items: center; gap: 6px; white-space: nowrap; }
    .action-btn:hover { background: var(--line); }
    .action-btn.edit { color: #f59e0b; border-color: rgba(245,158,11,0.3); }
    .action-btn.edit:hover { background: rgba(245,158,11,0.1); }
    .action-btn.delete { background: #ff4d4d; border-color: #ff4d4d; color: #fff; }
    .action-btn.delete:hover { background: #ff3333; }
    .empty-state { text-align: center; padding: 60px 20px; color: var(--dim); }
    .empty-state i { font-size: 64px; margin-bottom: 20px; opacity: 0.3; display: block; }
    .custom-alert { position: fixed; top: 50%; left: 50%; transform: translate(-50%,-50%) scale(0.7); background: var(--card); border: 1px solid var(--line); border-radius: 16px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); z-index: 10000; min-width: 400px; opacity: 0; pointer-events: none; transition: all 0.3s ease; }
    .custom-alert.show { opacity: 1; transform: translate(-50%,-50%) scale(1); pointer-events: all; }
    .alert-content { text-align: center; }
    .alert-icon { font-size: 48px; margin-bottom: 20px; }
    .alert-icon.success { color: #4ade80; }
    .alert-icon.error, .alert-icon.warning { color: #ff4d4d; }
    .alert-title { font-size: 24px; font-weight: 600; margin-bottom: 10px; color: var(--text); }
    .alert-message { font-size: 16px; color: var(--dim); margin-bottom: 25px; }
    .alert-buttons { display: flex; gap: 10px; justify-content: center; }
    .alert-btn { background: var(--bar); border: 1px solid var(--line); color: var(--text); padding: 12px 30px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500; transition: 0.2s; }
    .alert-btn:hover { background: var(--line); }
    .alert-btn.danger { background: #ff4d4d; border-color: #ff4d4d; color: #fff; }
    .alert-btn.danger:hover { background: #ff3333; }
    .alert-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; opacity: 0; pointer-events: none; transition: opacity 0.3s ease; }
    .alert-overlay.show { opacity: 1; pointer-events: all; }
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

  <div class="page-content">
    <h1 style="font-size:32px;margin-bottom:10px;">Admin Dashboard</h1>
    <p style="color:var(--dim);margin-bottom:30px;">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>

    <div class="stats-container">
      <div class="stat-card">
        <div class="stat-number"><?php echo $totalUsers; ?></div>
        <div class="stat-label">Total Users</div>
      </div>
      <div class="stat-card">
        <div class="stat-number"><?php echo $totalSongs; ?></div>
        <div class="stat-label">Total Songs</div>
      </div>
    </div>

    <div class="songs-section">
      <h2 class="section-title">My Public Songs</h2>

      <?php if ($result->num_rows > 0): ?>
        <?php while ($song = $result->fetch_assoc()): ?>
          <div class="song-card">
            <div class="song-header">
              <div class="song-info">
                <h3>
                  <?php echo htmlspecialchars($song['title']); ?>
                  <span style="background:#4ade80;color:#000;font-size:11px;padding:2px 8px;border-radius:4px;margin-left:8px;">PUBLIC</span>
                </h3>
                <?php if ($song['subtitle']): ?>
                  <p><?php echo htmlspecialchars($song['subtitle']); ?></p>
                <?php endif; ?>
              </div>
              <div class="song-actions">
                <button class="action-btn" onclick="viewSong(<?php echo $song['id']; ?>)">
                  <i class="fa-solid fa-eye"></i> View
                </button>
                <button class="action-btn edit" onclick="editSong(<?php echo $song['id']; ?>)">
                  <i class="fa-solid fa-pen"></i> Edit
                </button>
                <button class="action-btn delete" onclick="deleteSong(<?php echo $song['id']; ?>, '<?php echo htmlspecialchars(addslashes($song['title'])); ?>')">
                  <i class="fa-solid fa-trash"></i> Delete
                </button>
              </div>
            </div>
            <div class="song-meta">
              <div class="meta-item"><i class="fa-solid fa-user"></i><span><?php echo htmlspecialchars($song['creator_name'] ?? 'Unknown'); ?></span></div>
              <div class="meta-item"><i class="fa-solid fa-envelope"></i><span><?php echo htmlspecialchars($song['creator_email'] ?? 'N/A'); ?></span></div>
              <div class="meta-item"><i class="fa-solid fa-calendar"></i><span><?php echo date('M d, Y', strtotime($song['created_at'])); ?></span></div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="empty-state">
          <i class="fa-solid fa-music"></i>
          <h2>No Songs Yet</h2>
          <p>Create a song to push it to users.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="alert-overlay" id="alertOverlay"></div>
  <div class="custom-alert" id="customAlert">
    <div class="alert-content">
      <div class="alert-icon" id="alertIcon"><i class="fa-solid fa-circle-check"></i></div>
      <div class="alert-title" id="alertTitle">Success!</div>
      <div class="alert-message" id="alertMessage">Action completed!</div>
      <div class="alert-buttons" id="alertButtons">
        <button class="alert-btn" onclick="closeAlert()">OK</button>
      </div>
    </div>
  </div>

  <script src="../frontend/assets/js/user.js"></script>
  <script>
    let pendingSongId = null;
    let pendingSongTitle = null;

    function viewSong(songId) {
      window.location.href = `view_song.php?id=${songId}`;
    }

    function editSong(songId) {
      window.location.href = `edit_song.php?id=${songId}`;
    }

    function showAlert(type, title, message, buttons = null) {
      const alertBox = document.getElementById('customAlert');
      const overlay = document.getElementById('alertOverlay');
      const icon = document.getElementById('alertIcon');
      document.getElementById('alertTitle').textContent = title;
      document.getElementById('alertMessage').textContent = message;
      if (type === 'success') { icon.className = 'alert-icon success'; icon.innerHTML = '<i class="fa-solid fa-circle-check"></i>'; }
      else if (type === 'warning') { icon.className = 'alert-icon warning'; icon.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>'; }
      else { icon.className = 'alert-icon error'; icon.innerHTML = '<i class="fa-solid fa-circle-xmark"></i>'; }
      document.getElementById('alertButtons').innerHTML = buttons || '<button class="alert-btn" onclick="closeAlert()">OK</button>';
      overlay.classList.add('show');
      alertBox.classList.add('show');
    }

    function closeAlert() {
      document.getElementById('customAlert').classList.remove('show');
      document.getElementById('alertOverlay').classList.remove('show');
      pendingSongId = null;
      pendingSongTitle = null;
    }

    function deleteSong(songId, songTitle) {
      pendingSongId = songId;
      pendingSongTitle = songTitle;
      showAlert('warning', 'Confirm Delete',
        `Are you sure you want to delete "${songTitle}"? This cannot be undone.`,
        `<button class="alert-btn" onclick="closeAlert()">Cancel</button>
         <button class="alert-btn danger" onclick="confirmDelete()">Delete</button>`
      );
    }

    async function confirmDelete() {
      if (!pendingSongId) return;
      try {
        const res = await fetch('delete_song.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `song_id=${pendingSongId}`
        });
        const data = await res.json();
        if (data.success) {
          showAlert('success', 'Deleted!', 'Song deleted successfully!');
          setTimeout(() => location.reload(), 1500);
        } else {
          showAlert('error', 'Error', data.message || 'Failed to delete song');
        }
      } catch {
        showAlert('error', 'Error', 'Network error. Please try again.');
      }
    }
  </script>
</body>
</html>