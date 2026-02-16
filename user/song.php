<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT s.id, s.title, s.subtitle, s.is_public, s.user_id, u.name as creator_name
    FROM songs s
    LEFT JOIN users u ON s.user_id = u.id
    WHERE s.user_id = ? OR s.is_public = 1
    ORDER BY s.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$songs_result = $stmt->get_result();

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
<title>Songs - LyricScroll</title>
<link rel="icon" type="image/png" href="../favicon.png">
<link rel="stylesheet" href="../frontend/assets/css/user.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>

/* ---------- Page Layout ---------- */
.songs-container{
  max-width:1200px;
  margin:20px auto;
  padding:20px;
}

.songs-header{
  margin-bottom:28px;
}

.page-title{
  font-size:30px;
  color:var(--text);
  margin-bottom:6px;
}

.page-subtitle{
  font-size:15px;
  color:var(--dim);
}

/* ---------- Stats ---------- */
.songs-stats{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin:22px 0;
  padding:0 8px;
}

.stats-left{
  display:flex;
  gap:24px;
}

.stat-item{
  display:flex;
  align-items:center;
  gap:7px;
  font-size:14px;
  color:var(--dim);
}

.stat-item i{
  color:#4ade80;
}

.stat-number{
  font-weight:600;
  color:var(--text);
}

/* ---------- Sort Buttons ---------- */
.sort-options{
  display:flex;
  align-items:center;
  gap:10px;
}

.sort-btn{
  background:var(--bar);
  border:1px solid var(--line);
  padding:7px 14px;
  border-radius:7px;
  font-size:14px;
  color:var(--text);
  cursor:pointer;
}

.sort-btn:hover{
  background:var(--line);
}

.sort-btn.active{
  background:#4ade80;
  border-color:#4ade80;
  color:#000;
}

/* ---------- Song List ---------- */
.songs-list{
  display:flex;
  flex-direction:column;
  gap:14px;
}

.song-item{
  background:var(--card);
  border:1px solid var(--line);
  border-radius:12px;
  padding:18px 20px;

  display:flex;
  justify-content:space-between;
  align-items:center;

  transition:border-color .2s ease, transform .15s ease;
}

.song-item:hover{
  border-color:var(--dim);
  transform:translateX(3px);
}

.song-info{
  flex:1;
}

/* ---------- Song Text ---------- */
.song-title{
  font-size:18px;
  margin-bottom:4px;
  color:var(--text);

  display:flex;
  align-items:center;
  gap:8px;
}

.song-subtitle{
  font-size:14px;
  color:var(--dim);
}

/* ---------- Badges ---------- */
.badge{
  font-size:11px;
  font-weight:600;
  padding:3px 7px;
  border-radius:4px;
}

.public-badge{
  background:#4ade80;
  color:#000;
}

.my-song-badge{
  background:#3b82f6;
  color:#fff;
}

/* ---------- Buttons ---------- */
.song-actions{
  display:flex;
  gap:10px;
  flex-wrap:wrap;
}

.action-btn{
  display:flex;
  align-items:center;
  gap:6px;

  padding:7px 14px;
  font-size:14px;

  border-radius:6px;
  border:1px solid var(--line);
  background:var(--bar);
  color:var(--text);

  cursor:pointer;
}

.action-btn:hover{
  background:var(--line);
}

/* variants */
.playlist-btn{
  background:#4ade80;
  border-color:#4ade80;
  color:#000;
}

.playlist-btn:hover{
  background:#22c55e;
}

.edit-btn{
  color:#f59e0b;
  border-color:rgba(245,158,11,.35);
}

.edit-btn:hover{
  background:rgba(245,158,11,.1);
}

.delete-btn{
  color:#ff4d4d;
  border-color:rgba(255,77,77,.35);
}

.delete-btn:hover{
  background:rgba(255,77,77,.1);
}

/* ---------- Empty State ---------- */
.empty-state{
  text-align:center;
  padding:70px 20px;
  color:var(--dim);
}

