

<?php session_start();
 if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: login-signup.php"); exit(); }
  include '../db.php'; 
  if (!isset($_GET['id'])) { echo "User ID is missing."; exit(); }
   $user_id = (int)$_GET['id']; 
   // Optional: prevent deleting self 
 if ($_SESSION['user_id'] == $user_id) { echo "You cannot delete your own account."; exit(); }
  // Step 1: Delete related job applications first 
  $deleteApplications = $conn->prepare("DELETE FROM job_applications WHERE user_id = ?");
   $deleteApplications->bind_param("i", $user_id); 
   $deleteApplications->execute();
    $deleteApplications->close(); // Step 2: Now delete the user 
    $deleteUser = $conn->prepare("DELETE FROM users WHERE id = ?"); 
    $deleteUser->bind_param("i", $user_id);
     $deleteUser->execute(); 
     $deleteUser->close(); // Redirect to manage-users page 
      header("Location: manage-users.php"); 
 exit(); ?>