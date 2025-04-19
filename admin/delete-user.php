<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login-signup.php");
    exit();
}

include '../db.php';

if (!isset($_GET['id'])) {
    echo "User ID is missing.";
    exit();
}

$user_id = (int)$_GET['id'];

// Optional: prevent deleting self or admin
if ($_SESSION['user_id'] == $user_id) {
    echo "You cannot delete your own account.";
    exit();
}

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

header("Location: manage-users.php");
exit();
?>
