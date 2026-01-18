<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit;
}

$playlist_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// Fetch playlist details
$stmt = $conn->prepare("SELECT * FROM playlists WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $playlist_id, $user_id);
$stmt->execute();
$playlist_result = $stmt->get_result();

if ($playlist_result->num_rows === 0) {
    echo "<script>alert('Playlist not found'); window.location.href='setlist.php';</script>";
    exit;
}

$playlist = $playlist_result->fetch_assoc();

// Fetch songs in this playlist
$stmt = $conn->prepare("
    SELECT s.*, ps.added_at, u.name as creator_name
    FROM playlist_songs ps
    JOIN songs s ON ps.song_id = s.id
    LEFT JOIN users u ON s.user_id = u.id
    WHERE ps.playlist_id = ?
    ORDER BY ps.added_at DESC
");
$stmt->bind_param("i", $playlist_id);
$stmt->execute();
$songs_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($playlist['name']); ?> - LyricScroll</title>
<link rel="stylesheet" href="../frontend/assets/css/user.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.playlist-header {
  max-width: 1200px;
  margin: 20px auto;
  padding: 40px 20px;
  background: linear-gradient(135deg, var(--card) 0%, rgba(74, 222, 128, 0.1) 100%);
  border-radius: 16px;
  border: 1px solid var(--line);
  display: flex;
  align-items: center;
  gap: 30px;
}

.playlist-icon-large {
  width: 120px;
  height: 120px;
  background: linear-gradient(135deg, #4ade80, #22c55e);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  color: #000;
  flex-shrink: 0;
}

.playlist-details h1 {
  font-size: 42px;
  margin: 0 0 10px 0;
  color: var(--text);
}

.playlist-meta {
  display: flex;
  gap: 20px;
  font-size: 14px;
  color: var(--dim);
}

.back-to-playlists {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: var(--bar);
  border: 1px solid var(--line);
  border-radius: 8px;
  color: var(--text);
  text-decoration: none;
  transition: 0.2s;
  margin: 20px auto;
  max-width: 1200px;
  display: block;
  width: fit-content;
}

.back-to-playlists:hover {
  background: var(--line);
}

.songs-container {
  max-width: 1200px;
  margin: 20px auto;
  padding: 0 20px;
}

.song-item {
  background: var(--card);
  padding: 20px;
  margin-bottom: 10px;
  border-radius: 8px;
  border: 1px solid var(--line);
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: 0.2s;
}

.song-item:hover {
  border-color: var(--dim);
  transform: translateX(4px);
}

.song-info {
  flex: 1;
}

.song-title {
  font-size: 18px;
  margin: 0 0 5px 0;
  color: var(--text);
  display: flex;
  align-items: center;
  gap: 10px;
}

.badge {
  font-size: 11px;
  padding: 3px 8px;
  border-radius: 4px;
  font-weight: 600;
}

.public-badge {
  background: #4ade80;
  color: #000;
}

.song-subtitle {
  font-size: 14px;
  color: var(--dim);
  margin: 0;
}

.song-actions {
  display: flex;
  gap: 10px;
}

.action-btn {
  padding: 8px 16px;
  border-radius: 6px;
  border: 1px solid var(--line);
  background: var(--bar);
  color: var(--text);
  cursor: pointer;
  font-size: 14px;
  transition: 0.2s;
}

.action-btn:hover {
  background: var(--line);
}

.remove-btn {
  color: #ff4d4d;
}

.empty-state {
  text-align: center;
  padding: 80px 20px;
  color: var(--dim);
}

.empty-state i {
  font-size: 64px;
  margin-bottom: 20px;
  opacity: 0.3;
}

/* Alert styles */
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
.alert-icon.success { color: #4ade80; }
.alert-icon.error, .alert-icon.warning { color: #ff4d4d; }
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
.alert-buttons {
  display: flex;
  gap: 10px;
  justify-content: center;
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
.alert-btn:hover { background: var(--line); }
.alert-btn.danger {
  background: #ff4d4d;
  border-color: #ff4d4d;
}
.alert-btn.danger:hover { background: #ff3333; }
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
  <div class="nav-item active" data-page="setlist.php">
    <i class="fa-solid fa-list icon"></i><span>Playlists</span>
  </div>
  <div class="nav-item" data-page="create.php">
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
  <li data-link="setlist.php"><i class="fa-solid fa-list"></i> Playlists</li>
  <li data-link="search.php"><i class="fa-solid fa-magnifying-glass"></i> Search</li>
  <hr>
  <li data-link="create.php"><i class="fa-solid fa-plus"></i> Create Song</li>
  <hr>
  <li class="logout" data-link="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</li>
</ul>

<div class="overlay" id="overlay"></div>

<section class="page-content">
  <a href="setlist.php" class="back-to-playlists">
    <i class="fa-solid fa-arrow-left"></i> Back to Playlists
  </a>

  <div class="playlist-header">
    <div class="playlist-icon-large">
      <i class="fa-solid fa-<?php echo $playlist['is_default'] ? 'heart' : 'list-music'; ?>"></i>
    </div>
    <div class="playlist-details">
      <h1><?php echo htmlspecialchars($playlist['name']); ?></h1>
      <div class="playlist-meta">
        <span><i class="fa-solid fa-music"></i> <?php echo $songs_result->num_rows; ?> songs</span>
        <span><i class="fa-solid fa-calendar"></i> Created <?php echo date('M d, Y', strtotime($playlist['created_at'])); ?></span>
      </div>
    </div>
  </div>

  <div class="songs-container">
    <?php if ($songs_result->num_rows > 0): ?>
      <?php while ($song = $songs_result->fetch_assoc()): ?>
        <div class="song-item">
          <div class="song-info">
            <h3 class="song-title">
              <?php echo htmlspecialchars($song['title']); ?>
              <?php if ($song['is_public'] == 1): ?>
                <span class="badge public-badge">PUBLIC</span>
              <?php endif; ?>
            </h3>
            <p class="song-subtitle">
              <?php echo htmlspecialchars($song['subtitle']); ?>
              <?php if ($song['user_id'] != $user_id): ?>
                <span style="color: #4ade80;"> • by <?php echo htmlspecialchars($song['creator_name']); ?></span>
              <?php endif; ?>
            </p>
          </div>
          <div class="song-actions">
            <button class="action-btn" onclick="window.location.href='view_song.php?id=<?php echo $song['id']; ?>'">
              <i class="fa-solid fa-eye"></i> View
            </button>
            <button class="action-btn remove-btn" onclick="removeSong(<?php echo $song['id']; ?>, '<?php echo htmlspecialchars(addslashes($song['title'])); ?>')">
              <i class="fa-solid fa-trash"></i> Remove
            </button>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="empty-state">
        <i class="fa-solid fa-music"></i>
        <h2>No Songs Yet</h2>
        <p>Add songs to this playlist from your songs page</p>
        <button class="action-btn" onclick="window.location.href='song.php'" style="margin-top: 20px; padding: 12px 24px;">
          <i class="fa-solid fa-plus"></i> Go to Songs
        </button>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Alert Box -->
<div class="alert-overlay" id="alertOverlay"></div>
<div class="custom-alert" id="customAlert">
  <div class="alert-content">
    <div class="alert-icon" id="alertIcon">
      <i class="fa-solid fa-circle-check"></i>
    </div>
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

function showAlert(type, title, message, buttons = null) {
  const alert = document.getElementById('customAlert');
  const overlay = document.getElementById('alertOverlay');
  const icon = document.getElementById('alertIcon');
  const alertTitle = document.getElementById('alertTitle');
  const alertMessage = document.getElementById('alertMessage');
  const alertButtons = document.getElementById('alertButtons');

  alertTitle.textContent = title;
  alertMessage.textContent = message;

  if (type === 'success') {
    icon.className = 'alert-icon success';
    icon.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
  } else if (type === 'warning') {
    icon.className = 'alert-icon warning';
    icon.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
  } else {
    icon.className = 'alert-icon error';
    icon.innerHTML = '<i class="fa-solid fa-circle-xmark"></i>';
  }

  if (buttons) {
    alertButtons.innerHTML = buttons;
  } else {
    alertButtons.innerHTML = '<button class="alert-btn" onclick="closeAlert()">OK</button>';
  }

  overlay.classList.add('show');
  alert.classList.add('show');
}

function closeAlert() {
  const alert = document.getElementById('customAlert');
  const overlay = document.getElementById('alertOverlay');
  
  overlay.classList.remove('show');
  alert.classList.remove('show');
  pendingSongId = null;
}

function removeSong(songId, songTitle) {
  pendingSongId = songId;
  showAlert(
    'warning',
    'Remove from Playlist',
    `Remove "${songTitle}" from this playlist?`,
    `
      <button class="alert-btn" onclick="closeAlert()">Cancel</button>
      <button class="alert-btn danger" onclick="confirmRemove()">Remove</button>
    `
  );
}

async function confirmRemove() {
  if (!pendingSongId) return;

  try {
    const response = await fetch('remove_from_playlist.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ 
        playlist_id: <?php echo $playlist_id; ?>,
        song_id: pendingSongId 
      })
    });

    const result = await response.json();

    if (result.success) {
      showAlert('success', 'Success!', 'Song removed from playlist');
      setTimeout(() => location.reload(), 1500);
    } else {
      showAlert('error', 'Error', result.message || 'Failed to remove song');
    }
  } catch (error) {
    showAlert('error', 'Error', 'Network error. Please try again.');
  }
}
</script>
</body>
</html>