<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login-signup.php");
    exit();
}
include '../db.php';

// Total non-admin users
$user_result = $conn->query("SELECT COUNT(*) as total_users FROM users WHERE role != 'admin'");
$user_data = $user_result->fetch_assoc();
$total_users = $user_data['total_users'] ?? 0;

// Total contact messages
$message_result = $conn->query("SELECT COUNT(*) as total_messages FROM contact_messages");
$message_data = $message_result->fetch_assoc();
$total_messages = $message_data['total_messages'] ?? 0;

// Pending approvals
$approval_result = $conn->query("SELECT COUNT(*) as total_pending FROM job_applications WHERE status = 'pending'");
$approval_data = $approval_result->fetch_assoc();
$pending_approvals = $approval_data['total_pending'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
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

<!-- Navbar -->
<nav class="bg-white dark:bg-gray-800 shadow-md px-6 py-4 flex justify-between items-center">
  <div class="text-2xl font-bold text-blue-600 dark:text-white">JobTracker</div>
  <div class="flex items-center space-x-6">
    <a href="../home.php" class="text-blue-600 dark:text-white hover:text-blue-400">Home</a>
    <a href="../about.php" class="hover:text-blue-500 dark:text-white">About</a>
    <a href="../contact.php" class="hover:text-blue-500 dark:text-white">Contact</a>
    <a href="admin.php" class="text-blue-600 dark:text-white hover:text-blue-400">
      <i class="fas fa-cogs mr-1"></i> Admin Panel
    </a>
    <a href="../logout.php" class="text-blue-600 dark:text-white hover:text-blue-400">
      <i class="fas fa-sign-out-alt mr-1"></i> Logout
    </a>
    <button id="themeToggle" class="text-xl text-gray-700 dark:text-yellow-300 hover:text-yellow-500">
      <i class="fas fa-moon"></i>
    </button>
  </div>
</nav>

<div class="flex min-h-screen">
  <!-- Sidebar -->
  <div class="w-64 bg-blue-600 text-white p-6">
    <h2 class="text-2xl font-bold mb-6">Admin Panel</h2>
    <ul class="space-y-4">
      <li><a href="admin.php" class="flex items-center hover:bg-blue-500 p-2 rounded"><i class="fas fa-tachometer-alt mr-3"></i> Dashboard</a></li>
      <li><a href="manage-users.php" class="flex items-center hover:bg-blue-500 p-2 rounded"><i class="fas fa-users mr-3"></i> Manage Users</a></li>
      <li><a href="view-messages.php" class="flex items-center hover:bg-blue-500 p-2 rounded"><i class="fas fa-envelope mr-3"></i> View Messages</a></li>
      <li><a href="job-applications.php" class="flex items-center hover:bg-blue-500 p-2 rounded"><i class="fas fa-briefcase mr-3"></i> Job Applications</a></li>
      <li><a href="list-job.php" class="flex items-center hover:bg-blue-500 p-2 rounded"><i class="fas fa-list mr-3"></i> Jobs List</a></li>
      <li><a href="add-job.php" class="flex items-center hover:bg-blue-500 p-2 rounded"><i class="fas fa-plus mr-3"></i> Add Job</a></li>
      <li><a href="../logout.php" class="flex items-center hover:bg-blue-500 p-2 rounded"><i class="fas fa-sign-out-alt mr-3"></i> Logout</a></li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="flex-1 p-8">
    <h1 class="text-3xl font-bold text-blue-700 dark:text-white mb-6">Admin Dashboard</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold text-blue-700 dark:text-white">Total Users</h3>
        <p class="text-2xl font-bold"><?= $total_users ?></p>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold text-blue-700 dark:text-white">Messages</h3>
        <p class="text-2xl font-bold"><?= $total_messages ?></p>
      </div>
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold text-blue-700 dark:text-white">Pending Approvals</h3>
        <p class="text-2xl font-bold"><?= $pending_approvals ?></p>
      </div>
    </div>

    <!-- Chart -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
      <h2 class="text-xl font-semibold text-blue-700 dark:text-white mb-4">User Stats</h2>
      <canvas id="userChart" height="120"></canvas>
    </div>
  </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('userChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Users', 'Messages', 'Approvals'],
      datasets: [{
        label: 'Dashboard Stats',
        data: [<?= $total_users ?>, <?= $total_messages ?>, <?= $pending_approvals ?>],
        backgroundColor: ['#3B82F6', '#10B981', '#F59E0B'],
        borderRadius: 5
      }]
    },
    options: {
      scales: { y: { beginAtZero: true } }
    }
  });
</script>

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
