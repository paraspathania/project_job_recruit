<?php
include 'db.php';

$job_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$job = null;
$success = false;
$error = '';

if ($job_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $job = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['resume']['tmp_name'];
        $fileName = basename($_FILES['resume']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExt, ['doc', 'docx'])) {
            $uploadPath = 'uploads/' . time() . '_' . $fileName;
            move_uploaded_file($fileTmp, $uploadPath);

            $stmt = $conn->prepare("INSERT INTO job_applications (job_id, applicant_name, email, resume, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $job_id, $name, $email, $uploadPath, $message);
            $stmt->execute();
            $stmt->close();
            $success = true;
        } else {
            $error = "Only .doc and .docx files are allowed.";
        }
    } else {
        $error = "Please upload a resume.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Apply for Job</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 to-white min-h-screen flex items-center justify-center px-4">
  <div class="max-w-2xl w-full bg-white p-8 rounded-2xl shadow-2xl transition-all duration-300">
    <?php if ($job): ?>
      <h2 class="text-3xl font-bold text-blue-700 mb-6">Apply for: <?= htmlspecialchars($job['title']) ?></h2>

      <?php if ($success): ?>
        <div class="bg-green-100 text-green-800 p-4 rounded mb-6 text-sm font-semibold shadow-inner">
          ✅ Application submitted successfully!
        </div>
        <script>
          setTimeout(() => {
            window.location.href = 'home.php';
          }, 5000);
        </script>
      <?php elseif ($error): ?>
        <div class="bg-red-100 text-red-800 p-4 rounded mb-6 text-sm font-semibold shadow-inner">
          ❌ <?= $error ?>
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
          <input type="text" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
          <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Upload Resume (.doc/.docx)</label>
          <input type="file" name="resume" accept=".doc,.docx" required class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Message (Optional)</label>
          <textarea name="message" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-200"></textarea>
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition">Submit Application</button>
      </form>
    <?php else: ?>
      <p class="text-red-600 font-semibold text-lg">Invalid Job ID.</p>
    <?php endif; ?>
  </div>
</body>
</html>
