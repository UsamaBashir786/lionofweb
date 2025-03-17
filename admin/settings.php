<?php
// Include configuration files
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../config/auth.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header("Location: ../login.php");
  exit;
}

// Get current settings
$stmt = $conn->prepare("SELECT * FROM settings WHERE id = 1");
$stmt->execute();
$settings = $stmt->fetch();

// If settings don't exist, create default
if (!$settings) {
  $stmt = $conn->prepare("INSERT INTO settings (site_name, site_description, site_email, posts_per_page, enable_comments, enable_user_registration, maintenance_mode) VALUES (?, ?, ?, ?, ?, ?, ?)");
  $stmt->execute([
    'NewsHub',
    'Your Source for Latest Information',
    'admin@example.com',
    10,
    1,
    1,
    0
  ]);

  $stmt = $conn->prepare("SELECT * FROM settings WHERE id = 1");
  $stmt->execute();
  $settings = $stmt->fetch();
}

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Process the form data
  $site_name = trim($_POST['site_name']);
  $site_description = trim($_POST['site_description']);
  $site_email = trim($_POST['site_email']);
  $posts_per_page = (int)$_POST['posts_per_page'];
  $enable_comments = isset($_POST['enable_comments']) ? 1 : 0;
  $enable_user_registration = isset($_POST['enable_user_registration']) ? 1 : 0;
  $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;
  $footer_text = trim($_POST['footer_text']);
  $analytics_code = trim($_POST['analytics_code']);

  // Upload site logo if provided
  $site_logo = $settings['site_logo']; // Keep existing logo by default
  if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] == 0) {
    $upload_dir = '../uploads/';
    $logo_name = time() . '_' . basename($_FILES['site_logo']['name']);
    $target_file = $upload_dir . $logo_name;

    // Check if image file is a valid image
    $check = getimagesize($_FILES['site_logo']['tmp_name']);
    if ($check !== false) {
      // Try to upload file
      if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $target_file)) {
        $site_logo = 'uploads/' . $logo_name;
        // Delete old logo if exists
        if (!empty($settings['site_logo']) && file_exists('../' . $settings['site_logo'])) {
          unlink('../' . $settings['site_logo']);
        }
      } else {
        $error_message = "Sorry, there was an error uploading your logo.";
      }
    } else {
      $error_message = "File is not an image.";
    }
  }

  // Validate data
  if (empty($site_name)) {
    $error_message = "Site name is required.";
  } elseif (empty($site_email) || !filter_var($site_email, FILTER_VALIDATE_EMAIL)) {
    $error_message = "A valid site email is required.";
  } elseif ($posts_per_page < 1 || $posts_per_page > 50) {
    $error_message = "Posts per page must be between 1 and 50.";
  }

  if (empty($error_message)) {
    // Update settings in database
    $stmt = $conn->prepare("UPDATE settings SET 
            site_name = ?,
            site_description = ?,
            site_email = ?,
            site_logo = ?,
            posts_per_page = ?,
            enable_comments = ?,
            enable_user_registration = ?,
            maintenance_mode = ?,
            footer_text = ?,
            analytics_code = ?,
            updated_at = NOW()
            WHERE id = 1");

    $stmt->execute([
      $site_name,
      $site_description,
      $site_email,
      $site_logo,
      $posts_per_page,
      $enable_comments,
      $enable_user_registration,
      $maintenance_mode,
      $footer_text,
      $analytics_code
    ]);

    $success_message = "Settings updated successfully!";

    // Refresh settings data
    $stmt = $conn->prepare("SELECT * FROM settings WHERE id = 1");
    $stmt->execute();
    $settings = $stmt->fetch();
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>General Settings - NewsHub Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    .sidebar-active {
      border-left: 4px solid #3B82F6;
      background-color: rgba(59, 130, 246, 0.1);
    }
  </style>
</head>

<body class="bg-gray-100 font-sans">
  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <?php include_once 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex flex-col flex-1 w-0 overflow-hidden">
      <!-- Top Navigation -->
      <?php include_once 'includes/navbar.php'; ?>

      <!-- Main Content Area -->
      <main class="relative flex-1 overflow-y-auto focus:outline-none">
        <div class="py-6">
          <div class="px-4 mx-auto max-w-7xl sm:px-6 md:px-8">
            <h1 class="text-2xl font-semibold text-gray-900">General Settings</h1>
          </div>
          <div class="px-4 mx-auto max-w-7xl sm:px-6 md:px-8 mt-5">
            <!-- Success message -->
            <?php if (!empty($success_message)): ?>
              <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $success_message; ?></span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                  <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>Close</title>
                    <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                  </svg>
                </span>
              </div>
            <?php endif; ?>

            <!-- Error message -->
            <?php if (!empty($error_message)): ?>
              <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error_message; ?></span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                  <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>Close</title>
                    <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                  </svg>
                </span>
              </div>
            <?php endif; ?>

            <!-- Settings Form -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
              <form method="POST" action="" enctype="multipart/form-data">
                <div class="p-6 bg-white">
                  <!-- General Settings Section -->
                  <div class="mb-8">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">General Settings</h2>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                      <div class="sm:col-span-3">
                        <label for="site_name" class="block text-sm font-medium text-gray-700">Site Name</label>
                        <div class="mt-1">
                          <input type="text" name="site_name" id="site_name" value="<?php echo htmlspecialchars($settings['site_name']); ?>"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                        </div>
                      </div>

                      <div class="sm:col-span-3">
                        <label for="site_email" class="block text-sm font-medium text-gray-700">Site Email</label>
                        <div class="mt-1">
                          <input type="email" name="site_email" id="site_email" value="<?php echo htmlspecialchars($settings['site_email']); ?>"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                        </div>
                      </div>

                      <div class="sm:col-span-6">
                        <label for="site_description" class="block text-sm font-medium text-gray-700">Site Description</label>
                        <div class="mt-1">
                          <textarea id="site_description" name="site_description" rows="3"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"><?php echo htmlspecialchars($settings['site_description']); ?></textarea>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Brief description of your website. This will be used in search engine results.</p>
                      </div>

                      <div class="sm:col-span-6">
                        <label for="site_logo" class="block text-sm font-medium text-gray-700">
                          Site Logo
                        </label>

                        <?php if (!empty($settings['site_logo'])): ?>
                          <div class="mt-2 mb-4">
                            <div class="flex items-center">
                              <div class="flex-shrink-0 h-16 w-auto bg-gray-100">
                                <img class="h-16 w-auto" src="../<?php echo htmlspecialchars($settings['site_logo']); ?>" alt="Current logo">
                              </div>
                              <div class="ml-4">
                                <p class="text-sm text-gray-500">Current Logo</p>
                              </div>
                            </div>
                          </div>
                        <?php endif; ?>

                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                          <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48"
                              aria-hidden="true">
                              <path
                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                              <label for="file-upload"
                                class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <span>Upload a new logo</span>
                                <input id="file-upload" name="site_logo" type="file" class="sr-only">
                              </label>
                              <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">
                              PNG, JPG, GIF up to 2MB
                            </p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Content Settings Section -->
                  <div class="mb-8">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Content Settings</h2>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                      <div class="sm:col-span-2">
                        <label for="posts_per_page" class="block text-sm font-medium text-gray-700">Posts Per Page</label>
                        <div class="mt-1">
                          <input type="number" name="posts_per_page" id="posts_per_page" min="1" max="50" value="<?php echo $settings['posts_per_page']; ?>"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                      </div>

                      <div class="sm:col-span-6">
                        <div class="flex items-start">
                          <div class="flex items-center h-5">
                            <input id="enable_comments" name="enable_comments" type="checkbox"
                              <?php echo ($settings['enable_comments'] == 1) ? 'checked' : ''; ?>
                              class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                          </div>
                          <div class="ml-3 text-sm">
                            <label for="enable_comments" class="font-medium text-gray-700">Enable Comments</label>
                            <p class="text-gray-500">Allow users to comment on articles.</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- User Settings Section -->
                  <div class="mb-8">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">User Settings</h2>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                      <div class="sm:col-span-6">
                        <div class="flex items-start">
                          <div class="flex items-center h-5">
                            <input id="enable_user_registration" name="enable_user_registration" type="checkbox"
                              <?php echo ($settings['enable_user_registration'] == 1) ? 'checked' : ''; ?>
                              class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                          </div>
                          <div class="ml-3 text-sm">
                            <label for="enable_user_registration" class="font-medium text-gray-700">Enable User Registration</label>
                            <p class="text-gray-500">Allow new users to register on the website.</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Footer Settings Section -->
                  <div class="mb-8">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Footer Settings</h2>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                      <div class="sm:col-span-6">
                        <label for="footer_text" class="block text-sm font-medium text-gray-700">Footer Text</label>
                        <div class="mt-1">
                          <textarea id="footer_text" name="footer_text" rows="2"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"><?php echo htmlspecialchars($settings['footer_text']); ?></textarea>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Copyright text or other information to be displayed in the footer.</p>
                      </div>
                    </div>
                  </div>

                  <!-- Advanced Settings Section -->
                  <div class="mb-8">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Advanced Settings</h2>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                      <div class="sm:col-span-6">
                        <div class="flex items-start">
                          <div class="flex items-center h-5">
                            <input id="maintenance_mode" name="maintenance_mode" type="checkbox"
                              <?php echo ($settings['maintenance_mode'] == 1) ? 'checked' : ''; ?>
                              class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                          </div>
                          <div class="ml-3 text-sm">
                            <label for="maintenance_mode" class="font-medium text-gray-700">Maintenance Mode</label>
                            <p class="text-gray-500">Enable maintenance mode to prevent public access to the website. Only administrators can access the site when this is enabled.</p>
                          </div>
                        </div>
                      </div>

                      <div class="sm:col-span-6">
                        <label for="analytics_code" class="block text-sm font-medium text-gray-700">Analytics Code</label>
                        <div class="mt-1">
                          <textarea id="analytics_code" name="analytics_code" rows="5"
                            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md font-mono"><?php echo htmlspecialchars($settings['analytics_code']); ?></textarea>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Add Google Analytics or other tracking code here. This will be added to all pages.</p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                  <button type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Save Settings
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</body>

</html>