 <?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login-signup.php");
    exit();
}
include '../db.php';

$result = $conn->query("SELECT * FROM jobs ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Jobs List - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
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

<!-- Main Content -->
<div class="max-w-7xl mx-auto bg-white p-8 mt-8 rounded-xl shadow-md dark:bg-gray-800">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-blue-600 dark:text-white">📋 All Jobs</h1>
        <a href="add-job.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-500 transition">
            ➕ Add Job
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left border dark:border-gray-600">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">Company</th>
                    <th class="px-4 py-2">Location</th>
                    <th class="px-4 py-2">Salary</th>
                    <th class="px-4 py-2">Experience</th>
                    <th class="px-4 py-2">Category</th>
                    <th class="px-4 py-2">Deadline</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
                    <tr class="border-t hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-4 py-2"><?= $i++ ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['title']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['company_name']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['location']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['salary']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['experience']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['category']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['deadline']) ?></td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <a href="edit-job.php?id=<?= $row['id'] ?>" class="text-blue-600 hover:underline mr-3">Edit</a>
                            <a href="delete-job.php?id=<?= $row['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Delete this job?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Theme Toggle Script -->
<script>
const themeToggle = document.getElementById('themeToggle');
const html = document.documentElement;
function applyTheme(theme) {
    theme === 'dark' ? html.classList.add('dark') : html.classList.remove('dark');
    themeToggle.innerHTML = theme === 'dark' ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
}
themeToggle.addEventListener('click', () => {
    const newTheme = html.classList.contains('dark') ? 'light' : 'dark';
    localStorage.setItem('theme', newTheme);
    applyTheme(newTheme);
});
window.addEventListener('DOMContentLoaded', () => {
    applyTheme(localStorage.getItem('theme') || 'light');
});
</script>

<!-- Footer -->
<footer class="bg-white dark:bg-gray-800 text-center py-4 mt-10 shadow-inner">
    <p class="text-sm text-gray-500 dark:text-gray-300">© <?= date("Y") ?> JobTracker. All rights reserved.</p>
</footer>

</body>
</html>
