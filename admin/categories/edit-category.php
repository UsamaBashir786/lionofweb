<?php
// Include configuration files
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header("Location: ../login.php");
  exit;
}

// Check if category ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  $_SESSION['error_message'] = "Invalid category ID.";
  header("Location: manage-categories.php");
  exit;
}

$category_id = (int)$_GET['id'];
$category = getCategoryById($conn, $category_id);

// Check if category exists
if (!$category) {
  $_SESSION['error_message'] = "Category not found.";
  header("Location: manage-categories.php");
  exit;
}

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Process the form data
  $name = trim($_POST['name']);
  $slug = trim($_POST['slug']);
  $description = trim($_POST['description']);
  $is_active = isset($_POST['is_active']) ? 1 : 0;

  // Validate form data
  if (empty($name)) {
    $error_message = "Category name is required.";
  } elseif (empty($slug)) {
    // Generate slug from name if not provided
    $slug = generateSlug($name);
  }

  // Upload new icon if provided
  $icon_path = $category['icon']; // Keep existing icon by default
  if (isset($_FILES['icon']) && $_FILES['icon']['error'] == 0) {
    $upload_dir = '../../uploads/category-icons/';
    $icon_name = time() . '_' . basename($_FILES['icon']['name']);
    $target_file = $upload_dir . $icon_name;

    // Check if image file is a valid image
    $check = getimagesize($_FILES['icon']['tmp_name']);
    if ($check !== false) {
      // Try to upload file
      if (move_uploaded_file($_FILES['icon']['tmp_name'], $target_file)) {
        $icon_path = 'uploads/category-icons/' . $icon_name;
        // Delete old icon if exists
        if (!empty($category['icon']) && file_exists('../../' . $category['icon'])) {
          unlink('../../' . $category['icon']);
        }
      } else {
        $error_message = "Sorry, there was an error uploading your file.";
      }
    } else {
      $error_message = "File is not an image.";
    }
  }

  if (empty($error_message)) {
    // Update category in database
    $result = updateCategory($conn, $category_id, $name, $slug, $description, $icon_path, $is_active);

    if ($result) {
      $success_message = "Category successfully updated!";
      // Refresh category data
      $category = getCategoryById($conn, $category_id);
    } else {
      $error_message = "Error updating category in database. The slug may already be in use.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Category - NewsHub Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    .sidebar-active {
      border-left: 4px solid #3B82F6;
      background-color: rgba(59, 130, 246, 0.1);
    }
  </style>
  <?php include '../includes/style.php' ?>
</head>

<body class="bg-gray-100 font-sans">
  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <?php include_once '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex flex-col flex-1 w-0 overflow-hidden">
      <!-- Top Navigation -->
      <?php include_once '../includes/navbar.php'; ?>

      <!-- Main Content Area -->
      <main class="relative flex-1 overflow-y-auto focus:outline-none">
        <div class="py-6">
          <div class="px-4 mx-auto max-w-7xl sm:px-6 md:px-8">
            <div class="md:flex md:items-center md:justify-between">
              <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-semibold text-gray-900">Edit Category</h1>
              </div>
              <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="manage-categories.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                  <i class="fas fa-list -ml-1 mr-2 h-5 w-5 text-gray-500"></i>
                  Manage Categories
                </a>
              </div>
            </div>
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

            <!-- Edit Category Form -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
              <form method="POST" action="" enctype="multipart/form-data">
                <div class="p-6 bg-white">
                  <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                      <label for="name" class="block text-sm font-medium text-gray-700">Category Name</label>
                      <div class="mt-1">
                        <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($category['name']); ?>"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                      </div>
                    </div>

                    <div class="sm:col-span-3">
                      <label for="slug" class="block text-sm font-medium text-gray-700">
                        Slug
                        <span class="text-gray-500 text-xs ml-1">(used in URLs)</span>
                      </label>
                      <div class="mt-1">
                        <input type="text" name="slug" id="slug" value="<?php echo htmlspecialchars($category['slug']); ?>"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                      </div>
                    </div>

                    <div class="sm:col-span-6">
                      <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                      <div class="mt-1">
                        <textarea id="description" name="description" rows="3"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"><?php echo htmlspecialchars($category['description']); ?></textarea>
                      </div>
                      <p class="mt-2 text-sm text-gray-500">Brief description of the category. This may be displayed on the website.</p>
                    </div>

                    <div class="sm:col-span-6">
                      <label for="icon" class="block text-sm font-medium text-gray-700">
                        Category Icon
                      </label>

                      <?php if (!empty($category['icon'])): ?>
                        <div class="mt-2 mb-4">
                          <div class="flex items-center">
                            <div class="flex-shrink-0 h-16 w-16 bg-gray-100">
                              <img class="h-16 w-16 object-cover rounded-md" src="../../<?php echo htmlspecialchars($category['icon']); ?>" alt="Current icon">
                            </div>
                            <div class="ml-4">
                              <p class="text-sm text-gray-500">Current Icon</p>
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
                              <span>Upload a new icon</span>
                              <input id="file-upload" name="icon" type="file" class="sr-only">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                          </div>
                          <p class="text-xs text-gray-500">
                            PNG, JPG, GIF up to 2MB
                          </p>
                        </div>
                      </div>
                      <p class="mt-2 text-sm text-gray-500">The icon will be displayed next to the category name on the website.</p>
                    </div>

                    <div class="sm:col-span-6">
                      <div class="flex items-start">
                        <div class="flex items-center h-5">
                          <input id="is_active" name="is_active" type="checkbox"
                            <?php echo ($category['is_active'] == 1) ? 'checked' : ''; ?>
                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                          <label for="is_active" class="font-medium text-gray-700">Active Category</label>
                          <p class="text-gray-500">Enable this category to be visible on the website.</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                  <button type="button" onclick="window.location='manage-categories.php'"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-2">
                    Cancel
                  </button>
                  <button type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Update Category
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