.empty-state i{
  font-size:60px;
  margin-bottom:18px;
  opacity:.35;
}

.empty-state button{
  margin-top:18px;
  padding:11px 22px;
  border:none;
  border-radius:8px;

  background:#4ade80;
  color:#000;
  font-weight:600;
  cursor:pointer;
}

.empty-state button:hover{
  background:#22c55e;
}

/* ---------- Custom Alert ---------- */
.custom-alert{
  position:fixed;
  top:50%;
  left:50%;
  transform:translate(-50%,-50%) scale(.85);

  background:var(--card);
  border:1px solid var(--line);
  border-radius:14px;

  padding:28px;
  min-width:380px;

  opacity:0;
  pointer-events:none;
  z-index:10000;

  box-shadow:0 12px 35px rgba(0,0,0,.5);
  transition:all .25s ease;
}

.custom-alert.show{
  opacity:1;
  transform:translate(-50%,-50%) scale(1);
  pointer-events:auto;
}

.alert-content{
  text-align:center;
}

.alert-icon{
  font-size:46px;
  margin-bottom:16px;
}

.alert-icon.success{ color:#4ade80; }
.alert-icon.error,
.alert-icon.warning{ color:#ff4d4d; }

.alert-title{
  font-size:22px;
  font-weight:600;
  color:var(--text);
  margin-bottom:8px;
}

.alert-message{
  font-size:15px;
  color:var(--dim);
  margin-bottom:22px;
}

.playlist-select{
  width:100%;
  padding:11px;
  border-radius:8px;
  border:1px solid var(--line);
  background:var(--bar);
  color:var(--text);
  margin-bottom:18px;
}

.playlist-select:focus{
  outline:none;
  border-color:#4ade80;
}

/* buttons inside alert */
.alert-buttons{
  display:flex;
  justify-content:center;
  gap:10px;
}

.alert-btn{
  padding:11px 26px;
  border-radius:8px;
  border:1px solid var(--line);
  background:var(--bar);
  color:var(--text);
  cursor:pointer;
}

.alert-btn:hover{
  background:var(--line);
}

.alert-btn.primary{
  background:#4ade80;
  border-color:#4ade80;
  color:#000;
}

.alert-btn.primary:hover{
  background:#22c55e;
}

.alert-btn.danger{
  background:#ff4d4d;
  border-color:#ff4d4d;
}

.alert-btn.danger:hover{
  background:#ff3333;
}

/* overlay */
.alert-overlay{
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.7);
  opacity:0;
  pointer-events:none;
  transition:opacity .25s ease;
  z-index:9999;
}

.alert-overlay.show{
  opacity:1;
  pointer-events:auto;
}

</style>

</head>
<body>

<nav class="navbar">
  <div class="nav-item logo-container">
    <img src="../frontend/assets/images/transparent_logo.png" class="logo" alt="Logo">
  </div>
  <div class="nav-item" data-page="dashboard.php"><i class="fa-solid fa-home icon"></i><span>Dashboard</span></div>
  <div class="nav-item active" data-page="song.php"><i class="fa-solid fa-music icon"></i><span>Songs</span></div>
  <div class="nav-item" data-page="setlist.php"><i class="fa-solid fa-list icon"></i><span>Setlists</span></div>
  <div class="nav-item" data-page="create.php"><i class="fa-solid fa-plus icon"></i><span>Create</span></div>
  <div class="nav-item" data-page="search.php"><i class="fa-solid fa-magnifying-glass icon"></i><span>Search</span></div>
  <div class="nav-item" id="menuBtn"><i class="fa-solid fa-bars icon"></i></div>
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
  <div class="songs-container">
    <div class="songs-header">
      <h1 class="page-title">My Songs</h1>
      <p class="page-subtitle">Your personal collection and public songs</p>
    </div>

    <?php
    $total_songs = $songs_result->num_rows;
    $my_songs_count = 0;
    $public_songs_count = 0;
    mysqli_data_seek($songs_result, 0);
    while ($temp = $songs_result->fetch_assoc()) {
      if ($temp['user_id'] == $user_id) $my_songs_count++;
      else $public_songs_count++;
    }
    mysqli_data_seek($songs_result, 0);
    ?>

    <div class="songs-stats">
      <div class="stats-left">
        <div class="stat-item"><i class="fa-solid fa-music"></i><span><span class="stat-number"><?php echo $total_songs; ?></span> Total Songs</span></div>
        <div class="stat-item"><i class="fa-solid fa-user"></i><span><span class="stat-number"><?php echo $my_songs_count; ?></span> My Songs</span></div>
        <div class="stat-item"><i class="fa-solid fa-globe"></i><span><span class="stat-number"><?php echo $public_songs_count; ?></span> Public Songs</span></div>
      </div>
      <div class="sort-options">
        <span style="color:var(--dim);font-size:14px;">Sort:</span>
        <button class="sort-btn active" onclick="sortSongs('recent')">Recent</button>
        <button class="sort-btn" onclick="sortSongs('title')">Title</button>
      </div>
    </div>

    <?php if ($songs_result->num_rows > 0): ?>
      <div class="songs-list" id="songsList">
        <?php while ($song = $songs_result->fetch_assoc()): ?>
          <div class="song-item"
               data-title="<?php echo strtolower(htmlspecialchars($song['title'])); ?>"
               data-date="<?php echo $song['id']; ?>">
            <div class="song-info">
              <h3 class="song-title">
                <?php echo htmlspecialchars($song['title']); ?>
                <?php if ($song['user_id'] == $user_id && $song['is_public'] == 0): ?>
                  <span class="badge my-song-badge">MY SONG</span>
                <?php elseif ($song['is_public'] == 1): ?>
                  <span class="badge public-badge">PUBLIC</span>
                <?php endif; ?>
              </h3>
              <p class="song-subtitle">
                <?php echo htmlspecialchars($song['subtitle']); ?>
                <?php if ($song['user_id'] != $user_id): ?>
                  <span style="color:#4ade80;"> • by <?php echo htmlspecialchars($song['creator_name']); ?></span>
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
              <?php if ($song['user_id'] == $user_id && $song['is_public'] == 0): ?>
                <!-- Own song: Edit + Delete -->
                <button class="action-btn edit-btn" onclick="window.location.href='edit_song.php?id=<?php echo $song['id']; ?>'">
                  <i class="fa-solid fa-pen"></i> Edit
                </button>
                <button class="action-btn delete-btn" onclick="deleteSong(<?php echo $song['id']; ?>, '<?php echo htmlspecialchars(addslashes($song['title'])); ?>')">
                  <i class="fa-solid fa-trash"></i> Delete
                </button>
              <?php elseif ($song['is_public'] == 1): ?>
                <!-- Admin song: Delete only -->
                <button class="action-btn delete-btn" onclick="deleteSong(<?php echo $song['id']; ?>, '<?php echo htmlspecialchars(addslashes($song['title'])); ?>')">
                  <i class="fa-solid fa-trash"></i> Delete
                </button>
              <?php endif; ?>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <i class="fa-solid fa-music"></i>
        <h2>No Songs Yet</h2>
        <p>Create your first song to get started!</p>
        <button onclick="window.location.href='create.php'"><i class="fa-solid fa-plus"></i> Create Song</button>
      </div>
    <?php endif; ?>
  </div>
</section>

<div class="alert-overlay" id="alertOverlay"></div>
<div class="custom-alert" id="customAlert">
  <div class="alert-content">
    <div class="alert-icon" id="alertIcon"><i class="fa-solid fa-circle-check"></i></div>
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
const playlists = <?php echo json_encode($playlists); ?>;
const songItems = document.querySelectorAll('.song-item');

function sortSongs(type) {
  document.querySelectorAll('.sort-btn').forEach(btn => btn.classList.remove('active'));
  event.target.classList.add('active');
  const songsList = document.getElementById('songsList');
  const arr = Array.from(songItems);
  if (type === 'title') arr.sort((a,b) => a.getAttribute('data-title').localeCompare(b.getAttribute('data-title')));
  else arr.sort((a,b) => parseInt(b.getAttribute('data-date')) - parseInt(a.getAttribute('data-date')));
  arr.forEach(item => songsList.appendChild(item));
}

function showAlert(type, title, message, buttons = null, hasPlaylistSelect = false) {
  const alert = document.getElementById('customAlert');
  const overlay = document.getElementById('alertOverlay');
  const icon = document.getElementById('alertIcon');
  document.getElementById('alertTitle').textContent = title;
  document.getElementById('alertMessage').textContent = message;
  if (type === 'success') { icon.className = 'alert-icon success'; icon.innerHTML = '<i class="fa-solid fa-circle-check"></i>'; }
  else if (type === 'warning') { icon.className = 'alert-icon warning'; icon.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>'; }
  else { icon.className = 'alert-icon error'; icon.innerHTML = '<i class="fa-solid fa-circle-xmark"></i>'; }
  const selectContainer = document.getElementById('playlistSelectContainer');
  if (hasPlaylistSelect && playlists.length > 0) {
    let html = '<select id="playlistSelect" class="playlist-select">';
    playlists.forEach(pl => { html += `<option value="${pl.id}">${pl.name}</option>`; });
    html += '</select>';
    selectContainer.innerHTML = html;
  } else { selectContainer.innerHTML = ''; }
  document.getElementById('alertButtons').innerHTML = buttons || '<button class="alert-btn" onclick="closeAlert()">OK</button>';
  overlay.classList.add('show');
  alert.classList.add('show');
}

function closeAlert() {
  document.getElementById('customAlert').classList.remove('show');
  document.getElementById('alertOverlay').classList.remove('show');
  pendingSongId = null;
  pendingSongTitle = null;
}

function showAddToPlaylist(songId, songTitle) {
  if (playlists.length === 0) { showAlert('error', 'No Playlists', 'Please create a playlist first'); return; }
  pendingSongId = songId;
  pendingSongTitle = songTitle;
  showAlert('success', 'Add to Playlist', `Add "${songTitle}" to:`,
    `<button class="alert-btn" onclick="closeAlert()">Cancel</button>
     <button class="alert-btn primary" onclick="addToPlaylist()">Add</button>`, true);
}

async function addToPlaylist() {
  const select = document.getElementById('playlistSelect');
  if (!select?.value || !pendingSongId) return;
  try {
    const res = await fetch('add_to_playlist.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ playlist_id: parseInt(select.value), song_id: pendingSongId }) });
    const result = await res.json();
    if (result.success) showAlert('success', 'Success!', `"${pendingSongTitle}" added to playlist!`);
    else showAlert('error', 'Error', result.message || 'Failed to add song to playlist');
  } catch { showAlert('error', 'Error', 'Network error. Please try again.'); }
}

async function deleteSong(id, title) {
  pendingSongId = id;
  pendingSongTitle = title;
  showAlert('warning', 'Confirm Delete', `Are you sure you want to delete "${title}"? This cannot be undone.`,
    `<button class="alert-btn" onclick="closeAlert()">Cancel</button>
     <button class="alert-btn danger" onclick="confirmDelete()">Delete</button>`);
}

async function confirmDelete() {
  if (!pendingSongId) return;
  try {
    const res = await fetch('../user/delete_song.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ id: pendingSongId }) });
    const result = await res.json();
    if (result.success) { showAlert('success', 'Deleted!', 'Song deleted successfully!'); setTimeout(() => location.reload(), 1500); }
    else showAlert('error', 'Error', result.message || 'Failed to delete song');
  } catch { showAlert('error', 'Error', 'Network error. Please try again.'); }
}
</script>
</body>
</html>