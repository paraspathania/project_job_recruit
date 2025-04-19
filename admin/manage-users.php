<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login-signup.php");
    exit();
}

include '../db.php';

// Flash message helper
function flashMessage($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $msg];
}

// Pagination logic
$users_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $users_per_page;

// Fetch total users count (excluding admins)
$total_result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role != 'admin'");
$total_data = $total_result->fetch_assoc();
$total_pages = ceil($total_data['total'] / $users_per_page);

// Fetch users with limit and offset
$user_query = "SELECT id, username, email, role, status, created_at FROM users WHERE role != 'admin' ORDER BY created_at DESC LIMIT $offset, $users_per_page";
$user_result = $conn->query($user_query);
?>

<!DOCTYPE html>
<html lang="en" class="">
<head>
  <meta charset="UTF-8" />
  <title>Admin Panel</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' };
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-white transition duration-300">
<!---  Nav -->
<nav class="bg-white dark:bg-gray-800 shadow-md px-6 py-4 flex justify-between items-center">
  <div class="text-2xl font-bold text-blue-600 dark:text-white">JobTracker</div>
  <div class="flex items-center space-x-6">
    <a href="../home.php" class="text-blue-600 dark:text-white hover:text-blue-400">Home</a>
    <a href="../about.php" class="hover:text-blue-500 dark:text-white">About</a>
    <a href="../contact.php" class="hover:text-blue-500 dark:text-white">Contact</a>
    <?php if ($_SESSION['role'] === 'admin'): ?>
      <a href="admin.php" class="text-blue-600 dark:text-white hover:text-blue-400">
        <i class="fas fa-cogs mr-1"></i> Admin Panel
      </a>
    <?php endif; ?>
    <a href="../logout.php" class="text-blue-600 dark:text-white hover:text-blue-400">
      <i class="fas fa-sign-out-alt mr-1"></i> Logout
    </a>
    <!-- Theme Toggle Button -->
    <button id="themeToggle" class="text-xl text-gray-700 dark:text-yellow-300 hover:text-yellow-500">
      <i class="fas fa-moon"></i>
    </button>
  </div>
</nav>

<div class="p-8 bg-gray-100 min-h-screen">
    <h2 class="text-3xl font-bold text-blue-700 mb-6">Manage Users</h2>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="mb-4 px-4 py-2 rounded text-white <?= $_SESSION['flash']['type'] === 'success' ? 'bg-green-500' : 'bg-red-500' ?>">
            <?= $_SESSION['flash']['message'] ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-lg shadow text-sm">
            <thead class="bg-blue-100 text-blue-700">
                <tr>
                    <th class="py-2 px-4">ID</th>
                    <th class="py-2 px-4">Username</th>
                    <th class="py-2 px-4">Email</th>
                    <th class="py-2 px-4">Role</th>
                    <th class="py-2 px-4">Joined</th>
                    <th class="py-2 px-4">Status</th>
                    <th class="py-2 px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $user_result->fetch_assoc()): ?>
                    <tr class="border-t hover:bg-gray-50">
                        <td class="py-2 px-4"><?= $user['id'] ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($user['username']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($user['role']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($user['created_at']) ?></td>
                        <td class="py-2 px-4"><?= $user['status'] === 'blocked' ? 'Blocked' : 'Active' ?></td>
                        <td class="py-2 px-4 space-x-2">
                            <a href="view-user.php?id=<?= $user['id'] ?>" class="text-blue-600 hover:underline">View</a>
                            <a href="edit-user.php?id=<?= $user['id'] ?>" class="text-yellow-600 hover:underline">Edit</a>
                            <a href="delete-user.php?id=<?= $user['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Are you sure to delete this user?')">Delete</a>
                            <a href="toggle-status.php?id=<?= $user['id'] ?>" class="<?= $user['status'] === 'active' ? 'text-red-600' : 'text-green-600' ?> hover:underline">
                                <?= $user['status'] === 'active' ? 'Block' : 'Unblock' ?>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 flex justify-center space-x-2">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="manage-users.php?page=<?= $i ?>" class="px-3 py-1 rounded <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-200' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
</div>


<!-- Theme Toggle Script -->
<script>
  const themeToggle = document.getElementById('themeToggle');
  const htmlElement = document.documentElement;

  function applyTheme(theme) {
    if (theme === 'dark') {
      htmlElement.classList.add('dark');
      themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    } else {
      htmlElement.classList.remove('dark');
      themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
    }
  }

  themeToggle.addEventListener('click', () => {
    const newTheme = htmlElement.classList.contains('dark') ? 'light' : 'dark';
    localStorage.setItem('theme', newTheme);
    applyTheme(newTheme);
  });

  window.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme') || 'light';
    applyTheme(savedTheme);
  });
</script>

<!-- Footer -->
<footer class="bg-white dark:bg-gray-800 text-center py-4 mt-10 shadow-inner">
  <p class="text-sm text-gray-500 dark:text-gray-300">© <?= date("Y") ?> JobTracker. All rights reserved.</p>
</footer>

</body>
</html>

