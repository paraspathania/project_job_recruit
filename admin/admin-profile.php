<?php
session_start();
include '../db.php';

// Check if the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login-signup.php");
    exit();
}

// Fetch admin details from the database
$admin_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Update admin profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input data
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } else {
        // Prepared statement to update admin details
        $update_sql = "UPDATE users SET name = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('ssi', $name, $email, $admin_id);

        if ($stmt->execute()) {
            $message = "Profile updated successfully.";
        } else {
            $message = "Error updating profile.";
        }
    }
}

// Fetch all users
$users_sql = "SELECT * FROM users WHERE role != 'admin'"; // Exclude admin users
$users_result = $conn->query($users_sql);

// Handle user deletion
if (isset($_GET['delete_user_id'])) {
    $delete_user_id = $_GET['delete_user_id'];

    // Check if the user to delete is not the logged-in admin
    if ($delete_user_id != $admin_id) {
        // Prepared statement to delete user
        $delete_sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param('i', $delete_user_id);

        if ($stmt->execute()) {
            $message = "User deleted successfully.";
        } else {
            $message = "Error deleting user.";
        }
    } else {
        $message = "You cannot delete your own account.";
    }
}
?>

<!-- Admin Profile Form (unchanged) -->
<?php include '../header.php'; ?>

<div class="max-w-3xl mx-auto mt-10 bg-white dark:bg-gray-800 p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4 text-blue-700 dark:text-white">Admin Profile</h1>

    <?php if (isset($message)): ?>
        <div class="text-center text-green-600"><?= htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form action="admin-profile.php" method="POST">
        <div class="mb-4">
            <label for="name" class="block text-gray-700 dark:text-gray-300">Name</label>
            <input type="text" name="name" id="name" class="w-full p-2 border rounded dark:bg-gray-900 dark:text-white" value="<?= htmlspecialchars($admin['name']); ?>" required>
        </div>
        <div class="mb-4">
            <label for="email" class="block text-gray-700 dark:text-gray-300">Email</label>
            <input type="email" name="email" id="email" class="w-full p-2 border rounded dark:bg-gray-900 dark:text-white" value="<?= htmlspecialchars($admin['email']); ?>" required>
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Update Profile</button>
    </form>
</div>

<!-- User Management Section (unchanged) -->
<div class="max-w-3xl mx-auto mt-10 bg-white dark:bg-gray-800 p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4 text-blue-700 dark:text-white">Manage Users</h2>
    <table class="min-w-full border-collapse border border-gray-300">
        <thead>
            <tr>
                <th class="px-4 py-2 border border-gray-300">Name</th>
                <th class="px-4 py-2 border border-gray-300">Email</th>
                <th class="px-4 py-2 border border-gray-300">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $users_result->fetch_assoc()): ?>
                <tr>
                    <td class="px-4 py-2 border border-gray-300"><?= htmlspecialchars($user['name']); ?></td>
                    <td class="px-4 py-2 border border-gray-300"><?= htmlspecialchars($user['email']); ?></td>
                    <td class="px-4 py-2 border border-gray-300">
                        <a href="edit-user.php?id=<?= $user['id']; ?>" class="text-blue-600 hover:text-blue-500">Edit</a> | 
                        <a href="admin-profile.php?delete_user_id=<?= $user['id']; ?>" class="text-red-600 hover:text-red-500" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../footer.php'; ?>