<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login-signup.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage-users.php");
    exit();
}

$id = (int)$_GET['id'];

// Prevent self toggle
if ($id === $_SESSION['user_id']) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => "You can't change your own status."];
    header("Location: manage-users.php");
    exit();
}

$result = $conn->query("SELECT status FROM users WHERE id = $id");
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => "User not found."];
    header("Location: manage-users.php");
    exit();
}

$newStatus = $user['status'] === 'active' ? 'blocked' : 'active';

$stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
$stmt->bind_param("si", $newStatus, $id);
$stmt->execute();

$_SESSION['flash'] = ['type' => 'success', 'message' => "User status updated to $newStatus."];
header("Location: manage-users.php");
exit();
