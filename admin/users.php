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
  <link rel="icon" type="image/png" href="../favicon.png">
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
    /* Custom Alert/Confirm Box */
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
    .alert-icon.success {
      color: #4ade80;
    }
    .alert-icon.error, .alert-icon.warning {
      color: #ff4d4d;
    }
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
    .alert-btn:hover {
      background: var(--line);
    }
    .alert-btn.danger {
      background: #ff4d4d;
      border-color: #ff4d4d;
    }
    .alert-btn.danger:hover {
      background: #ff3333;
    }
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

  <!-- Custom Alert Box -->
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
    let pendingUserId = null;
    let pendingUserName = null;

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
      pendingUserId = null;
      pendingUserName = null;
    }

    function removeUser(userId, userName) {
      pendingUserId = userId;
      pendingUserName = userName;
      showAlert(
        'warning',
        'Confirm Remove User',
        `Are you sure you want to remove "${userName}"? This will also delete all their songs. This action cannot be undone.`,
        `
          <button class="alert-btn" onclick="closeAlert()">Cancel</button>
          <button class="alert-btn danger" onclick="confirmRemove()">Remove User</button>
        `
      );
    }

    async function confirmRemove() {
      if (!pendingUserId) return;

      try {
        const response = await fetch('remove_user.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `user_id=${pendingUserId}`
        });

        const data = await response.json();

        if (data.success) {
          showAlert('success', 'Success!', 'User removed successfully!');
          setTimeout(() => location.reload(), 1500);
        } else {
          showAlert('error', 'Error', data.message || 'Failed to remove user');
        }
      } catch (error) {
        showAlert('error', 'Error', 'Network error. Please try again.');
      }
    }
  </script>
</body>
</html>