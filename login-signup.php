<?php
session_start();
include 'db.php';

$error = "";
$success = "";

// Signup Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required for sign up.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$hashed_password', 'user')";
        if ($conn->query($sql) === TRUE) {
            $success = "Sign up successful! Please log in.";
        } else {
            $error = "Email already exists or something went wrong.";
        }
    }
}

// Login Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: home.php");
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login / Sign Up</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="bg-gray-100 text-gray-800 transition-colors duration-300">

<!-- Navigation -->
<nav class="bg-white dark:bg-gray-800 shadow p-4 flex justify-between items-center">
  <div class="text-xl font-bold text-blue-600 dark:text-white">JobTracker</div>
  <div class="space-x-4">
    <a href="home.php" class="text-blue-600 dark:text-blue-300 hover:underline">← Back to Home</a>
    <button onclick="toggleDarkMode()" class="ml-4">
      <i class="fas fa-moon text-gray-600 dark:text-yellow-300"></i>
    </button>
  </div>
</nav>

<!-- Form Card -->
<div class="max-w-md mx-auto mt-12 p-6 bg-white dark:bg-gray-800 shadow-lg rounded-lg transition-all duration-500">
  <?php if ($error): ?>
    <div class="bg-red-100 text-red-700 p-2 mb-4 rounded"><?= $error ?></div>
  <?php elseif ($success): ?>
    <div class="bg-green-100 text-green-700 p-2 mb-4 rounded"><?= $success ?></div>
  <?php endif; ?>

  <!-- Login Form -->
  <form method="POST" id="login-form" class="space-y-4 transition-all duration-300">
    <h2 class="text-2xl font-bold">Login</h2>
    <input type="email" name="email" class="w-full p-2 border rounded dark:bg-gray-700" placeholder="Email" required />
    <input type="password" name="password" class="w-full p-2 border rounded dark:bg-gray-700" placeholder="Password" required />
    <button type="submit" name="login" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">Login</button>

    <div class="flex items-center justify-between mt-4">
      <span class="text-sm">Don't have an account?</span>
      <button type="button" onclick="toggleForm('signup')" class="text-blue-600 hover:underline">Sign up</button>
    </div>
  </form>

  <!-- Signup Form -->
  <form method="POST" id="signup-form" class="space-y-4 hidden transition-all duration-300">
    <h2 class="text-2xl font-bold">Sign Up</h2>
    <input type="text" name="username" class="w-full p-2 border rounded dark:bg-gray-700" placeholder="Username" required />
    <input type="email" name="email" class="w-full p-2 border rounded dark:bg-gray-700" placeholder="Email" required />
    <input type="password" name="password" class="w-full p-2 border rounded dark:bg-gray-700" placeholder="Password" required />
    <button type="submit" name="signup" class="w-full py-2 bg-green-600 hover:bg-green-700 text-white rounded">Sign Up</button>

    <div class="flex items-center justify-between mt-4">
      <span class="text-sm">Already have an account?</span>
      <button type="button" onclick="toggleForm('login')" class="text-blue-600 hover:underline">Login</button>
    </div>
  </form>
</div>

<script>
function toggleForm(formType) {
  const login = document.getElementById('login-form');
  const signup = document.getElementById('signup-form');
  login.classList.toggle('hidden', formType === 'signup');
  signup.classList.toggle('hidden', formType === 'login');
}

function toggleDarkMode() {
  document.documentElement.classList.toggle('dark');
}
</script>
</body>
</html>
