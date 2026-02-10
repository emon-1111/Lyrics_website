<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get statistics
$my_songs = $conn->query("SELECT COUNT(*) as count FROM songs WHERE user_id = $user_id")->fetch_assoc()['count'];
$public_songs = $conn->query("SELECT COUNT(*) as count FROM songs WHERE is_public = 1")->fetch_assoc()['count'];
$my_playlists = $conn->query("SELECT COUNT(*) as count FROM playlists WHERE user_id = $user_id")->fetch_assoc()['count'];

// Get recent songs (last 5)
$recent_stmt = $conn->prepare("
    SELECT s.id, s.title, s.subtitle, s.is_public, s.user_id, u.name as creator_name
    FROM songs s
    LEFT JOIN users u ON s.user_id = u.id
    WHERE s.user_id = ? OR s.is_public = 1
    ORDER BY s.created_at DESC
    LIMIT 5
");
$recent_stmt->bind_param("i", $user_id);
$recent_stmt->execute();
$recent_songs = $recent_stmt->get_result();

// Get playlists (first 3)
$playlists_stmt = $conn->prepare("
    SELECT p.id, p.name, p.is_default, COUNT(ps.id) as song_count
    FROM playlists p
    LEFT JOIN playlist_songs ps ON p.id = ps.playlist_id
    WHERE p.user_id = ?
    GROUP BY p.id
    ORDER BY p.is_default DESC, p.created_at DESC
    LIMIT 3
");
$playlists_stmt->bind_param("i", $user_id);
$playlists_stmt->execute();
$playlists = $playlists_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - LyricScroll</title>
<link rel="stylesheet" href="../frontend/assets/css/user.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.dashboard-container {
  max-width: 1200px;
  margin: 20px auto;
  padding: 20px;
}

.welcome-section {
  background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
  border-radius: 16px;
  padding: 40px;
  margin-bottom: 30px;
  color: #000;
}

.welcome-section h1 {
  font-size: 36px;
  margin: 0 0 10px 0;
}

.welcome-section p {
  font-size: 18px;
  opacity: 0.8;
  margin: 0;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.stat-card {
  background: var(--card);
  border: 1px solid var(--line);
  border-radius: 12px;
  padding: 24px;
  transition: 0.2s;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.3);
}

.stat-icon {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #4ade80, #22c55e);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: #000;
  margin-bottom: 16px;
}

.stat-number {
  font-size: 32px;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 4px;
}

.stat-label {
  font-size: 14px;
  color: var(--dim);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-title {
  font-size: 24px;
  color: var(--text);
  margin: 0;
}

.view-all-link {
  color: #4ade80;
  text-decoration: none;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: 0.2s;
}

.view-all-link:hover {
  color: #22c55e;
}

.content-section {
  margin-bottom: 40px;
}

.song-list, .playlist-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.song-item, .playlist-item {
  background: var(--card);
  border: 1px solid var(--line);
  border-radius: 12px;
  padding: 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: 0.2s;
  cursor: pointer;
}

.song-item:hover, .playlist-item:hover {
  border-color: var(--dim);
  transform: translateX(4px);
}

.item-info {
  flex: 1;
}

.item-title {
  font-size: 16px;
  color: var(--text);
  margin: 0 0 4px 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.item-subtitle {
  font-size: 14px;
  color: var(--dim);
  margin: 0;
}

.badge {
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 4px;
  font-weight: 600;
}

.public-badge {
  background: #4ade80;
  color: #000;
}

.my-song-badge {
  background: #3b82f6;
  color: #fff;
}

.item-action {
  color: var(--dim);
  font-size: 18px;
}

.quick-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-top: 30px;
}

.action-card {
  background: var(--card);
  border: 1px solid var(--line);
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  cursor: pointer;
  transition: 0.2s;
  text-decoration: none;
  color: var(--text);
}

.action-card:hover {
  border-color: #4ade80;
  transform: translateY(-4px);
}

.action-icon {
  font-size: 32px;
  margin-bottom: 12px;
  color: #4ade80;
}

.action-title {
  font-size: 16px;
  font-weight: 600;
  margin: 0;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: var(--dim);
}

.empty-state i {
  font-size: 48px;
  margin-bottom: 16px;
  opacity: 0.3;
}
</style>
</head>
<body>

<nav class="navbar">
  <div class="nav-item logo-container">
    <img src="../frontend/assets/images/transparent_logo.png" class="logo" alt="Logo">
  </div>
  <div class="nav-item active" data-page="dashboard.php">
    <i class="fa-solid fa-home icon"></i><span>Dashboard</span>
  </div>
  <div class="nav-item" data-page="song.php">
    <i class="fa-solid fa-music icon"></i><span>Songs</span>
  </div>
  <div class="nav-item" data-page="setlist.php">
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
  <div class="dashboard-container">
    <!-- Welcome Section -->
    <div class="welcome-section">
      <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
      <p>Your music library is ready to explore</p>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fa-solid fa-music"></i>
        </div>
        <div class="stat-number"><?php echo $my_songs; ?></div>
        <div class="stat-label">My Songs</div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fa-solid fa-globe"></i>
        </div>
        <div class="stat-number"><?php echo $public_songs; ?></div>
        <div class="stat-label">Public Songs</div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fa-solid fa-list"></i>
        </div>
        <div class="stat-number"><?php echo $my_playlists; ?></div>
        <div class="stat-label">Playlists</div>
      </div>
    </div>

    <!-- Recent Songs -->
    <div class="content-section">
      <div class="section-header">
        <h2 class="section-title">Recent Songs</h2>
        <a href="song.php" class="view-all-link">
          View all <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>

      <?php if ($recent_songs->num_rows > 0): ?>
        <div class="song-list">
          <?php while ($song = $recent_songs->fetch_assoc()): ?>
            <div class="song-item" onclick="window.location.href='view_song.php?id=<?php echo $song['id']; ?>'">
              <div class="item-info">
                <h3 class="item-title">
                  <?php echo htmlspecialchars($song['title']); ?>
                  <?php if ($song['user_id'] == $user_id): ?>
                    <span class="badge my-song-badge">MY SONG</span>
                  <?php elseif ($song['is_public'] == 1): ?>
                    <span class="badge public-badge">PUBLIC</span>
                  <?php endif; ?>
                </h3>
                <p class="item-subtitle">
                  <?php echo htmlspecialchars($song['subtitle']); ?>
                  <?php if ($song['user_id'] != $user_id): ?>
                    • by <?php echo htmlspecialchars($song['creator_name']); ?>
                  <?php endif; ?>
                </p>
              </div>
              <i class="fa-solid fa-chevron-right item-action"></i>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <i class="fa-solid fa-music"></i>
          <p>No songs yet. Create your first song!</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Playlists -->
    <div class="content-section">
      <div class="section-header">
        <h2 class="section-title">My Playlists</h2>
        <a href="setlist.php" class="view-all-link">
          View all <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>

      <?php if ($playlists->num_rows > 0): ?>
        <div class="playlist-list">
          <?php while ($playlist = $playlists->fetch_assoc()): ?>
            <div class="playlist-item" onclick="window.location.href='view_playlist.php?id=<?php echo $playlist['id']; ?>'">
              <div class="item-info">
                <h3 class="item-title">
                  <i class="fa-solid fa-<?php echo $playlist['is_default'] ? 'heart' : 'list-music'; ?>" style="color: #4ade80;"></i>
                  <?php echo htmlspecialchars($playlist['name']); ?>
                </h3>
                <p class="item-subtitle"><?php echo $playlist['song_count']; ?> songs</p>
              </div>
              <i class="fa-solid fa-chevron-right item-action"></i>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <i class="fa-solid fa-list-music"></i>
          <p>No playlists yet.</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <a href="create.php" class="action-card">
        <div class="action-icon">
          <i class="fa-solid fa-plus"></i>
        </div>
        <h3 class="action-title">Create Song</h3>
      </a>

      <a href="search.php" class="action-card">
        <div class="action-icon">
          <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <h3 class="action-title">Search Songs</h3>
      </a>

      <a href="setlist.php" class="action-card">
        <div class="action-icon">
          <i class="fa-solid fa-list"></i>
        </div>
        <h3 class="action-title">My Playlists</h3>
      </a>
    </div>
  </div>
</section>

<script src="../frontend/assets/js/user.js"></script>
</body>
</html>