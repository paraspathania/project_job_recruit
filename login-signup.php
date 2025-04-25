<?php
session_start();
include 'db.php';

$signupError = $signupSuccess = "";
$loginError = "";

// --- Signup Logic ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $signupError = "All fields are required.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,}$/', $username)) {
        $signupError = "Username must be at least 3 characters and contain only letters, numbers, or underscores.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $signupError = "Invalid email format.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?#&])[A-Za-z\d@$!%*?#&]{8,}$/', $password)) {
        $signupError = "Password must be at least 8 characters, include uppercase, lowercase, number, and special character.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $signupError = "Email already exists. Try logging in.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            if ($stmt->execute()) {
                $signupSuccess = "Sign up successful! You can now log in.";
            } else {
                $signupError = "Something went wrong. Please try again.";
            }
        }
        $stmt->close();
    }
}

// --- Login Logic ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $loginError = "Both email and password are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $loginError = "Invalid email format.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header("Location: home.php");
                exit();
            } else {
                $loginError = "Incorrect password!";
            }
        } else {
            $loginError = "No account found with that email.";
        }
        $stmt->close();
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
  <script>tailwind.config = { darkMode: 'class' }</script>
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

  <!-- Login Form -->
  <form method="POST" id="login-form" class="space-y-4 transition-all duration-300">
    <h2 class="text-2xl font-bold">Login</h2>

    <?php if ($loginError): ?>
      <div class="bg-red-100 text-red-700 p-2 mb-2 rounded"><?= $loginError ?></div>
    <?php endif; ?>

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

    <?php if ($signupError): ?>
      <div class="bg-red-100 text-red-700 p-2 mb-2 rounded"><?= $signupError ?></div>
    <?php elseif ($signupSuccess): ?>
      <div class="bg-green-100 text-green-700 p-2 mb-2 rounded"><?= $signupSuccess ?></div>
    <?php endif; ?>

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
  document.getElementById('login-form').classList.toggle('hidden', formType === 'signup');
  document.getElementById('signup-form').classList.toggle('hidden', formType === 'login');
}

function toggleDarkMode() {
  document.documentElement.classList.toggle('dark');
}
</script>
</body>
</html>
