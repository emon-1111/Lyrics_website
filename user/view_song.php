<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit;
}

// Get song ID
$song_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// Fetch song
$stmt = $conn->prepare("SELECT * FROM songs WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $song_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: song.php");
    exit;
}

$song = $result->fetch_assoc();
$parts = json_decode($song['parts'], true);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($song['title']); ?> - LyricScroll</title>
<link rel="stylesheet" href="../frontend/assets/css/user.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.lyrics-container {
  max-width: 900px;
  margin: 80px auto 20px;
  padding: 20px;
  background: var(--bg);
  min-height: 100vh;
}

.lyrics-header {
  text-align: center;
  margin-bottom: 40px;
  padding-bottom: 20px;
  border-bottom: 1px solid var(--line);
}

.lyrics-header h1 {
  font-size: 36px;
  margin: 0 0 10px 0;
  color: var(--text);
}

.lyrics-header p {
  font-size: 16px;
  color: var(--dim);
  margin: 0;
}

.controls-bar {
  position: fixed;
  top: 70px;
  left: 0;
  right: 0;
  background: var(--bar);
  padding: 15px 20px;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  border-bottom: 1px solid var(--line);
  z-index: 100;
}

.control-group {
  display: flex;
  align-items: center;
  gap: 10px;
}

.control-btn {
  background: var(--card);
  border: 1px solid var(--line);
  color: var(--text);
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: 0.2s;
}

.control-btn:hover {
  background: var(--line);
}

.control-btn.active {
  background: #4CAF50;
  border-color: #4CAF50;
}

.control-label {
  font-size: 14px;
  color: var(--text);
}

.speed-value, .size-value {
  font-size: 14px;
  color: var(--text);
  min-width: 30px;
  text-align: center;
}

.lyrics-part {
  margin-bottom: 40px;
  padding: 20px;
  background: var(--card);
  border-radius: 12px;
  border: 1px solid var(--line);
}

.part-name {
  font-size: 14px;
  font-weight: 600;
  color: var(--dim);
  text-transform: uppercase;
  margin-bottom: 15px;
  padding: 8px 12px;
  background: var(--bar);
  border-radius: 6px;
  display: inline-block;
}

.part-lyrics {
  font-size: 18px;
  line-height: 1.8;
  color: var(--text);
  white-space: pre-wrap;
  font-family: 'Inter', sans-serif;
}

.back-btn {
  position: fixed;
  bottom: 30px;
  right: 30px;
  padding: 12px 24px;
  background: var(--card);
  border: 1px solid var(--line);
  color: var(--text);
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  z-index: 100;
  transition: 0.2s;
}

.back-btn:hover {
  background: var(--line);
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

  <div class="nav-item active" data-page="song.php">
    <i class="fa-solid fa-music icon"></i><span>Songs</span>
  </div>

  <div class="nav-item" data-page="setlist.php">
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

<div class="controls-bar">
  <div class="control-group">
    <span class="control-label">play</span>
    <button class="control-btn" id="playBtn">
      <i class="fa-solid fa-play"></i>
    </button>
  </div>

  <div class="control-group">
    <span class="control-label">auto scroll</span>
    <button class="control-btn" id="autoScrollBtn">
      <i class="fa-solid fa-toggle-off"></i>
    </button>
  </div>

  <div class="control-group">
    <span class="control-label">speed: <span class="speed-value" id="speedValue">12</span></span>
    <button class="control-btn" onclick="changeSpeed(-1)">
      <i class="fa-solid fa-minus"></i>
    </button>
    <button class="control-btn" onclick="changeSpeed(1)">
      <i class="fa-solid fa-plus"></i>
    </button>
  </div>

  <div class="control-group">
    <span class="control-label">size: <span class="size-value" id="sizeValue">20</span></span>
    <button class="control-btn" onclick="changeSize(-2)">
      <i class="fa-solid fa-minus"></i>
    </button>
    <button class="control-btn" onclick="changeSize(2)">
      <i class="fa-solid fa-plus"></i>
    </button>
  </div>
</div>

<div class="lyrics-container" id="lyricsContainer">
  <div class="lyrics-header">
    <h1><?php echo htmlspecialchars($song['title']); ?> (<?php echo $song['id']; ?>)</h1>
    <p><?php echo htmlspecialchars($song['subtitle']); ?></p>
  </div>

  <?php foreach ($parts as $part): ?>
    <div class="lyrics-part">
      <div class="part-name"><?php echo htmlspecialchars($part['name']); ?></div>
      <div class="part-lyrics"><?php echo htmlspecialchars($part['lyrics']); ?></div>
    </div>
  <?php endforeach; ?>
</div>

<button class="back-btn" onclick="window.location.href='song.php'">
  <i class="fa-solid fa-arrow-left"></i> Back to Songs
</button>

<script src="../frontend/assets/js/user.js"></script>
<script>
let autoScroll = false;
let scrollSpeed = 12;
let fontSize = 20;
let scrollInterval;

const playBtn = document.getElementById('playBtn');
const autoScrollBtn = document.getElementById('autoScrollBtn');
const lyricsContainer = document.getElementById('lyricsContainer');

// Play/Pause
playBtn.addEventListener('click', () => {
  if (autoScroll) {
    stopScroll();
  } else {
    startScroll();
  }
});

// Auto scroll toggle
autoScrollBtn.addEventListener('click', () => {
  autoScroll = !autoScroll;
  autoScrollBtn.classList.toggle('active');
  autoScrollBtn.querySelector('i').className = autoScroll ? 'fa-solid fa-toggle-on' : 'fa-solid fa-toggle-off';
  
  if (autoScroll) {
    startScroll();
  } else {
    stopScroll();
  }
});

function startScroll() {
  autoScroll = true;
  playBtn.classList.add('active');
  playBtn.querySelector('i').className = 'fa-solid fa-pause';
  
  scrollInterval = setInterval(() => {
    window.scrollBy(0, scrollSpeed / 10);
  }, 50);
}

function stopScroll() {
  autoScroll = false;
  playBtn.classList.remove('active');
  playBtn.querySelector('i').className = 'fa-solid fa-play';
  clearInterval(scrollInterval);
}

function changeSpeed(delta) {
  scrollSpeed = Math.max(1, Math.min(30, scrollSpeed + delta));
  document.getElementById('speedValue').textContent = scrollSpeed;
}

function changeSize(delta) {
  fontSize = Math.max(12, Math.min(36, fontSize + delta));
  document.getElementById('sizeValue').textContent = fontSize;
  
  document.querySelectorAll('.part-lyrics').forEach(el => {
    el.style.fontSize = fontSize + 'px';
  });
}
</script>
</body>
</html>