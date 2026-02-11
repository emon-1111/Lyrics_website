<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all playlists for this user
$stmt = $conn->prepare("
    SELECT p.*, COUNT(ps.id) as song_count
    FROM playlists p
    LEFT JOIN playlist_songs ps ON p.id = ps.playlist_id
    WHERE p.user_id = ?
    GROUP BY p.id
    ORDER BY p.is_default DESC, p.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Playlists - LyricScroll</title>
 <link rel="icon" type="image/png" href="../favicon.png">
<link rel="stylesheet" href="../frontend/assets/css/user.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.playlists-container {
  max-width: 1200px;
  margin: 20px auto;
  padding: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.page-title {
  font-size: 32px;
  color: var(--text);
  margin: 0;
}

.create-playlist-btn {
  background: #4ade80;
  color: #000;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: 0.2s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.create-playlist-btn:hover {
  background: #22c55e;
  transform: translateY(-2px);
}

.playlists-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.playlist-card {
  background: var(--card);
  border: 1px solid var(--line);
  border-radius: 12px;
  padding: 20px;
  cursor: pointer;
  transition: 0.2s;
  position: relative;
  overflow: hidden;
}

.playlist-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.4);
  border-color: var(--dim);
}

.playlist-card.default {
  border-color: #4ade80;
  background: linear-gradient(135deg, var(--card) 0%, rgba(74, 222, 128, 0.1) 100%);
}

.playlist-icon {
  width: 60px;
  height: 60px;
  background: var(--bar);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  margin-bottom: 16px;
}

