<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login-signup.php");
    exit();
}
include '../db.php';

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Fetch file path before deleting
    $stmt = $conn->prepare("SELECT resume FROM job_applications WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $resume_path = '';
    if ($row = $result->fetch_assoc()) {
        $resume_path = $row['resume'];
    }
    $stmt->close();

    // Delete from database
    $stmt = $conn->prepare("DELETE FROM job_applications WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    // Remove resume file
    if ($resume_path && file_exists("../" . $resume_path)) {
        unlink("../" . $resume_path);
    }

    header("Location: job-applications.php");
    exit();
}

// Fetch job applications
$query = "SELECT ja.id, ja.applicant_name, ja.email, ja.message, ja.resume, j.title as job_title
          FROM job_applications ja
          JOIN jobs j ON ja.job_id = j.id
          ORDER BY ja.id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Job Applications</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body class="bg-gray-100 text-gray-900 p-6">
  <div class="max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold text-blue-700 mb-6">Job Applications</h1>

    <div class="bg-white p-4 rounded shadow overflow-auto">
      <table class="min-w-full table-auto">
        <thead>
          <tr class="bg-blue-600 text-white">
            <th class="px-4 py-2 text-left">#</th>
            <th class="px-4 py-2 text-left">Applicant Name</th>
            <th class="px-4 py-2 text-left">Email</th>
            <th class="px-4 py-2 text-left">Job Title</th>
            <th class="px-4 py-2 text-left">Message</th>
            <th class="px-4 py-2 text-left">Resume</th>
            <th class="px-4 py-2 text-left">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr class="border-t">
                <td class="px-4 py-2"><?= $row['id'] ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($row['applicant_name']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($row['email']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($row['job_title']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($row['message']) ?></td>
                <td class="px-4 py-2">
                  <a href="../<?= $row['resume'] ?>" target="_blank" class="text-blue-600 hover:underline">View Resume</a>
                </td>
                <td class="px-4 py-2">
                  <a href="?delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this application?')" class="text-red-600 hover:underline">
                    <i class="fas fa-trash-alt"></i> Delete
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="text-center py-4 text-gray-500">No applications found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
