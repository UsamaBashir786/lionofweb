<?php
// Include necessary files
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Page Not Found - NewsHub</title>
  <link rel="shortcut icon" href="assets/images/logo.png" type="image/x-icon">
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-gray-100 font-sans">
  <?php include 'includes/navbar.php'; ?>

  <main class="container mx-auto px-4 py-12">
    <div class="max-w-lg mx-auto text-center">
      <h1 class="text-9xl font-bold text-blue-600 mb-4">404</h1>
      <h2 class="text-3xl font-semibold text-gray-800 mb-4">Page Not Found</h2>
      <p class="text-lg text-gray-600 mb-8">
        We're sorry, the page you requested could not be found.
        It may have been moved, deleted, or never existed.
      </p>
      <div class="space-y-4">
        <a href="index.php" class="bg-blue-600 text-white hover:bg-blue-700 font-semibold py-3 px-6 rounded-lg inline-block transition duration-300">
          <i class="fas fa-home mr-2"></i> Back to Homepage
        </a>
        <div class="text-gray-500 mt-2">or</div>
        <div class="relative max-w-md mx-auto">
          <form action="search.php" method="get">
            <input type="text" name="q" placeholder="Search for articles..." class="w-full pl-4 pr-10 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-blue-600">
              <i class="fas fa-search"></i>
            </button>
          </form>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <?php include 'includes/footer.php'; ?>

  <script src="assets/js/script.js"></script>
</body>

</html>