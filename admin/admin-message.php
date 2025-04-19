<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login-signup.php");
    exit();
}

$result = $conn->query("SELECT * FROM contact_messages ORDER BY submitted_at DESC");
?>

<?php include '../header.php'; ?>

<div class="max-w-4xl mx-auto mt-10 bg-white p-6 rounded shadow">
  <h1 class="text-2xl font-bold mb-4 text-blue-700">Contact Messages</h1>
  <table class="w-full table-auto border border-gray-300">
    <thead>
      <tr class="bg-gray-100">
        <th class="border px-4 py-2">Name</th>
        <th class="border px-4 py-2">Email</th>
        <th class="border px-4 py-2">Message</th>
        <th class="border px-4 py-2">Date</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($msg = $result->fetch_assoc()): ?>
      <tr>
        <td class="border px-4 py-2"><?= htmlspecialchars($msg['name']); ?></td>
        <td class="border px-4 py-2"><?= htmlspecialchars($msg['email']); ?></td>
        <td class="border px-4 py-2"><?= htmlspecialchars($msg['message']); ?></td>
        <td class="border px-4 py-2"><?= $msg['submitted_at']; ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include '../footer.php'; ?>
