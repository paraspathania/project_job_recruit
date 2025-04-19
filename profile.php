<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login-signup.php");
    exit();
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$query = "SELECT username, email, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['created_at'] = $user['created_at'];
} else {
    echo "User not found.";
    exit();
}
?>

<!-- // Show upload status alerts -->
<?php if (isset($_GET['upload'])): ?>
    <div class="max-w-4xl mx-auto mb-4" id="uploadMessage">
        <?php if ($_GET['upload'] === 'success'): ?>
            <div class="bg-green-100 text-green-800 p-3 rounded">✅ Resume uploaded successfully!</div>
        <?php elseif ($_GET['upload'] === 'invalidtype'): ?>
            <div class="bg-red-100 text-red-800 p-3 rounded">❌ Invalid file type. Please upload PDF, DOC, DOCX, or CSV.</div>
        <?php elseif ($_GET['upload'] === 'toolarge'): ?>
            <div class="bg-red-100 text-red-800 p-3 rounded">❌ File is too large. Maximum size is 5MB.</div>
        <?php elseif ($_GET['upload'] === 'error'): ?>
            <div class="bg-red-100 text-red-800 p-3 rounded">❌ Something went wrong during upload.</div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
// Resume file check
$resumeExists = false;
$resumeFilePath = '';
$resumeExtensions = ['pdf', 'doc', 'docx', 'csv'];
foreach ($resumeExtensions as $ext) {
    $filePath = "uploads/{$user_id}.$ext";
    if (file_exists($filePath)) {
        $resumeExists = true;
        $resumeFilePath = $filePath;
        break;
    }
}
?>

<?php include 'header.php'; ?>

<div class="bg-gray-100 dark:bg-gray-900 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <!-- Profile Header -->
    <div class="max-w-4xl mx-auto mb-10 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-extrabold text-blue-700 dark:text-white">Hello, <?= htmlspecialchars($_SESSION['username']) ?> 👋</h2>
                <p class="text-gray-600 dark:text-gray-400 text-sm mt-1">Welcome to your dashboard</p>
            </div>
            <a href="edit-profile.php" class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition duration-200 text-sm">
                Edit Profile
            </a>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Personal Info -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md transition-all duration-300 hover:shadow-lg">
            <h3 class="text-xl font-semibold text-blue-600 dark:text-blue-300 mb-4">👤 Personal Info</h3>
            <ul class="space-y-2 text-gray-700 dark:text-gray-300">
                <li><strong>Email:</strong> <?= htmlspecialchars($_SESSION['email']) ?></li>
                <li><strong>Joined:</strong> <?= date("F d, Y", strtotime($_SESSION['created_at'])) ?></li>
                <li><strong>Status:</strong> <span class="text-green-500 font-semibold">Active</span></li>
            </ul>
        </div>

        <!-- Resume -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md transition-all duration-300 hover:shadow-lg">
            <h3 class="text-xl font-semibold text-blue-600 dark:text-blue-300 mb-4">📄 Resume</h3>
            <?php if ($resumeExists): ?>
                <a href="<?= $resumeFilePath ?>" target="_blank" class="text-blue-600 dark:text-blue-400 underline">View Resume</a>
            <?php else: ?>
                <p class="text-gray-600 dark:text-gray-400">No resume uploaded yet.</p>
            <?php endif; ?>
            <form id="resumeUploadForm" action="upload-resume.php" method="post" enctype="multipart/form-data" class="mt-4">
                <input type="file" name="resume" id="resumeFile" class="block mb-2" required>
                <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Upload</button>
            </form>
        </div>

        <!-- Applied Jobs -->
        <div class="md:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-md transition-all duration-300 hover:shadow-lg">
            <h3 class="text-xl font-semibold text-blue-600 dark:text-blue-300 mb-4">📌 Applied Jobs</h3>
            <ul class="space-y-3 text-gray-700 dark:text-gray-300">
                <li class="flex justify-between items-center bg-gray-100 dark:bg-gray-700 p-3 rounded-lg">
                    <span>Frontend Developer at XYZ</span>
                    <span class="text-sm text-green-600">Applied on Jan 5, 2025</span>
                </li>
                <li class="flex justify-between items-center bg-gray-100 dark:bg-gray-700 p-3 rounded-lg">
                    <span>Backend Developer at ABC</span>
                    <span class="text-sm text-green-600">Applied on Mar 22, 2025</span>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
    const form = document.getElementById('resumeUploadForm');
    const resumeExists = <?= $resumeExists ? 'true' : 'false' ?>;

    form.addEventListener('submit', function (e) {
        if (resumeExists) {
            const confirmReplace = confirm("A resume already exists. Do you want to replace it?");
            if (!confirmReplace) {
                e.preventDefault();
            }
        }
    });
    
    <script>
    // Hide the message after 5 seconds
    const uploadMessage = document.getElementById("uploadMessage");
    if (uploadMessage) {
        setTimeout(() => {
            uploadMessage.style.display = "none";
        }, 5000);
    }
</script>


<?php include 'footer.php'; ?>
