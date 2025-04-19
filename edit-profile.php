<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login-signup.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = $_POST['username'];
    $new_email = $_POST['email'];
    $new_password = $_POST['password'];

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET username=?, email=?, password=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $new_username, $new_email, $hashed_password, $user_id);
    } else {
        $query = "UPDATE users SET username=?, email=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $new_username, $new_email, $user_id);
    }

    if ($stmt->execute()) {
        $_SESSION['username'] = $new_username;
        $_SESSION['email'] = $new_email;
        header("Location: profile.php");
        exit();
    } else {
        $error = "Failed to update profile.";
    }
}

// Fetch current user data
$query = "SELECT username, email FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<?php include 'header.php'; ?>

<div class="bg-gray-100 dark:bg-gray-900 min-h-screen py-12 px-6">
    <div class="max-w-xl mx-auto bg-white dark:bg-gray-800 rounded-2xl shadow-md p-8 transition-all">
        <h2 class="text-3xl font-bold text-blue-700 dark:text-white mb-6">✏️ Edit Profile</h2>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="edit-profile.php" class="space-y-6">
            <!-- Username -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring focus:ring-blue-300 transition">
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring focus:ring-blue-300 transition">
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Password (optional)</label>
                <input type="password" name="password" placeholder="••••••••"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-800 dark:text-gray-200 focus:ring focus:ring-blue-300 transition">
                <p class="text-xs text-gray-500 mt-1">Leave blank if you don’t want to change your password.</p>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-between mt-6">
                <a href="profile.php" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">← Back to Profile</a>
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition shadow">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
