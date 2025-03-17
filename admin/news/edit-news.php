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

// Check if article ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  $_SESSION['error_message'] = "Invalid article ID.";
  header("Location: manage-news.php");
  exit;
}

$article_id = (int)$_GET['id'];
$article = getArticleById($conn, $article_id);

// Check if article exists
if (!$article) {
  $_SESSION['error_message'] = "Article not found.";
  header("Location: manage-news.php");
  exit;
}

// Get categories for dropdown
$categories = getAllCategories($conn);

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Process the form data
  $title = trim($_POST['title']);
  $content = $_POST['content'];
  $category_id = $_POST['category_id'];
  $status = $_POST['status'];
  $featured = isset($_POST['featured']) ? 1 : 0;
  $breaking = isset($_POST['breaking']) ? 1 : 0;

  // Upload new image if provided
  $image_path = $article['image']; // Keep existing image by default
  if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $upload_dir = '../../uploads/news-images/';
    $image_name = time() . '_' . basename($_FILES['image']['name']);
    $target_file = $upload_dir . $image_name;

    // Check if image file is a valid image
    $check = getimagesize($_FILES['image']['tmp_name']);
    if ($check !== false) {
      // Try to upload file
      if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $image_path = 'uploads/news-images/' . $image_name;
        // Delete old image if exists
        if (!empty($article['image']) && file_exists('../../' . $article['image'])) {
          unlink('../../' . $article['image']);
        }
      } else {
        $error_message = "Sorry, there was an error uploading your file.";
      }
    } else {
      $error_message = "File is not an image.";
    }
  }

  if (empty($error_message)) {
    // Update article in database
    $result = updateArticle($conn, $article_id, $title, $content, $category_id, $image_path, $status, $featured, $breaking);

    if ($result) {
      $success_message = "Article successfully updated!";
      // Refresh article data
      $article = getArticleById($conn, $article_id);
    } else {
      $error_message = "Error updating article in database.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Article - NewsHub Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <!-- TinyMCE editor -->
  <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
  <script>
    tinymce.init({
      selector: '#content',
      height: 500,
      plugins: [
        'advlist autolink lists link image charmap print preview anchor',
        'searchreplace visualblocks code fullscreen',
        'insertdatetime media table paste code help wordcount'
      ],
      toolbar: 'undo redo | formatselect | ' +
        'bold italic backcolor | alignleft aligncenter ' +
        'alignright alignjustify | bullist numlist outdent indent | ' +
        'removeformat | help',
      content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
    });
  </script>
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
                <h1 class="text-2xl font-semibold text-gray-900">Edit Article</h1>
              </div>
              <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="manage-news.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                  <i class="fas fa-list -ml-1 mr-2 h-5 w-5 text-gray-500"></i>
                  Manage Articles
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

            <!-- Edit Article Form -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
              <form method="POST" action="" enctype="multipart/form-data">
                <div class="p-6 bg-white">
                  <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-6">
                      <label for="title" class="block text-sm font-medium text-gray-700">Article Title</label>
                      <div class="mt-1">
                        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($article['title']); ?>"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                      </div>
                    </div>

                    <div class="sm:col-span-3">
                      <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                      <div class="mt-1">
                        <select id="category_id" name="category_id"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                          <option value="">Select a category</option>
                          <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo ($article['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                              <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>

                    <div class="sm:col-span-3">
                      <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                      <div class="mt-1">
                        <select id="status" name="status"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                          <option value="published" <?php echo ($article['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                          <option value="draft" <?php echo ($article['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                          <option value="pending" <?php echo ($article['status'] == 'pending') ? 'selected' : ''; ?>>Pending Review</option>
                        </select>
                      </div>
                    </div>

                    <div class="sm:col-span-6">
                      <label for="image" class="block text-sm font-medium text-gray-700">
                        Featured Image
                      </label>

                      <?php if (!empty($article['image'])): ?>
                        <div class="mt-2 mb-4">
                          <div class="flex items-center">
                            <div class="flex-shrink-0 h-16 w-24 bg-gray-100">
                              <img class="h-16 w-24 object-cover" src="../../<?php echo htmlspecialchars($article['image']); ?>" alt="Current image">
                            </div>
                            <div class="ml-4">
                              <p class="text-sm text-gray-500">Current Image</p>
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
                              <span>Upload a new image</span>
                              <input id="file-upload" name="image" type="file" class="sr-only">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                          </div>
                          <p class="text-xs text-gray-500">
                            PNG, JPG, GIF up to 10MB
                          </p>
                        </div>
                      </div>
                    </div>

                    <div class="sm:col-span-6">
                      <label for="content" class="block text-sm font-medium text-gray-700">
                        Article Content
                      </label>
                      <div class="mt-1">
                        <textarea id="content" name="content" rows="10"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"><?php echo htmlspecialchars($article['content']); ?></textarea>
                      </div>
                    </div>

                    <div class="sm:col-span-6">
                      <div class="flex items-start">
                        <div class="flex items-center h-5">
                          <input id="featured" name="featured" type="checkbox"
                            <?php echo ($article['featured'] == 1) ? 'checked' : ''; ?>
                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                          <label for="featured" class="font-medium text-gray-700">Featured Article</label>
                          <p class="text-gray-500">This article will be displayed in the featured section on the homepage.</p>
                        </div>
                      </div>
                    </div>

                    <div class="sm:col-span-6">
                      <div class="flex items-start">
                        <div class="flex items-center h-5">
                          <input id="breaking" name="breaking" type="checkbox"
                            <?php echo ($article['breaking'] == 1) ? 'checked' : ''; ?>
                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                          <label for="breaking" class="font-medium text-gray-700">Breaking News</label>
                          <p class="text-gray-500">This article will be displayed in the breaking news ticker.</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                  <button type="button" onclick="window.location='manage-news.php'"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-2">
                    Cancel
                  </button>
                  <button type="submit"
                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Update Article
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