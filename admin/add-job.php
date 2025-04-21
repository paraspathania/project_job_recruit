<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login-signup.php");
    exit();
}
include '../db.php';


$title = $description = $location = $category = $deadline = $company_name = $salary = $experience = $skills = '';
$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $category = trim($_POST['category']);
    $deadline = trim($_POST['deadline']);
    $company_name = trim($_POST['company']);
    $salary = trim($_POST['salary']);
    $experience = trim($_POST['experience']);
    $skills = trim($_POST['skills']);

    if (!$title || !$description || !$location || !$category || !$deadline || !$company_name) {
        $errors[] = "All required fields must be filled out.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO jobs (title, description, location, category, deadline, company_name, salary, experience, skills, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssssss", $title, $description, $location, $category, $deadline, $company_name, $salary, $experience, $skills);
        if ($stmt->execute()) {
            $success = true;
            $title = $description = $location = $category = $deadline = $company_name = $salary = $experience = $skills = '';
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}
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

<!-- Navbar -->
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

<!-- Form Card -->
<div class="max-w-4xl mx-auto mt-10 bg-white dark:bg-gray-800 shadow-xl rounded-2xl p-8">
  <h1 class="text-3xl font-semibold mb-6 text-blue-600 dark:text-blue-400">➕ Add New Job</h1>

  <?php if ($success): ?>
    <div class="bg-green-100 text-green-800 px-4 py-3 rounded-lg mb-4 border border-green-300">
      ✅ Job added successfully!
    </div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="bg-red-100 text-red-800 px-4 py-3 rounded-lg mb-4 border border-red-300">
      <ul class="list-disc pl-5 space-y-1">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <?php
      function input($name, $label, $type = 'text', $required = false, $value = '') {
        $req = $required ? 'required' : '';
        return "
        <div class='flex flex-col'>
          <label class='font-medium mb-1'>$label" . ($required ? "*" : "") . "</label>
          <input name='$name' type='$type' value='" . htmlspecialchars($value) . "' class='border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-blue-300 dark:bg-gray-700 dark:border-gray-600' $req />
        </div>";
      }
    ?>
    <?= input('title', 'Title', 'text', true, $title) ?>
    <?= input('location', 'Location', 'text', true, $location) ?>
    <?= input('category', 'Category', 'text', true, $category) ?>
    <?= input('deadline', 'Deadline', 'date', true, $deadline) ?>
    <?= input('company', 'Company', 'text', true, $company_name) ?>
    <?= input('salary', 'Salary', 'text', false, $salary) ?>
    <?= input('experience', 'Experience', 'text', false, $experience) ?>
    <?= input('skills', 'Skills', 'text', false, $skills) ?>

    <div class="col-span-full">
      <label class="font-medium mb-1">Description*</label>
      <textarea name="description" required rows="4" class="w-full border rounded px-3 py-2 dark:bg-gray-700 dark:border-gray-600"><?= htmlspecialchars($description) ?></textarea>
    </div>

    <div class="col-span-full flex justify-end">
      <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
        Create Job
      </button>
    </div>
  </form>
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
