<?php
include 'db.php';

if (!isset($_GET['id'])) {
    echo "Job ID not specified.";
    exit;
}

$job_id = intval($_GET['id']);
$sql = "SELECT * FROM jobs WHERE id = $job_id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo "Job not found.";
    exit;
}

$job = $result->fetch_assoc();

// Graceful fallbacks
$company = $job['company_name'] ?? 'Not specified';
$experience = $job['experience'] ?? 'Not specified';
$salary = $job['salary'] ?? '₹25,000/month';  // Default if null
$skills = $job['skills'] ?? 'Not specified';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($job['title']); ?> - Job Details</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config = { darkMode: 'class' }</script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-100 text-gray-800">

  <!-- Navbar -->
  <nav class="bg-white shadow px-6 py-4 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-blue-600">🚀 Job Application Tracker</h1>
    <div class="space-x-6">
      <a href="home.php" class="text-blue-600 hover:underline">Home</a>
      <a href="about.php" class="hover:underline">About</a>
      <a href="contact.php" class="hover:underline">Contact</a>
      <a href="login.php" class="hover:underline">Login</a>
    </div>
  </nav>

  <!-- Job Details Card -->
  <main class="max-w-4xl mx-auto mt-10 p-8 bg-white rounded-2xl shadow-xl">
    <h2 class="text-4xl font-extrabold text-blue-700 mb-2"><?php echo htmlspecialchars($job['title']); ?></h2>
    <p class="text-sm text-gray-500 mb-4"><i class="fas fa-calendar-alt mr-1"></i> Deadline: <?= htmlspecialchars($job['deadline']) ?></p>

    <div class="grid md:grid-cols-2 gap-8">
      <div class="space-y-3">
        <p><i class="fas fa-building text-blue-500 mr-2"></i><strong>Company:</strong> <?= htmlspecialchars($company) ?></p>
        <p><i class="fas fa-map-marker-alt text-blue-500 mr-2"></i><strong>Location:</strong> <?= htmlspecialchars($job['location']) ?></p>
        <p><i class="fas fa-briefcase text-blue-500 mr-2"></i><strong>Experience:</strong> <?= htmlspecialchars($experience) ?></p>
        <p><i class="fas fa-money-bill-wave text-blue-500 mr-2"></i><strong>Salary:</strong> <?= htmlspecialchars($salary) ?></p>
        <p><i class="fas fa-code text-blue-500 mr-2"></i><strong>Skills:</strong> <?= htmlspecialchars($skills) ?></p>
      </div>

      <div>
        <h3 class="text-xl font-semibold text-blue-600 mb-2"><i class="fas fa-info-circle mr-1"></i> Job Description</h3>
        <p class="text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($job['description'])) ?></p>
      </div>
    </div>

    <div class="mt-8 flex justify-between items-center">
      <a href="home.php" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">← Back</a>
      <a href="apply.php?id=<?= $job['id'] ?>" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700"><i class="fas fa-paper-plane mr-2"></i>Apply Now</a>
    </div>
  </main>

  <footer class="text-center py-6 text-sm text-gray-500 mt-12">
    © <?= date("Y") ?> JobTracker. All rights reserved.
  </footer>

</body>
</html>
