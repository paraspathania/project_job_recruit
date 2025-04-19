<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login-signup.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['resume'];
        $fileTmp = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileName = $file['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExts = ['pdf', 'doc', 'docx', 'csv'];
        if (!in_array($fileExt, $allowedExts)) {
            header("Location: profile.php?upload=invalidtype");
            exit();
        }

        if ($fileSize > 5 * 1024 * 1024) { // 5MB max
            header("Location: profile.php?upload=toolarge");
            exit();
        }

        // Upload directory
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Save the file using user ID
        $newFilename = $uploadDir . $_SESSION['user_id'] . '.' . $fileExt;

        // Remove previous file if it exists
        foreach ($allowedExts as $ext) {
            $oldFile = $uploadDir . $_SESSION['user_id'] . '.' . $ext;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        // Move uploaded file
        if (move_uploaded_file($fileTmp, $newFilename)) {
            header("Location: profile.php?upload=success");
            exit();
        } else {
            header("Location: profile.php?upload=error");
            exit();
        }
    } else {
        header("Location: profile.php?upload=error");
        exit();
    }
} else {
    header("Location: profile.php");
    exit();
}
?>
