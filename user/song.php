<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Songs - LyricScroll</title>
<link rel="stylesheet" href="../frontend/assets/css/user.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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

<div class="overlay" id="overlay"></div>

<section class="page-content dashboard">
  <?php
  // Fetch user's private songs AND all public songs
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
  $result = $stmt->get_result();
  ?>
  
  <div class="songs-container">
    <h2 class="page-title"><?php echo $result->num_rows; ?> Songs</h2>
    
    <?php if ($result->num_rows > 0): ?>
      <?php while ($song = $result->fetch_assoc()): ?>
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
            <button class="action-btn" onclick="window.location.href='view_song.php?id=<?php echo $song['id']; ?>'">View</button>
            <?php if ($song['user_id'] == $user_id): ?>
              <button class="action-btn delete-btn" onclick="deleteSong(<?php echo $song['id']; ?>)">Delete</button>
            <?php endif; ?>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="empty-state">
        <p>No songs yet. Create your first song!</p>
        <button onclick="window.location.href='create.php'">Create Song</button>
      </div>
    <?php endif; ?>
  </div>
  
  <?php $stmt->close(); ?>
</section>

<style>
.songs-container {
  max-width: 1000px;
  margin: 20px auto;
  padding: 20px;
}

.page-title {
  font-size: 32px;
  margin-bottom: 20px;
  color: var(--text);
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

.delete-btn {
  color: #ff4d4d;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: var(--dim);
}

.empty-state button {
  margin-top: 20px;
  padding: 10px 20px;
  background: var(--bar);
  border: 1px solid var(--line);
  color: var(--text);
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
}
</style>

<script>
async function deleteSong(id) {
  if (!confirm('Are you sure you want to delete this song?')) return;
  
  try {
    const response = await fetch('../user/delete_song.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    
    const result = await response.json();
    
    if (result.success) {
      location.reload();
    } else {
      alert(result.message);
    }
  } catch (error) {
    alert('Error deleting song');
  }
}
</script>

<script src="../frontend/assets/js/user.js"></script>
</body>
</html>