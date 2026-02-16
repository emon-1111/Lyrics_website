<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$admin_id = $_SESSION['user_id'];

// Only show admin's OWN public songs — not user songs
$stmt = $conn->prepare("
    SELECT s.id, s.title, s.subtitle, s.created_at, u.name as creator_name, u.email as creator_email
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
  <title>All Songs - Admin Panel</title>
  <link rel="icon" type="image/png" href="../favicon.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
  <link rel="stylesheet" href="../frontend/assets/css/user.css">
  <style>
    .songs-container { max-width: 1200px; margin: 0 auto; }
    .song-card { background: var(--card); border: 1px solid var(--line); border-radius: 12px; padding: 20px; margin-bottom: 15px; transition: 0.2s; }
    .song-card:hover { background: #1a1a1a; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
    .song-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
    .song-info h3 { margin: 0 0 6px 0; font-size: 20px; color: var(--text); }
    .song-info p { margin: 0; font-size: 14px; color: var(--dim); }
    .song-meta { display: flex; gap: 20px; margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--line); }
    .meta-item { display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--dim); }
    .song-actions { display: flex; gap: 8px; }
    .action-btn { padding: 8px 16px; border-radius: 8px; border: 1px solid var(--line); background: var(--bar); color: var(--text); cursor: pointer; font-size: 13px; transition: 0.2s; display: flex; align-items: center; gap: 6px; }
    .action-btn:hover { background: var(--line); }
    .action-btn.edit { color: #f59e0b; border-color: rgba(245,158,11,0.3); }
    .action-btn.edit:hover { background: rgba(245,158,11,0.1); }
    .action-btn.delete { background: #ff4d4d; border-color: #ff4d4d; color: #fff; }
    .action-btn.delete:hover { background: #ff3333; }
    .empty-state { text-align: center; padding: 60px 20px; color: var(--dim); }
    .empty-state i { font-size: 64px; margin-bottom: 20px; opacity: 0.3; }
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
    <div class="songs-container">
      <h1 style="font-size:32px;margin-bottom:10px;">My Public Songs</h1>
      <p style="color:var(--dim);margin-bottom:30px;">Songs you have pushed to users</p>

      <?php if ($result->num_rows > 0): ?>
        <?php while ($song = $result->fetch_assoc()): ?>
          <div class="song-card">
            <div class="song-header">
              <div class="song-info">
                <h3><?php echo htmlspecialchars($song['title']); ?></h3>
                <?php if ($song['subtitle']): ?><p><?php echo htmlspecialchars($song['subtitle']); ?></p><?php endif; ?>
              </div>
              <div class="song-actions">
                <button class="action-btn" onclick="window.location.href='view_song.php?id=<?php echo $song['id']; ?>'">
                  <i class="fa-solid fa-eye"></i> View
                </button>
                <button class="action-btn edit" onclick="window.location.href='edit_song.php?id=<?php echo $song['id']; ?>'">
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

  <script src="../frontend/assets/js/user.js"></script>
  <script>
    function deleteSong(songId, songTitle) {
      if (confirm(`Delete "${songTitle}"? This cannot be undone.`)) {
        fetch('delete_song.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: `song_id=${songId}` })
          .then(r => r.json())
          .then(data => { if (data.success) location.reload(); else alert('Error: ' + data.message); });
      }
    }
  </script>
</body>
</html>