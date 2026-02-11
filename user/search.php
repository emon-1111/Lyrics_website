<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all songs (user's own songs + public songs)
$stmt = $conn->prepare("
    SELECT s.id, s.title, s.subtitle, s.is_public, s.user_id, u.name as creator_name
    FROM songs s
    LEFT JOIN users u ON s.user_id = u.id
    WHERE s.user_id = ? OR s.is_public = 1
    ORDER BY s.title ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$songs_result = $stmt->get_result();

// Fetch user's playlists for add to playlist feature
$playlists_stmt = $conn->prepare("SELECT id, name FROM playlists WHERE user_id = ? ORDER BY is_default DESC, name ASC");
$playlists_stmt->bind_param("i", $user_id);
$playlists_stmt->execute();
$playlists_result = $playlists_stmt->get_result();
$playlists = [];
while ($pl = $playlists_result->fetch_assoc()) {
    $playlists[] = $pl;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Search Songs - LyricScroll</title>
<link rel="icon" type="image/png" href="../favicon.png">
<link rel="stylesheet" href="../frontend/assets/css/user.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.search-container {
  max-width: 1200px;
  margin: 20px auto;
  padding: 20px;
}

.search-header {
  margin-bottom: 30px;
}

.page-title {
  font-size: 32px;
  color: var(--text);
  margin: 0 0 20px 0;
}

.search-box {
  position: relative;
  max-width: 600px;
  margin: 0 auto 30px;
}

.search-input {
  width: 100%;
  padding: 16px 50px 16px 20px;
  background: var(--card);
  border: 2px solid var(--line);
  border-radius: 50px;
  color: var(--text);
  font-size: 16px;
  transition: 0.3s;
}

.search-input:focus {
  outline: none;
  border-color: #4ade80;
  box-shadow: 0 0 0 3px rgba(74, 222, 128, 0.1);
}

.search-icon {
  position: absolute;
  right: 20px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 20px;
  color: var(--dim);
  pointer-events: none;
}

.search-results-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding: 0 10px;
}

.results-count {
  font-size: 16px;
  color: var(--dim);
}

.results-count span {
  color: #4ade80;
  font-weight: 600;
}

.sort-options {
  display: flex;
  gap: 10px;
  align-items: center;
}

.sort-btn {
  background: var(--bar);
  border: 1px solid var(--line);
  color: var(--text);
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  transition: 0.2s;
}

.sort-btn:hover {
  background: var(--line);
}

.sort-btn.active {
  background: #4ade80;
  border-color: #4ade80;
  color: #000;
}

.songs-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.song-item {
  background: var(--card);
  padding: 20px;
  border-radius: 12px;
  border: 1px solid var(--line);
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: 0.2s;
  opacity: 1;
  transform: scale(1);
}

.song-item.hidden {
  display: none;
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

.song-title .highlight {
  background: #4ade80;
  color: #000;
  padding: 0 4px;
  border-radius: 3px;
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
  flex-wrap: wrap;
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
  display: flex;
  align-items: center;
  gap: 6px;
}

.action-btn:hover {
  background: var(--line);
}

.playlist-btn {
  background: #4ade80;
  border-color: #4ade80;
  color: #000;
}

.playlist-btn:hover {
  background: #22c55e;
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

.no-results {
  text-align: center;
  padding: 60px 20px;
  color: var(--dim);
  display: none;
}

.no-results.show {
  display: block;
}

.no-results i {
  font-size: 48px;
  margin-bottom: 16px;
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
.playlist-select {
  width: 100%;
  padding: 12px;
  background: var(--bar);
  border: 1px solid var(--line);
  border-radius: 8px;
  color: var(--text);
  font-size: 14px;
  margin-bottom: 20px;
  cursor: pointer;
}
.playlist-select:focus {
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

<section class="page-content">
  <div class="search-container">
    <div class="search-header">
      <h1 class="page-title">Search Songs</h1>
      
      <div class="search-box">
        <input 
          type="text" 
          id="searchInput" 
          class="search-input" 
          placeholder="Search by song title or artist..."
          autocomplete="off"
        >
        <i class="fa-solid fa-magnifying-glass search-icon"></i>
      </div>
    </div>

    <div class="search-results-header">
      <div class="results-count">
        Showing <span id="resultsCount"><?php echo $songs_result->num_rows; ?></span> of <span><?php echo $songs_result->num_rows; ?></span> songs
      </div>
      <div class="sort-options">
        <span style="color: var(--dim); font-size: 14px;">Sort:</span>
        <button class="sort-btn active" onclick="sortSongs('title')">Title</button>
        <button class="sort-btn" onclick="sortSongs('recent')">Recent</button>
      </div>
    </div>

    <div class="songs-list" id="songsList">
      <?php if ($songs_result->num_rows > 0): ?>
        <?php while ($song = $songs_result->fetch_assoc()): ?>
          <div class="song-item" 
               data-title="<?php echo strtolower(htmlspecialchars($song['title'])); ?>"
               data-subtitle="<?php echo strtolower(htmlspecialchars($song['subtitle'])); ?>"
               data-creator="<?php echo strtolower(htmlspecialchars($song['creator_name'] ?? '')); ?>"
               data-date="<?php echo $song['id']; ?>">
            <div class="song-info">
              <h3 class="song-title">
                <span class="song-title-text"><?php echo htmlspecialchars($song['title']); ?></span>
                <?php if ($song['is_public'] == 1): ?>
                  <span class="badge public-badge">PUBLIC</span>
                <?php endif; ?>
              </h3>
              <p class="song-subtitle">
                <span class="song-subtitle-text"><?php echo htmlspecialchars($song['subtitle']); ?></span>
                <?php if ($song['user_id'] != $user_id): ?>
                  <span style="color: #4ade80;"> • by <span class="creator-text"><?php echo htmlspecialchars($song['creator_name']); ?></span></span>
                <?php endif; ?>
              </p>
            </div>
            <div class="song-actions">
              <button class="action-btn" onclick="window.location.href='view_song.php?id=<?php echo $song['id']; ?>'">
                <i class="fa-solid fa-eye"></i> View
              </button>
              <button class="action-btn playlist-btn" onclick="showAddToPlaylist(<?php echo $song['id']; ?>, '<?php echo htmlspecialchars(addslashes($song['title'])); ?>')">
                <i class="fa-solid fa-plus"></i> Playlist
              </button>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="empty-state">
          <i class="fa-solid fa-music"></i>
          <h2>No Songs Available</h2>
          <p>Start by creating your first song!</p>
        </div>
      <?php endif; ?>
    </div>

    <div class="no-results" id="noResults">
      <i class="fa-solid fa-search"></i>
      <h2>No Results Found</h2>
      <p>Try searching with different keywords</p>
    </div>
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
    <div id="playlistSelectContainer"></div>
    <div class="alert-buttons" id="alertButtons">
      <button class="alert-btn" onclick="closeAlert()">OK</button>
    </div>
  </div>
</div>

<script src="../frontend/assets/js/user.js"></script>
<script>
let pendingSongId = null;
let pendingSongTitle = null;
let currentSort = 'title';

const playlists = <?php echo json_encode($playlists); ?>;
const totalSongs = <?php echo $songs_result->num_rows; ?>;

// Search functionality
const searchInput = document.getElementById('searchInput');
const songItems = document.querySelectorAll('.song-item');
const resultsCount = document.getElementById('resultsCount');
const noResults = document.getElementById('noResults');

searchInput.addEventListener('input', function() {
  const searchTerm = this.value.toLowerCase().trim();
  let visibleCount = 0;

  songItems.forEach(item => {
    const title = item.getAttribute('data-title');
    const subtitle = item.getAttribute('data-subtitle');
    const creator = item.getAttribute('data-creator');
    
    // Search in title, subtitle, or creator name
    if (title.includes(searchTerm) || subtitle.includes(searchTerm) || creator.includes(searchTerm)) {
      item.classList.remove('hidden');
      visibleCount++;
      
      // Highlight matching text
      highlightText(item, searchTerm);
    } else {
      item.classList.add('hidden');
    }
  });

  // Update results count
  resultsCount.textContent = visibleCount;
  
  // Show/hide no results message
  if (visibleCount === 0 && searchTerm !== '') {
    noResults.classList.add('show');
  } else {
    noResults.classList.remove('show');
  }
});

function highlightText(item, searchTerm) {
  if (!searchTerm) {
    // Reset highlighting
    const titleText = item.querySelector('.song-title-text');
    const subtitleText = item.querySelector('.song-subtitle-text');
    const creatorText = item.querySelector('.creator-text');
    
    if (titleText) titleText.innerHTML = titleText.textContent;
    if (subtitleText) subtitleText.innerHTML = subtitleText.textContent;
    if (creatorText) creatorText.innerHTML = creatorText.textContent;
    return;
  }

  // Highlight matching text in title
  const titleText = item.querySelector('.song-title-text');
  if (titleText) {
    const text = titleText.textContent;
    const regex = new RegExp(`(${searchTerm})`, 'gi');
    titleText.innerHTML = text.replace(regex, '<span class="highlight">$1</span>');
  }

  // Highlight matching text in subtitle
  const subtitleText = item.querySelector('.song-subtitle-text');
  if (subtitleText) {
    const text = subtitleText.textContent;
    const regex = new RegExp(`(${searchTerm})`, 'gi');
    subtitleText.innerHTML = text.replace(regex, '<span class="highlight">$1</span>');
  }

  // Highlight matching text in creator
  const creatorText = item.querySelector('.creator-text');
  if (creatorText) {
    const text = creatorText.textContent;
    const regex = new RegExp(`(${searchTerm})`, 'gi');
    creatorText.innerHTML = text.replace(regex, '<span class="highlight">$1</span>');
  }
}

// Sort functionality
function sortSongs(type) {
  currentSort = type;
  
  // Update active button
  document.querySelectorAll('.sort-btn').forEach(btn => {
    btn.classList.remove('active');
  });
  event.target.classList.add('active');

  const songsList = document.getElementById('songsList');
  const songsArray = Array.from(songItems);

  if (type === 'title') {
    songsArray.sort((a, b) => {
      const titleA = a.getAttribute('data-title');
      const titleB = b.getAttribute('data-title');
      return titleA.localeCompare(titleB);
    });
  } else if (type === 'recent') {
    songsArray.sort((a, b) => {
      const dateA = parseInt(a.getAttribute('data-date'));
      const dateB = parseInt(b.getAttribute('data-date'));
      return dateB - dateA; // Descending order
    });
  }

  // Re-append in sorted order
  songsArray.forEach(item => songsList.appendChild(item));
}

// Alert functions
function showAlert(type, title, message, buttons = null, hasPlaylistSelect = false) {
  const alert = document.getElementById('customAlert');
  const overlay = document.getElementById('alertOverlay');
  const icon = document.getElementById('alertIcon');
  const alertTitle = document.getElementById('alertTitle');
  const alertMessage = document.getElementById('alertMessage');
  const alertButtons = document.getElementById('alertButtons');
  const selectContainer = document.getElementById('playlistSelectContainer');

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

  if (hasPlaylistSelect && playlists.length > 0) {
    let selectHTML = '<select id="playlistSelect" class="playlist-select">';
    playlists.forEach(pl => {
      selectHTML += `<option value="${pl.id}">${pl.name}</option>`;
    });
    selectHTML += '</select>';
    selectContainer.innerHTML = selectHTML;
  } else {
    selectContainer.innerHTML = '';
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
  pendingSongTitle = null;
}

function showAddToPlaylist(songId, songTitle) {
  if (playlists.length === 0) {
    showAlert('error', 'No Playlists', 'Please create a playlist first');
    return;
  }

  pendingSongId = songId;
  pendingSongTitle = songTitle;
  
  showAlert(
    'success',
    'Add to Playlist',
    `Add "${songTitle}" to:`,
    `
      <button class="alert-btn" onclick="closeAlert()">Cancel</button>
      <button class="alert-btn primary" onclick="addToPlaylist()">Add</button>
    `,
    true
  );
}

async function addToPlaylist() {
  const select = document.getElementById('playlistSelect');
  const playlistId = select?.value;

  if (!playlistId || !pendingSongId) return;

  try {
    const response = await fetch('add_to_playlist.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        playlist_id: parseInt(playlistId),
        song_id: pendingSongId
      })
    });

    const result = await response.json();

    if (result.success) {
      showAlert('success', 'Success!', `"${pendingSongTitle}" added to playlist!`);
    } else {
      showAlert('error', 'Error', result.message || 'Failed to add song to playlist');
    }
  } catch (error) {
    showAlert('error', 'Error', 'Network error. Please try again.');
  }
}

// Auto-focus search input on page load
window.addEventListener('load', () => {
  searchInput.focus();
});
</script>
</body>
</html>