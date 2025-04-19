<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/header.php';
?>

<!-- Hero Banner -->
<section class="bg-blue-50 dark:bg-gray-800 py-20 text-center">
  <h1 class="text-4xl font-bold text-blue-900 dark:text-white mb-2">Get in Touch</h1>
  <p class="text-gray-700 dark:text-gray-300 text-lg">We’re here to help. Send us a message and we'll get back to you shortly!</p>
</section>

<!-- Contact Form -->
<section class="max-w-3xl mx-auto bg-white dark:bg-gray-900 p-8 rounded-xl shadow-lg -mt-16 relative z-10">
  <form action="send-message.php" method="POST" class="space-y-6">
    <div>
      <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Name</label>
      <input type="text" name="name" required class="w-full p-3 border rounded-lg dark:bg-gray-800 dark:text-white">
    </div>

    <div>
      <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Email</label>
      <input type="email" name="email" required class="w-full p-3 border rounded-lg dark:bg-gray-800 dark:text-white">
    </div>

    <div>
      <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Message</label>
      <textarea name="message" rows="5" required class="w-full p-3 border rounded-lg dark:bg-gray-800 dark:text-white"></textarea>
    </div>

    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition">Send Message</button>
  </form>
</section>

<!-- Additional Contact Info -->
<section class="max-w-4xl mx-auto px-6 py-12 text-center">
  <h2 class="text-2xl font-bold mb-4 text-blue-700 dark:text-white">Other Ways to Reach Us</h2>
  <div class="flex justify-center gap-10 mt-6 text-gray-600 dark:text-gray-300">
    <div>
      <i class="fas fa-envelope text-xl mb-2"></i>
      <p>support@jobtracker.com</p>
    </div>
    <div>
      <i class="fas fa-phone-alt text-xl mb-2"></i>
      <p>+1 800 123 4567</p>
    </div>
    <div>
      <i class="fas fa-map-marker-alt text-xl mb-2"></i>
      <p>New York, NY</p>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>
