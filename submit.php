<?php
$host = 'localhost';
$db = 'job_tracker';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$name = $_POST['name'];
$email = $_POST['email'];
$position = $_POST['position'];

$stmt = $conn->prepare("INSERT INTO applications (name, email, position) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $position);
$stmt->execute();
$stmt->close();
$conn->close();

echo "Application submitted successfully.";
?>
