<?php
$host = 'localhost';
$db = 'job_tracker';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT name, email, position FROM applications ORDER BY id DESC");

$applications = array();
while ($row = $result->fetch_assoc()) {
  $applications[] = $row;
}

echo json_encode($applications);
?>
