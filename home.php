<?php
include 'db.php';
session_start();

$keyword = $_GET['keyword'] ?? '';
$location = $_GET['location'] ?? '';
$category = $_GET['category'] ?? '';

$sql = "SELECT * FROM jobs WHERE 1=1";
if ($keyword) $sql .= " AND title LIKE '%" . $conn->real_escape_string($keyword) . "%'";
if ($location) $sql .= " AND location LIKE '%" . $conn->real_escape_string($location) . "%'";
if ($category) $sql .= " AND category = '" . $conn->real_escape_string($category) . "'";
$sql .= " ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Job Application Tracker - Home</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
    }
  </script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
</head>
<body class="text-gray-800 transition-colors duration-300 bg-gradient-to-b from-blue-50 via-white to-blue-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">

<!-- Navbar -->
<nav class="bg-white dark:bg-gray-900 shadow-md px-6 py-4 flex justify-between items-center">
  <div class="text-2xl font-bold text-blue-600 dark:text-white">🚀 JobTracker</div>
  <div class="flex items-center space-x-6">
    <a href="home.php" class="text-blue-600 dark:text-white font-medium hover:text-blue-400">Home</a>
    <a href="about.php" class="hover:text-blue-500 dark:text-white">About</a>
    <a href="contact.php" class="hover:text-blue-500 dark:text-white">Contact</a>
    <?php if (isset($_SESSION['username'])): ?>
      <a href="profile.php" class="text-blue-600 dark:text-white hover:text-blue-400"><i class="fas fa-user-circle mr-1"></i> Profile</a>
      <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="admin/admin.php" class="text-blue-600 dark:text-white hover:text-blue-400"><i class="fas fa-cogs mr-1"></i> Admin Panel</a>
      <?php endif; ?>
      <a href="logout.php" class="text-blue-600 dark:text-white hover:text-blue-400"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
    <?php else: ?>
      <a href="login-signup.php" class="text-blue-600 dark:text-white hover:text-blue-400"><i class="fas fa-sign-in-alt mr-1"></i> Login/Sign Up</a>
    <?php endif; ?>
    <button onclick="toggleTheme()" id="themeToggle" class="text-xl text-gray-800 dark:text-white hover:text-yellow-400">
      <i class="fas fa-moon"></i>
    </button>
  </div>
</nav>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-blue-400 to-indigo-600 dark:from-gray-700 dark:to-gray-900 py-24 text-center text-white">
  <div class="max-w-4xl mx-auto">
    <h1 class="text-5xl font-extrabold drop-shadow-xl mb-3">Find Your Dream Job</h1>
    <p class="text-lg text-blue-100 dark:text-gray-300">Explore top job openings and apply with a single click!</p>
  </div>
</section>

<!-- Search Bar -->
<form method="GET" action="home.php" class="max-w-6xl mx-auto -mt-12 relative z-10 px-4">
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 grid grid-cols-1 md:grid-cols-4 gap-4">
    <input type="text" name="keyword" placeholder="Search job title..." class="p-2 rounded border w-full" value="<?php echo htmlspecialchars($keyword); ?>">
    <input type="text" name="location" placeholder="Location..." class="p-2 rounded border w-full" value="<?php echo htmlspecialchars($location); ?>">
    <select name="category" class="p-2 rounded border w-full">
      <option value="">All Categories</option>
      <option value="IT" <?php if ($category === 'IT') echo 'selected'; ?>>IT</option>
      <option value="Marketing" <?php if ($category === 'Marketing') echo 'selected'; ?>>Marketing</option>
      <option value="Finance" <?php if ($category === 'Finance') echo 'selected'; ?>>Finance</option>
    </select>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Search</button>
  </div>
</form>

<!-- Job Listings -->
<main class="max-w-6xl mx-auto px-4 py-14">
  <h2 class="text-3xl font-bold mb-8 text-center text-blue-800 dark:text-blue-200">✨ Featured Job Openings</h2>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php
    if ($result->num_rows > 0) {
      while ($job = $result->fetch_assoc()) {
        echo "
        <div class='bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6 rounded-2xl shadow-lg hover:shadow-2xl transition duration-300 relative'>
          <div class='absolute top-4 right-4 bg-blue-100 dark:bg-blue-700 text-blue-600 dark:text-white px-3 py-1 text-xs rounded-full'>Featured</div>
          <h3 class='text-xl font-bold text-blue-700 dark:text-blue-300 mb-2'>" . htmlspecialchars($job['title']) . "</h3>
          <p class='text-gray-700 dark:text-gray-300 mb-2'>" . substr(htmlspecialchars($job['description']), 0, 100) . "...</p>
          <p class='text-sm text-gray-500 dark:text-gray-400'>📍 " . htmlspecialchars($job['location']) . "</p>
          <p class='text-sm text-gray-500 dark:text-gray-400 mb-3'>🗓 Deadline: " . htmlspecialchars($job['deadline']) . "</p>
          <div class='flex justify-between items-center'>
            <a href='apply.php?id={$job['id']}' class='bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm'>Apply Now</a>
            <a href='job-details.php?id={$job['id']}' class='text-blue-600 dark:text-blue-400 hover:underline text-sm'>View Details</a>
          </div>
        </div>
        ";
      }
    } else {
      echo "<p class='text-center text-gray-600 dark:text-white col-span-full'>No job listings found.</p>";
    }
    ?>
  </div>
</main>

<script>
  function toggleTheme() {
    const isDark = document.body.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    document.getElementById('themeToggle').innerHTML = isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
  }
  window.addEventListener('DOMContentLoaded', () => {
    const theme = localStorage.getItem('theme') || (new Date().getHours() >= 18 || new Date().getHours() <= 6 ? 'dark' : 'light');
    if (theme === 'dark') {
      document.body.classList.add('dark');
      document.getElementById('themeToggle').innerHTML = '<i class="fas fa-sun"></i>';
    } else {
      document.getElementById('themeToggle').innerHTML = '<i class="fas fa-moon"></i>';
    }
  });
</script>

<footer class="bg-white dark:bg-gray-900 text-center py-6 border-t border-gray-200 dark:border-gray-700">
  <p class="text-sm text-gray-500 dark:text-gray-400">© <?= date("Y") ?> JobTracker. All rights reserved.</p>
</footer>

</body>
</html>
