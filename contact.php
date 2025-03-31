<?php
// Include database connection
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Initialize variables
$name = $email = $phone = $subject = $message = '';
$success_message = $error_message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get form data
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $phone = trim($_POST['phone']);
  $subject = trim($_POST['subject']);
  $message = trim($_POST['message']);

  // Basic validation
  if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    $error_message = "All fields are required.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error_message = "Please enter a valid email address.";
  } else {
    // Insert into database
    try {
      $stmt = $conn->prepare("
                INSERT INTO contact_submissions (name, email, phone, subject, message, created_at) 
                VALUES (:name, :email, :phone, :subject, :message, NOW())
            ");

      $stmt->bindParam(':name', $name);
      $stmt->bindParam(':email', $email);
      $stmt->bindParam(':phone', $phone);
      $stmt->bindParam(':subject', $subject);
      $stmt->bindParam(':message', $message);

      if ($stmt->execute()) {
        $success_message = "Your message has been sent successfully! We'll get back to you soon.";
        // Clear form data after successful submission
        $name = $email = $phone = $subject = $message = '';
      } else {
        $error_message = "There was a problem submitting your message. Please try again.";
      }
    } catch (PDOException $e) {
      $error_message = "Database error: " . $e->getMessage();
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us - NewsHub</title>
  <link rel="shortcut icon" href="assets/images/logo.png" type="image/x-icon">
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="node_modules/aos/dist/aos.css">
  <style>
    body {
      overflow-x: hidden;
    }
  </style>
</head>

<body class="bg-gray-100 font-sans">
  <?php include 'includes/navbar.php'; ?>

  <!-- Page Header -->
  <div class="bg-blue-600 text-white py-16">
    <div class="container mx-auto px-4 text-center">
      <h1 class="text-4xl font-bold mb-2" data-aos="fade-up" data-aos-duration="800">Contact Us</h1>
      <p class="text-xl text-blue-100" data-aos="fade-up" data-aos-delay="100" data-aos-duration="800">
        We'd love to hear from you. Reach out to our team with any questions or feedback.
      </p>
    </div>
  </div>

  <main class="container mx-auto px-4 py-12">
    <div class="max-w-4xl mx-auto">
      <!-- Contact Form Section -->
      <div class="form-container" data-aos="fade-up" data-aos-duration="1000">
        <h2 class="text-3xl font-bold text-gray-800 mb-8">Send Us a Message</h2>

        <!-- Success message -->
        <?php if (!empty($success_message)): ?>
          <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline"><?php echo $success_message; ?></span>
          </div>
        <?php endif; ?>

        <!-- Error message -->
        <?php if (!empty($error_message)): ?>
          <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline"><?php echo $error_message; ?></span>
          </div>
        <?php endif; ?>

        <!-- Contact Form -->
        <form method="POST" action="" class="bg-white rounded-lg shadow-lg p-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Name Field -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
              <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>"
                placeholder="John Doe" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
            </div>

            <!-- Email Field -->
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
              <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>"
                placeholder="john@example.com" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Phone Field -->
            <div>
              <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
              <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>"
                placeholder="(123) 456-7890" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Subject Field -->
            <div>
              <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject *</label>
              <select id="subject" name="subject" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                <option value="" disabled <?php echo empty($subject) ? 'selected' : ''; ?>>Select a subject</option>
                <option value="general" <?php echo $subject === 'general' ? 'selected' : ''; ?>>General Inquiry</option>
                <option value="support" <?php echo $subject === 'support' ? 'selected' : ''; ?>>Customer Support</option>
                <option value="billing" <?php echo $subject === 'billing' ? 'selected' : ''; ?>>Billing Question</option>
                <option value="other" <?php echo $subject === 'other' ? 'selected' : ''; ?>>Other</option>
              </select>
            </div>
          </div>

          <!-- Message Field -->
          <div class="mb-6">
            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Your Message *</label>
            <textarea id="message" name="message" rows="5" placeholder="How can we help you?"
              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required><?php echo htmlspecialchars($message); ?></textarea>
          </div>

          <!-- Privacy Policy Checkbox -->
          <div class="mb-6">
            <label class="inline-flex items-center">
              <input type="checkbox" class="rounded text-blue-600 focus:ring-blue-500 border-gray-300" required>
              <span class="ml-2 text-sm text-gray-600">I agree to the <a href="#" class="text-blue-600 hover:underline">privacy policy</a> and consent to having this website store my information.</span>
            </label>
          </div>

          <!-- Submit Button -->
          <div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md transition duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              Send Message
            </button>
          </div>
        </form>
      </div>

    </div>
  </main>

  <!-- Newsletter Section -->
  <!-- <section class="bg-blue-600 text-white rounded-lg p-8 mb-12 container mx-auto" data-aos="fade-up" data-aos-duration="1000">
    <div class="flex flex-col md:flex-row items-center justify-between">
      <div class="mb-6 md:mb-0 md:mr-8">
        <h2 class="text-2xl font-bold mb-2">Subscribe to Our Newsletter</h2>
        <p class="text-blue-100">Stay informed with our latest news and updates delivered directly to your inbox.</p>
      </div>
      <div class="w-full md:w-1/2">
        <form class="flex flex-col sm:flex-row gap-3">
          <input type="email" placeholder="Enter your email address" class="flex-grow px-4 py-3 rounded-lg text-gray-800 focus:outline-none">
          <button type="submit" class="bg-white text-blue-600 hover:bg-blue-100 font-semibold py-3 px-6 rounded-lg transition duration-300">
            Subscribe
          </button>
        </form>
      </div>
    </div>
  </section> -->

  <!-- Footer will be loaded here -->
  <?php include 'includes/footer.php'; ?>
  <script src="assets/js/script.js"></script>
  <script src="node_modules/aos/dist/aos.js"></script>
  <script>
    AOS.init();
  </script>
</body>

</html>