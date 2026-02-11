<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$songId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("
    SELECT s.*, u.name as creator_name, u.email as creator_email
    FROM songs s
    LEFT JOIN users u ON s.user_id = u.id
    WHERE s.id = ?
");
$stmt->bind_param("i", $songId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Song not found'); window.location.href='dashboard.php';</script>";
    exit;
}

$song = $result->fetch_assoc();
$parts = json_decode($song['parts'], true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($song['title']); ?> - Admin Panel</title>
  <link rel="icon" type="image/png" href="../favicon.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
  <link rel="stylesheet" href="../frontend/assets/css/user.css">
  <style>
    .song-view-container {
      max-width: 800px;
      margin: 0 auto;
    }
    .song-header {
      background: var(--card);
      border: 1px solid var(--line);
      border-radius: 12px;
      padding: 24px;
      margin-bottom: 20px;
    }
    .song-header h1 {
      margin: 0 0 8px 0;
      font-size: 32px;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .badge {
      font-size: 11px;
      padding: 4px 10px;
      border-radius: 4px;
      font-weight: 600;
    }
    .public-badge {
      background: #4ade80;
      color: #000;
    }
    .private-badge {
      background: #888;
      color: #000;
    }
    .song-header .subtitle {
      color: var(--dim);
      font-size: 18px;
      margin-bottom: 16px;
    }
    .song-meta {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      margin-top: 16px;
      padding-top: 16px;
      border-top: 1px solid var(--line);
    }
    .meta-item {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
      color: var(--dim);
    }
    .song-part {
      background: var(--card);
      border: 1px solid var(--line);
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 16px;
    }
    .part-label {
      font-size: 16px;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .part-text {
      color: var(--text);
      line-height: 1.8;
      white-space: pre-wrap;
      font-size: 15px;
    }
    .back-btn {
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
      margin-bottom: 20px;
    }
    .back-btn:hover {
      background: var(--line);
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar">
    <img src="../frontend/assets/images/transparent_logo.png" alt="Logo" class="logo">
    
    <div class="nav-item" data-page="dashboard.php">
      <i class="fa-solid fa-music icon"></i>
      <span>Songs</span>
    </div>
    
    <div class="nav-item" data-page="create_song.php">
      <i class="fa-solid fa-plus icon"></i>
      <span>Create Song</span>
    </div>
    
    <div class="nav-item" data-page="users.php">
      <i class="fa-solid fa-users icon"></i>
      <span>Users</span>
    </div>
    
    <i class="fa-solid fa-bars icon" style="cursor: pointer;"></i>
  </nav>

  <!-- Dropdown Menu -->
  <ul class="dropdown-menu" id="dropdownMenu">
    <li data-link="dashboard.php">
      <i class="fa-solid fa-music"></i>
      <span>Songs</span>
    </li>
    <li data-link="create_song.php">
      <i class="fa-solid fa-plus"></i>
      <span>Create Song</span>
    </li>
    <li data-link="users.php">
      <i class="fa-solid fa-users"></i>
      <span>Users</span>
    </li>
    <li class="logout" data-link="../auth/logout.php">
      <i class="fa-solid fa-right-from-bracket"></i>
      <span>Logout</span>
    </li>
  </ul>

  <!-- Page Content -->
  <div class="page-content">
    <div class="song-view-container">
      <a href="dashboard.php" class="back-btn">
        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
      </a>
      
      <div class="song-header">
        <h1>
          <?php echo htmlspecialchars($song['title']); ?>
          <?php if ($song['is_public'] == 1): ?>
            <span class="badge public-badge">PUBLIC</span>
          <?php else: ?>
            <span class="badge private-badge">PRIVATE</span>
          <?php endif; ?>
        </h1>
        <?php if ($song['subtitle']): ?>
          <div class="subtitle"><?php echo htmlspecialchars($song['subtitle']); ?></div>
        <?php endif; ?>
        
        <div class="song-meta">
          <div class="meta-item">
            <i class="fa-solid fa-user"></i>
            <span><?php echo htmlspecialchars($song['creator_name'] ?? 'Unknown'); ?></span>
          </div>
          <div class="meta-item">
            <i class="fa-solid fa-envelope"></i>
            <span><?php echo htmlspecialchars($song['creator_email'] ?? 'N/A'); ?></span>
          </div>
          <div class="meta-item">
            <i class="fa-solid fa-calendar"></i>
            <span><?php echo date('M d, Y', strtotime($song['created_at'])); ?></span>
          </div>
        </div>
      </div>
      
      <?php if ($parts && is_array($parts)): ?>
        <?php foreach ($parts as $part): ?>
          <div class="song-part">
            <div class="part-label"><?php echo htmlspecialchars($part['label']); ?></div>
            <div class="part-text"><?php echo htmlspecialchars($part['text']); ?></div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="song-part">
          <div class="part-text">No lyrics available</div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script src="../frontend/assets/js/user.js"></script>
</body>
</html>