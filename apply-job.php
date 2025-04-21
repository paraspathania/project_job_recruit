<?php
session_start();
include 'db.php'; // make sure this connects to your database

// Step 1: Check if user is logged in and job_id is sent
if (isset($_POST['job_id']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $job_id = $_POST['job_id'];

    // Step 2: Get job title and company from jobs table
    $stmt = $conn->prepare("SELECT title, company FROM jobs WHERE id = ?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $job = $result->fetch_assoc();

    if ($job) {
        $job_title = $job['title'];
        $company_name = $job['company'];

        // Step 3: Save application to job_applications table
        $insert = $conn->prepare("INSERT INTO job_applications (user_id, job_title, company_name) VALUES (?, ?, ?)");
        $insert->bind_param("iss", $user_id, $job_title, $company_name);
        $insert->execute();
    }

    // Step 4: Redirect back to profile or job listing
    header("Location: profile.php");
    exit();
} else {
    echo "Something went wrong. Make sure you're logged in and clicked a valid apply button.";
}
?>
