<?php
session_start();

// Only allow admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login-signup.php");
    exit();
}

// Check for a valid job ID in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: jobs-list.php?error=invalid_id");
    exit();
}

include '../db.php';

$job_id = intval($_GET['id']);

// Prepare and execute delete query
$stmt = $conn->prepare("DELETE FROM jobs WHERE id = ?");
$stmt->bind_param("i", $job_id);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header("Location: jobs-list.php?deleted=success");
    exit();
} else {
    $stmt->close();
    $conn->close();
    header("Location: jobs-list.php?error=delete_failed");
    exit();
}
?>
