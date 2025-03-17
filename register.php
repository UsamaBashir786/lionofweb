<?php
// Start session
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - NewsHub</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-gray-100 font-sans">
  <!-- Navigation bar will be loaded here -->
  <?php include 'includes/navbar.php'; ?>

  <!-- Register Section -->
  <main class="container mx-auto px-4 py-12 flex justify-center">
    <div class="w-full max-w-md">
      <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Register Header -->
        <div class="bg-blue-600 text-white py-6 px-8">
          <div class="flex items-center justify-center mb-4">
            <div class="bg-white text-blue-600 text-2xl font-bold p-2 rounded-lg mr-2">
              <i class="fas fa-newspaper"></i>
            </div>
            <span class="text-2xl font-bold">News<span class="text-blue-200">Hub</span></span>
          </div>
          <h1 class="text-2xl font-bold text-center">Create an Account</h1>
          <p class="text-center text-blue-100 mt-2">Join the NewsHub community</p>
        </div>

        <!-- Register Form -->
        <div class="p-8">
          <!-- Error message -->
          <?php if (isset($_SESSION['register_error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
              <span class="block sm:inline"><?php echo $_SESSION['register_error']; ?></span>
              <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                  <title>Close</title>
                  <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                </svg>
              </span>
            </div>
          <?php unset($_SESSION['register_error']);
          endif; ?>

          <form action="config/auth.php" method="POST" class="space-y-6">
            <input type="hidden" name="action" value="register">

            <!-- Full Name Input -->
            <div>
              <label for="name" class="block text-gray-700 font-medium mb-2">Full Name</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <i class="fas fa-user text-gray-400"></i>
                </div>
                <input
                  type="text"
                  id="name"
                  name="name"
                  class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Your full name"
                  required>
              </div>
            </div>

            <!-- Email Input -->
            <div>
              <label for="email" class="block text-gray-700 font-medium mb-2">Email Address</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <i class="fas fa-envelope text-gray-400"></i>
                </div>
                <input
                  type="email"
                  id="email"
                  name="email"
                  class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="your.email@example.com"
                  required>
              </div>
            </div>

            <!-- Password Input -->
            <div>
              <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <i class="fas fa-lock text-gray-400"></i>
                </div>
                <input
                  type="password"
                  id="password"
                  name="password"
                  class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="••••••••"
                  required>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                  <button
                    type="button"
                    id="toggle-password"
                    class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <i class="fas fa-eye"></i>
                  </button>
                </div>
              </div>
              <p class="mt-1 text-xs text-gray-500">Minimum 8 characters, including letters and numbers</p>
            </div>

            <!-- Confirm Password Input -->
            <div>
              <label for="confirm_password" class="block text-gray-700 font-medium mb-2">Confirm Password</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <i class="fas fa-lock text-gray-400"></i>
                </div>
                <input
                  type="password"
                  id="confirm_password"
                  name="confirm_password"
                  class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="••••••••"
                  required>
              </div>
            </div>

            <!-- Terms and Conditions Checkbox -->
            <div class="flex items-start">
              <div class="flex items-center h-5">
                <input
                  type="checkbox"
                  id="terms"
                  name="terms"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  required>
              </div>
              <div class="ml-3 text-sm">
                <label for="terms" class="text-gray-700">
                  I agree to the <a href="terms.php" class="text-blue-600 hover:text-blue-800">Terms of Service</a> and <a href="privacy.php" class="text-blue-600 hover:text-blue-800">Privacy Policy</a>
                </label>
              </div>
            </div>

            <!-- Sign Up Button -->
            <div>
              <button
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-300">
                Create Account
              </button>
            </div>
          </form>

          <!-- Social Login Options -->
          <div class="mt-6">
            <div class="relative">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
              </div>
              <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">Or sign up with</span>
              </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-3">
            <a
              href="#"
              class="flex justify-center items-center py-2 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
              <i class="fab fa-google text-red-600 mr-2"></i>
              Google
              </a>
              <a
              href="#"
              class="flex justify-center items-center py-2 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
              <i class="fab fa-facebook-f text-blue-600 mr-2"></i>
              Facebook
              </a>
            </div>
          </div>

          <!-- Login Link -->
          <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
              Already have an account?
              <a href="login.php" class="font-medium text-blue-600 hover:text-blue-800">
                Sign in
              </a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer will be loaded here -->
  <?php include 'includes/footer.php'; ?>

  <!-- JavaScript -->
  <script>
    // Password visibility toggle
    document.getElementById('toggle-password').addEventListener('click', function() {
      const passwordInput = document.getElementById('password');
      const icon = this.querySelector('i');

      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
      } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
      }
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;

      if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
      }

      if (password.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters long.');
      }
    });
  </script>
</body>

</html>