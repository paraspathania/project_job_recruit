<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login-signup.php");
    exit();
}
include '../db.php';

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Filters
$filter_job = $_GET['filter_job'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';

// Build WHERE clause
$where = [];
$params = [];
$types = '';

if ($filter_job !== '') {
    $where[] = 'j.title = ?';
    $params[] = $filter_job;
    $types .= 's';
}
if ($filter_status !== '') {
    $where[] = 'ja.status = ?';
    $params[] = $filter_status;
    $types .= 's';
}



$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total count
$count_query = "SELECT COUNT(*) AS total FROM job_applications ja JOIN jobs j ON ja.job_id = j.id $where_clause";
$stmt = $conn->prepare($count_query);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$count_result = $stmt->get_result();
$total = $count_result->fetch_assoc()['total'];
$stmt->close();

$total_pages = ceil($total / $limit);

// Fetch data
$query = "SELECT ja.id, ja.applicant_name, ja.email, ja.message, ja.resume, ja.status, j.title AS job_title 
          FROM job_applications ja 
          JOIN jobs j ON ja.job_id = j.id 
          $where_clause 
          ORDER BY ja.id DESC 
          LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Job dropdown
$jobs_result = $conn->query("SELECT DISTINCT title FROM jobs");
$statuses = ['Approved', 'Waiting', 'Cancelled', 'Pending'];
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

<div class="max-w-7xl mx-auto">
  <h1 class="text-3xl font-bold text-blue-700 mb-6">Manage Job Applications</h1>

  <!-- Filters -->
  <form method="GET" class="mb-4 flex gap-4 flex-wrap">
    <select name="filter_job" class="border border-gray-300 rounded px-3 py-1">
      <option value="">All Jobs</option>
      <?php while ($job = $jobs_result->fetch_assoc()): ?>
        <option value="<?= htmlspecialchars($job['title']) ?>" <?= $filter_job === $job['title'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($job['title']) ?>
        </option>
      <?php endwhile; ?>
    </select>

    <select name="filter_status" class="border border-gray-300 rounded px-3 py-1">
      <option value="">All Statuses</option>
      <?php foreach ($statuses as $status): ?>
        <option value="<?= $status ?>" <?= $filter_status === $status ? 'selected' : '' ?>><?= $status ?></option>
      <?php endforeach; ?>
    </select>

    <button type="submit" class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700">Filter</button>
    <a href="job-applications.php" class="text-blue-600 px-2 py-1 hover:underline">Reset</a>
  </form>

  <!-- Table -->
  <div class="bg-white p-4 rounded shadow overflow-auto">
    <table class="min-w-full table-auto text-sm">
      <thead>
        <tr class="bg-blue-600 text-white">
          <th class="px-4 py-2 text-left">#</th>
          <th class="px-4 py-2 text-left">Applicant</th>
          <th class="px-4 py-2 text-left">Email</th>
          <th class="px-4 py-2 text-left">Job</th>
          <th class="px-4 py-2 text-left">Message</th>
          <th class="px-4 py-2 text-left">Resume</th>
          <th class="px-4 py-2 text-left">Status</th>
          <th class="px-4 py-2 text-left">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr class="border-t">
              <td class="px-4 py-2"><?= $row['id'] ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($row['applicant_name']) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($row['email']) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($row['job_title']) ?></td>
              <td class="px-4 py-2"><?= nl2br(htmlspecialchars($row['message'])) ?></td>
              <td class="px-4 py-2">
                <?php if (!empty($row['resume'])): ?>
                  <a href="../<?= $row['resume'] ?>" target="_blank" class="text-blue-600 underline">View</a>
                <?php else: ?>
                  <span class="text-gray-500">N/A</span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-2">
                <?php
                  $status_color = match ($row['status']) {
                    'Approved' => 'text-green-600 font-semibold',
                    'Waiting' => 'text-yellow-600 font-semibold',
                    'Cancelled' => 'text-red-600 font-semibold',
                    default => 'text-gray-600'
                  };
                ?>
                <span class="<?= $status_color ?>"><?= htmlspecialchars($row['status'] ?? 'Pending') ?></span>
              </td>
              <td class="px-4 py-2 space-x-2">
                <a href="?status_id=<?= $row['id'] ?>&new_status=Approved" class="text-green-700 hover:underline">Approve</a>
                <a href="?status_id=<?= $row['id'] ?>&new_status=Waiting" class="text-yellow-700 hover:underline">Wait</a>
                <a href="?status_id=<?= $row['id'] ?>&new_status=Cancelled" class="text-red-700 hover:underline">Cancel</a>
                <a href="?delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')" class="text-gray-600 hover:underline">Delete</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" class="text-center py-4 text-gray-500">No applications found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
      <div class="mt-4 flex justify-center space-x-2">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <a href="?page=<?= $i ?>&filter_job=<?= urlencode($filter_job) ?>&filter_status=<?= urlencode($filter_status) ?>"
             class="px-3 py-1 rounded <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
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
