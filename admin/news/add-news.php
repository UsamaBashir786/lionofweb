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
  $author_id = $_SESSION['user_id']; // Current logged in admin

  // Additional fields
  $subtitle = trim($_POST['subtitle'] ?? '');
  $excerpt = trim($_POST['excerpt'] ?? '');
  $source = trim($_POST['source'] ?? '');
  $source_url = trim($_POST['source_url'] ?? '');
  $publish_date = !empty($_POST['publish_date']) ? $_POST['publish_date'] : date('Y-m-d H:i:s');
  $tags = trim($_POST['tags'] ?? '');

  // SEO fields
  $meta_title = trim($_POST['meta_title'] ?? '');
  $meta_description = trim($_POST['meta_description'] ?? '');
  $meta_keywords = trim($_POST['meta_keywords'] ?? '');

  // Social media
  $social_title = trim($_POST['social_title'] ?? '');
  $social_description = trim($_POST['social_description'] ?? '');

  // Upload image if provided
  $image_path = '';
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
      } else {
        $error_message = "Sorry, there was an error uploading your file.";
      }
    } else {
      $error_message = "File is not an image.";
    }
  }

  // Upload social image if provided
  $social_image = '';
  if (isset($_FILES['social_image']) && $_FILES['social_image']['error'] == 0) {
    $upload_dir = '../../uploads/news-images/social/';
    if (!file_exists($upload_dir)) {
      mkdir($upload_dir, 0755, true);
    }

    $image_name = time() . '_social_' . basename($_FILES['social_image']['name']);
    $target_file = $upload_dir . $image_name;

    // Check if image file is a valid image
    $check = getimagesize($_FILES['social_image']['tmp_name']);
    if ($check !== false) {
      // Try to upload file
      if (move_uploaded_file($_FILES['social_image']['tmp_name'], $target_file)) {
        $social_image = 'uploads/news-images/social/' . $image_name;
      } else {
        $error_message = "Sorry, there was an error uploading the social media image.";
      }
    } else {
      $error_message = "Social media file is not an image.";
    }
  }

  if (empty($error_message)) {
    // Insert article into database
    $result = addArticleEnhanced(
      $conn,
      $title,
      $content,
      $category_id,
      $image_path,
      $status,
      $featured,
      $breaking,
      $author_id,
      $subtitle,
      $excerpt,
      $source,
      $source_url,
      $publish_date,
      $tags,
      $meta_title,
      $meta_description,
      $meta_keywords,
      $social_title,
      $social_description,
      $social_image
    );

    if ($result) {
      $success_message = "Article successfully added!";
      // Clear form values on success
      $title = $content = $subtitle = $excerpt = $source = $source_url = '';
      $tags = $meta_title = $meta_description = $meta_keywords = '';
      $social_title = $social_description = '';
      $category_id = $status = '';
      $featured = $breaking = 0;
    } else {
      $error_message = "Error adding article to database.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add New Article - NewsHub Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <!-- TinyMCE editor -->
  <script src="https://cdn.tiny.cloud/1/2u3loz6azvlme3v7gibeawtm1ao7ylo096qdbp49ybl7a5aq/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
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

    .tab-active {
      border-bottom: 2px solid #3B82F6;
      color: #3B82F6;
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
                <h1 class="text-2xl font-semibold text-gray-900">Add New Article</h1>
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

            <!-- Add Article Form -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
              <form method="POST" action="" enctype="multipart/form-data">
                <!-- Form Tabs -->
                <div class="bg-white border-b border-gray-200">
                  <div class="sm:flex sm:items-baseline">
                    <div class="mt-4 sm:mt-0">
                      <nav class="-mb-px flex space-x-8">
                        <a href="#" class="tab-link tab-active whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm" data-target="basic-info">
                          Basic Information
                        </a>
                        <a href="#" class="tab-link whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-target="content-tab">
                          Content
                        </a>
                        <a href="#" class="tab-link whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-target="seo-tab">
                          SEO & Metadata
                        </a>
                        <a href="#" class="tab-link whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-target="social-tab">
                          Social Media
                        </a>
                      </nav>
                    </div>
                  </div>
                </div>

                <!-- Basic Information Tab -->
                <div id="basic-info" class="tab-content p-6 bg-white">
                  <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-6">
                      <label for="title" class="block text-sm font-medium text-gray-700">Article Title</label>
                      <div class="mt-1">
                        <input type="text" name="title" id="title" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                      </div>
                    </div>

                    <div class="sm:col-span-6">
                      <label for="subtitle" class="block text-sm font-medium text-gray-700">Subtitle</label>
                      <div class="mt-1">
                        <input type="text" name="subtitle" id="subtitle" value="<?php echo isset($subtitle) ? htmlspecialchars($subtitle) : ''; ?>"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                      </div>
                      <p class="mt-1 text-xs text-gray-500">A secondary headline that provides additional context</p>
                    </div>

                    <div class="sm:col-span-3">
                      <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                      <div class="mt-1">
                        <select id="category_id" name="category_id"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                          <option value="">Select a category</option>
                          <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo (isset($category_id) && $category_id == $category['id']) ? 'selected' : ''; ?>>
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
                          <option value="published" <?php echo (isset($status) && $status == 'published') ? 'selected' : ''; ?>>Published</option>
                          <option value="draft" <?php echo (isset($status) && $status == 'draft') ? 'selected' : ''; ?>>Draft</option>
                          <option value="pending" <?php echo (isset($status) && $status == 'pending') ? 'selected' : ''; ?>>Pending Review</option>
                          <option value="scheduled" <?php echo (isset($status) && $status == 'scheduled') ? 'selected' : ''; ?>>Scheduled</option>
                        </select>
                      </div>
                    </div>

                    <div class="sm:col-span-3">
                      <label for="publish_date" class="block text-sm font-medium text-gray-700">Publish Date & Time</label>
                      <div class="mt-1">
                        <input type="datetime-local" name="publish_date" id="publish_date"
                          value="<?php echo isset($publish_date) ? date('Y-m-d\TH:i', strtotime($publish_date)) : date('Y-m-d\TH:i'); ?>"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                      </div>
                      <p class="mt-1 text-xs text-gray-500">When scheduled, article will be published at this time</p>
                    </div>

                    <div class="sm:col-span-3">
                      <label for="tags" class="block text-sm font-medium text-gray-700">Tags</label>
                      <div class="mt-1">
                        <input type="text" name="tags" id="tags" value="<?php echo isset($tags) ? htmlspecialchars($tags) : ''; ?>"
                          placeholder="Tag1, Tag2, Tag3"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                      </div>
                      <p class="mt-1 text-xs text-gray-500">Comma-separated list of tags</p>
                    </div>

                    <div class="sm:col-span-6">
                      <label for="image" class="block text-sm font-medium text-gray-700">
                        Featured Image
                      </label>
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
                              <span>Upload a file</span>
                              <input id="file-upload" name="image" type="file" class="sr-only">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                          </div>
                          <p class="text-xs text-gray-500">
                            PNG, JPG, GIF up to 10MB
                          </p>
                        </div>
                      </div>
                      <p class="mt-1 text-xs text-gray-500">Recommended size: 1200 × 628 pixels</p>
                    </div>

                    <div class="sm:col-span-3">
                      <label for="source" class="block text-sm font-medium text-gray-700">Source Name</label>
                      <div class="mt-1">
                        <input type="text" name="source" id="source" value="<?php echo isset($source) ? htmlspecialchars($source) : ''; ?>"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                      </div>
                      <p class="mt-1 text-xs text-gray-500">Original source of the content (if applicable)</p>
                    </div>

                    <div class="sm:col-span-3">
                      <label for="source_url" class="block text-sm font-medium text-gray-700">Source URL</label>
                      <div class="mt-1">
                        <input type="url" name="source_url" id="source_url" value="<?php echo isset($source_url) ? htmlspecialchars($source_url) : ''; ?>"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                      </div>
                      <p class="mt-1 text-xs text-gray-500">Link to the original content (if applicable)</p>
                    </div>

                    <div class="sm:col-span-6">
                      <div class="flex items-start">
                        <div class="flex items-center h-5">
                          <input id="featured" name="featured" type="checkbox"
                            <?php echo (isset($featured) && $featured) ? 'checked' : ''; ?>
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
                            <?php echo (isset($breaking) && $breaking) ? 'checked' : ''; ?>
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

                <!-- Content Tab -->
                <div id="content-tab" class="tab-content p-6 bg-white hidden">
                  <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-6">
                      <label for="excerpt" class="block text-sm font-medium text-gray-700">
                        Excerpt / Summary
                      </label>
                      <div class="mt-1">
                        <textarea id="excerpt" name="excerpt" rows="3"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"><?php echo isset($excerpt) ? htmlspecialchars($excerpt) : ''; ?></textarea>
                      </div>
                      <p class="mt-1 text-xs text-gray-500">A short summary of the article. If left empty, it will be automatically generated.</p>
                    </div>

                    <div class="sm:col-span-6">
                      <label for="content" class="block text-sm font-medium text-gray-700">
                        Article Content
                      </label>
                      <div class="mt-1">
                        <textarea id="content" name="content" rows="10"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"><?php echo isset($content) ? htmlspecialchars($content) : ''; ?></textarea>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- SEO Tab -->
                <div id="seo-tab" class="tab-content p-6 bg-white hidden">
                  <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-6">
                      <label for="meta_title" class="block text-sm font-medium text-gray-700">
                        Meta Title
                      </label>
                      <div class="mt-1">
                        <input type="text" name="meta_title" id="meta_title" value="<?php echo isset($meta_title) ? htmlspecialchars($meta_title) : ''; ?>"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                      </div>
                      <p class="mt-1 text-xs text-gray-500">Title tag for SEO. If left empty, the article title will be used.</p>
                    </div>

                    <div class="sm:col-span-6">
                      <label for="meta_description" class="block text-sm font-medium text-gray-700">
                        Meta Description
                      </label>
                      <div class="mt-1">
                        <textarea id="meta_description" name="meta_description" rows="3"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"><?php echo isset($meta_description) ? htmlspecialchars($meta_description) : ''; ?></textarea>
                      </div>
                      <p class="mt-1 text-xs text-gray-500">Description for search engines. Recommended length: 150-160 characters.</p>
                    </div>

                    <div class="sm:col-span-6">
                      <label for="meta_keywords" class="block text-sm font-medium text-gray-700">
                        Meta Keywords
                      </label>
                      <div class="mt-1">
                        <input type="text" name="meta_keywords" id="meta_keywords" value="<?php echo isset($meta_keywords) ? htmlspecialchars($meta_keywords) : ''; ?>"
                          placeholder="keyword1, keyword2, keyword3"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                      </div>
                      <p class="mt-1 text-xs text-gray-500">Comma-separated keywords (less important for modern SEO)</p>
                    </div>
                  </div>
                </div>

                <!-- Social Media Tab -->
                <div id="social-tab" class="tab-content p-6 bg-white hidden">
                  <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-6">
                      <label for="social_title" class="block text-sm font-medium text-gray-700">
                        Social Media Title
                      </label>
                      <div class="mt-1">
                        <input type="text" name="social_title" id="social_title" value="<?php echo isset($social_title) ? htmlspecialchars($social_title) : ''; ?>"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                      </div>
                      <p class="mt-1 text-xs text-gray-500">Title for social media sharing. If left empty, the article title will be used.</p>
                    </div>

                    <div class="sm:col-span-6">
                      <label for="social_description" class="block text-sm font-medium text-gray-700">
                        Social Media Description
                      </label>
                      <div class="mt-1">
                        <textarea id="social_description" name="social_description" rows="3"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"><?php echo isset($social_description) ? htmlspecialchars($social_description) : ''; ?></textarea>
                      </div>
                      <p class="mt-1 text-xs text-gray-500">Description for social media sharing. If left empty, the meta description will be used.</p>
                    </div>

                    <div class="sm:col-span-6">
                      <label for="social_image" class="block text-sm font-medium text-gray-700">
                        Social Media Image
                      </label>
                      <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                          <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                          </svg>
                          <div class="flex text-sm text-gray-600">
                            <label for="social-image-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                              <span>Upload a file</span>
                              <input id="social-image-upload" name="social_image" type="file" class="sr-only">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                          </div>
                          <p class="text-xs text-gray-500">
                            PNG, JPG, GIF up to 10MB
                          </p>
                        </div>
                      </div>
                      <p class="mt-1 text-xs text-gray-500">Optimized image for social media sharing. Recommended size: 1200 × 630 pixels.</p>
                    </div>
                  </div>
                </div>

                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                  <button type="button" onclick="window.location='manage-news.php'" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-2">
                    Cancel
                  </button>
                  <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Save Article
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script>
    // Tab switching functionality
    document.addEventListener('DOMContentLoaded', function() {
      const tabLinks = document.querySelectorAll('.tab-link');
      const tabContents = document.querySelectorAll('.tab-content');

      tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
          e.preventDefault();

          // Remove active class from all tabs
          tabLinks.forEach(tab => {
            tab.classList.remove('tab-active');
            tab.classList.add('text-gray-500');
            tab.classList.remove('text-blue-600');
          });

          // Add active class to clicked tab
          this.classList.add('tab-active');
          this.classList.remove('text-gray-500');
          this.classList.add('text-blue-600');

          // Hide all tab contents
          tabContents.forEach(content => {
            content.classList.add('hidden');
          });

          // Show corresponding tab content
          const targetId = this.getAttribute('data-target');
          document.getElementById(targetId).classList.remove('hidden');
        });
      });
    });
  </script>
</body>

</html>