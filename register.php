<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->query("SELECT * FROM users WHERE email = '$email'");
    if ($check->num_rows > 0) {
        echo "User already exists.";
    } else {
        $insert = $conn->query("INSERT INTO users (email, password) VALUES ('$email', '$password')");
        if ($insert) {
            echo "Registered successfully.";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}
?>