.playlist-card.default .playlist-icon {
  background: linear-gradient(135deg, #4ade80, #22c55e);
  color: #000;
}

.playlist-name {
  font-size: 20px;
  font-weight: 600;
  color: var(--text);
  margin-bottom: 8px;
}

.playlist-info {
  font-size: 14px;
  color: var(--dim);
  display: flex;
  align-items: center;
  gap: 6px;
}

.playlist-actions {
  position: absolute;
  top: 16px;
  right: 16px;
  opacity: 0;
  transition: 0.2s;
}

.playlist-card:hover .playlist-actions {
  opacity: 1;
}

.delete-playlist-btn {
  background: #ff4d4d;
  border: none;
  color: white;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: 0.2s;
}

.delete-playlist-btn:hover {
  background: #ff3333;
  transform: scale(1.1);
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
.alert-input {
  width: 100%;
  padding: 12px;
  background: var(--bar);
  border: 1px solid var(--line);
  border-radius: 8px;
  color: var(--text);
  font-size: 14px;
  margin-bottom: 20px;
}
.alert-input:focus {
  outline: none;
  border-color: #4ade80;
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
.alert-btn.primary {
  background: #4ade80;
  border-color: #4ade80;
  color: #000;
}
.alert-btn.primary:hover { background: #22c55e; }
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
    <i class="fa-solid fa-list icon"></i><span>Setlists</span>
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
  <li data-link="setlist.php"><i class="fa-solid fa-list"></i> Setlists</li>
  <li data-link="search.php"><i class="fa-solid fa-magnifying-glass"></i> Search</li>
  <hr>
  <li data-link="create.php"><i class="fa-solid fa-plus"></i> Create Song</li>
  <hr>
  <li class="logout" data-link="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</li>
</ul>

<div class="overlay" id="overlay"></div>

<section class="page-content">
  <div class="playlists-container">
    <div class="page-header">
      <h1 class="page-title">My Playlists</h1>
      <button class="create-playlist-btn" onclick="showCreatePlaylist()">
        <i class="fa-solid fa-plus"></i> Create Playlist
      </button>
    </div>

    <?php if ($result->num_rows > 0): ?>
      <div class="playlists-grid">
        <?php while ($playlist = $result->fetch_assoc()): ?>
          <div class="playlist-card <?php echo $playlist['is_default'] ? 'default' : ''; ?>" 
               onclick="window.location.href='view_playlist.php?id=<?php echo $playlist['id']; ?>'">
            <div class="playlist-icon">
              <i class="fa-solid fa-<?php echo $playlist['is_default'] ? 'heart' : 'list-music'; ?>"></i>
            </div>
            <div class="playlist-name"><?php echo htmlspecialchars($playlist['name']); ?></div>
            <div class="playlist-info">
              <i class="fa-solid fa-music"></i>
              <span><?php echo $playlist['song_count']; ?> songs</span>
            </div>
            <?php if (!$playlist['is_default']): ?>
              <div class="playlist-actions" onclick="event.stopPropagation();">
                <button class="delete-playlist-btn" onclick="deletePlaylist(<?php echo $playlist['id']; ?>, '<?php echo htmlspecialchars(addslashes($playlist['name'])); ?>')">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </div>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <i class="fa-solid fa-list-music"></i>
        <h2>No Playlists Yet</h2>
        <p>Create your first playlist to organize your songs</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Custom Alert Box -->
<div class="alert-overlay" id="alertOverlay"></div>
<div class="custom-alert" id="customAlert">
  <div class="alert-content">
    <div class="alert-icon" id="alertIcon">
      <i class="fa-solid fa-circle-check"></i>
    </div>
    <div class="alert-title" id="alertTitle">Success!</div>
    <div class="alert-message" id="alertMessage">Action completed!</div>
    <div id="alertInputContainer"></div>
    <div class="alert-buttons" id="alertButtons">
      <button class="alert-btn" onclick="closeAlert()">OK</button>
    </div>
  </div>
</div>

<script src="../frontend/assets/js/user.js"></script>
<script>
let pendingPlaylistId = null;

function showAlert(type, title, message, buttons = null, hasInput = false) {
  const alert = document.getElementById('customAlert');
  const overlay = document.getElementById('alertOverlay');
  const icon = document.getElementById('alertIcon');
  const alertTitle = document.getElementById('alertTitle');
  const alertMessage = document.getElementById('alertMessage');
  const alertButtons = document.getElementById('alertButtons');
  const inputContainer = document.getElementById('alertInputContainer');

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

  if (hasInput) {
    inputContainer.innerHTML = '<input type="text" id="playlistNameInput" class="alert-input" placeholder="Enter playlist name" maxlength="50">';
  } else {
    inputContainer.innerHTML = '';
  }

  if (buttons) {
    alertButtons.innerHTML = buttons;
  } else {
    alertButtons.innerHTML = '<button class="alert-btn" onclick="closeAlert()">OK</button>';
  }

  overlay.classList.add('show');
  alert.classList.add('show');

  if (hasInput) {
    setTimeout(() => document.getElementById('playlistNameInput')?.focus(), 100);
  }
}

function closeAlert() {
  const alert = document.getElementById('customAlert');
  const overlay = document.getElementById('alertOverlay');
  
  overlay.classList.remove('show');
  alert.classList.remove('show');
  pendingPlaylistId = null;
}

function showCreatePlaylist() {
  showAlert(
    'success',
    'Create New Playlist',
    'Enter a name for your playlist',
    `
      <button class="alert-btn" onclick="closeAlert()">Cancel</button>
      <button class="alert-btn primary" onclick="createPlaylist()">Create</button>
    `,
    true
  );
}

async function createPlaylist() {
  const nameInput = document.getElementById('playlistNameInput');
  const name = nameInput?.value.trim();

  if (!name) {
    showAlert('error', 'Error', 'Please enter a playlist name');
    return;
  }

  try {
    const response = await fetch('create_playlist.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name })
    });

    const result = await response.json();

    if (result.success) {
      showAlert('success', 'Success!', 'Playlist created successfully!');
      setTimeout(() => location.reload(), 1500);
    } else {
      showAlert('error', 'Error', result.message || 'Failed to create playlist');
    }
  } catch (error) {
    showAlert('error', 'Error', 'Network error. Please try again.');
  }
}

function deletePlaylist(playlistId, playlistName) {
  pendingPlaylistId = playlistId;
  showAlert(
    'warning',
    'Delete Playlist',
    `Are you sure you want to delete "${playlistName}"? All songs will be removed from this playlist.`,
    `
      <button class="alert-btn" onclick="closeAlert()">Cancel</button>
      <button class="alert-btn danger" onclick="confirmDeletePlaylist()">Delete</button>
    `
  );
}

async function confirmDeletePlaylist() {
  if (!pendingPlaylistId) return;

  try {
    const response = await fetch('delete_playlist.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ playlist_id: pendingPlaylistId })
    });

    const result = await response.json();

    if (result.success) {
      showAlert('success', 'Success!', 'Playlist deleted successfully!');
      setTimeout(() => location.reload(), 1500);
    } else {
      showAlert('error', 'Error', result.message || 'Failed to delete playlist');
    }
  } catch (error) {
    showAlert('error', 'Error', 'Network error. Please try again.');
  }
}
</script>
</body>
</html>