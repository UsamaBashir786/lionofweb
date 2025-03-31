<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Page Under Development - Lion Of Web</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    .animate-pulse {
      animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {

      0%,
      100% {
        opacity: 1;
      }

      50% {
        opacity: .5;
      }
    }

    .animate-bounce {
      animation: bounce 1s infinite;
    }

    @keyframes bounce {

      0%,
      100% {
        transform: translateY(-5%);
        animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
      }

      50% {
        transform: translateY(0);
        animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
      }
    }
  </style>
</head>

<body class="bg-gray-100 font-sans min-h-screen flex flex-col">
  <?php
  // Include only the top part of navigation
  include 'includes/navbar.php';
  ?>

  <div class="flex-grow flex items-center justify-center p-4">
    <div class="max-w-3xl w-full bg-white rounded-lg shadow-xl p-8 text-center">
      <div class="mb-8">
        <div class="mx-auto w-24 h-24 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center animate-pulse">
          <i class="fas fa-tools text-4xl"></i>
        </div>
      </div>

      <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-4">Under Development</h1>

      <p class="text-xl text-gray-600 mb-6">
        We're working hard to bring you this page. Please check back soon!
      </p>

      <div class="mb-8 bg-gray-200 h-3 rounded-full overflow-hidden">
        <div class="bg-blue-600 h-full w-2/3 rounded-full"></div>
      </div>

      <div class="mb-8">
        <p class="text-gray-500 mb-2">
          Looking for something else in the meantime?
        </p>
        <div class="flex flex-wrap justify-center gap-3 mt-4">
          <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors duration-200">
            <i class="fas fa-home mr-2"></i> Homepage
          </a>
          <a href="contact.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-6 rounded-lg transition-colors duration-200">
            <i class="fas fa-envelope mr-2"></i> Contact Us
          </a>
        </div>
      </div>

      <div class="mt-8 text-sm text-gray-500 border-t border-gray-200 pt-6">
        <p class="mb-2">
          <strong>Requested URL:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>
        </p>
        <div class="animate-bounce text-blue-600 mt-6">
          <i class="fas fa-arrow-down"></i> Scroll down for more content
        </div>
      </div>
    </div>
  </div>

  <!-- Include footer -->
  <?php include 'includes/footer.php'; ?>

  <!-- Custom JavaScript for this page -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // You could add some special effects or logging here
      console.log('Page under development accessed: ' + window.location.pathname);

      // Optional: Log missing page to analytics or custom endpoint
      /* 
      fetch('/api/log-missing-page', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
          },
          body: JSON.stringify({
              url: window.location.pathname,
              timestamp: new Date().toISOString()
          })
      });
      */
    });
  </script>
</body>

</html>