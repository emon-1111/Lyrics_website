<?php
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Fetch all users except admins
$stmt = $conn->prepare("SELECT id, name, email, created_at FROM users WHERE role='user' ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users - Admin Panel</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
  <link rel="stylesheet" href="../frontend/assets/css/user.css">
  <style>
    .users-container {
      max-width: 1000px;
      margin: 0 auto;
    }
    .user-card {
      background: var(--card);
      border: 1px solid var(--line);
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: 0.2s;
    }
    .user-card:hover {
      background: #1a1a1a;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    .user-info h3 {
      margin: 0 0 8px 0;
      font-size: 18px;
      color: var(--text);
    }
    .user-info p {
      margin: 0;
      font-size: 14px;
      color: var(--dim);
    }
    .user-actions {
      display: flex;
      gap: 10px;
    }
    .remove-btn {
      background: #ff4d4d;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      transition: 0.2s;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .remove-btn:hover {
      background: #ff3333;
      transform: scale(1.05);
    }
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: var(--dim);
    }
    .empty-state i {
      font-size: 64px;
      margin-bottom: 20px;
      opacity: 0.3;
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
    <div class="users-container">
      <h1 style="font-size: 32px; margin-bottom: 10px;">User Management</h1>
      <p style="color: var(--dim); margin-bottom: 30px;">Manage all registered users</p>
      
      <?php if ($result->num_rows > 0): ?>
        <?php while ($user = $result->fetch_assoc()): ?>
          <div class="user-card">
            <div class="user-info">
              <h3><?php echo htmlspecialchars($user['name']); ?></h3>
              <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <div class="user-actions">
              <button class="remove-btn" onclick="removeUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')">
                <i class="fa-solid fa-trash"></i>
                Remove User
              </button>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="empty-state">
          <i class="fa-solid fa-users"></i>
          <h2>No Users Found</h2>
          <p>There are no registered users yet.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <script src="../frontend/assets/js/user.js"></script>
  <script>
    function removeUser(userId, userName) {
      if (confirm(`Are you sure you want to remove "${userName}"? This action cannot be undone.`)) {
        fetch('remove_user.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `user_id=${userId}`
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('User removed successfully!');
            location.reload();
          } else {
            alert('Error: ' + data.message);
          }
        })
        .catch(error => {
          alert('Error removing user: ' + error);
        });
      }
    }
  </script>
</body>
</html>