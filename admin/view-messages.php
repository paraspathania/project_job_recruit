<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login-signup.php");
    exit();
}

include '../db.php';


// Handle delete
if (isset($_GET['delete'])) {
    $delId = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->bind_param("i", $delId);
    $stmt->execute();
    header("Location: view-messages.php?msg=deleted");
    exit();
}

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=contact_messages.csv');
    $output = fopen("php://output", "w");
    fputcsv($output, ['ID', 'Name', 'Email', 'Message', 'Created At']);

    $csvResult = $conn->query("SELECT id, name, email, message, created_at FROM contact_messages ORDER BY created_at DESC");
    while ($row = $csvResult->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// Pagination setup
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search/filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchSql = '';
$params = [];
$types = '';

if ($search) {
    $searchSql = "WHERE name LIKE ? OR email LIKE ?";
    $searchTerm = "%" . $search . "%";
    $params = [$searchTerm, $searchTerm];
    $types = "ss";
}

// Count total messages
$countQuery = "SELECT COUNT(*) as total FROM contact_messages $searchSql";
$stmtCount = $conn->prepare($countQuery);
if ($params) {
    $stmtCount->bind_param($types, ...$params);
}
$stmtCount->execute();
$countResult = $stmtCount->get_result()->fetch_assoc();
$totalMessages = $countResult['total'];
$totalPages = ceil($totalMessages / $limit);

// Fetch paginated messages
$query = "SELECT id, name, email, message, created_at FROM contact_messages $searchSql ORDER BY created_at DESC LIMIT ?, ?";
$stmt = $conn->prepare($query);
if ($params) {
    $types .= "ii";
    $params[] = $offset;
    $params[] = $limit;
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $offset, $limit);
}
$stmt->execute();
$result = $stmt->get_result();
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


<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-blue-700">Contact Messages</h2>
        <a href="?export=csv" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Export CSV</a>
    </div>

    <!-- Search Form -->
    <form method="GET" class="mb-4 flex space-x-2">
        <input type="text" name="search" placeholder="Search by name or email"
               value="<?= htmlspecialchars($search) ?>"
               class="px-3 py-2 border rounded w-full max-w-md" />
        <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Search</button>
    </form>

    <!-- Messages Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border text-sm">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="py-2 px-4 border">Name</th>
                    <th class="py-2 px-4 border">Email</th>
                    <th class="py-2 px-4 border">Message</th>
                    <th class="py-2 px-4 border">Received At</th>
                    <th class="py-2 px-4 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="py-2 px-4 border"><?= htmlspecialchars($row['name']) ?></td>
                            <td class="py-2 px-4 border"><?= htmlspecialchars($row['email']) ?></td>
                            <td class="py-2 px-4 border"><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                            <td class="py-2 px-4 border"><?= $row['created_at'] ?></td>
                            <td class="py-2 px-4 border space-x-2">
                                <a href="mailto:<?= htmlspecialchars($row['email']) ?>"
                                   class="text-blue-600 hover:underline">Reply</a>
                                <a href="?delete=<?= $row['id'] ?>"
                                   onclick="return confirm('Are you sure you want to delete this message?')"
                                   class="text-red-600 hover:underline">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="py-4 px-4 text-center text-gray-500">No messages found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 flex justify-center space-x-2">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"
               class="px-3 py-1 rounded <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' ?>">
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

