<?php
// File: admin/trending.php

// Include configuration files
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header("Location: login.php");
  exit;
}

// Default values for forms
$trending_id = 0;
$position = 0;
$title = '';
$description = '';
$content = '';
$author = '';
$publish_date = date('Y-m-d');
$link_url = '';
$image = '';
$featured_image = '';
$meta_keywords = '';
$meta_description = '';
$active = 1;
$is_edit_mode = false;

// Get the highest position currently used
$max_position = 0;
$stmt = $conn->query("SELECT MAX(position) as max_pos FROM trending_items");
if ($stmt) {
  $result = $stmt->fetch();
  $max_position = (int)$result['max_pos'];
}
$next_position = $max_position + 1;

// Check if we're in edit mode
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
  $trending_id = (int)$_GET['edit'];
  $stmt = $conn->prepare("SELECT * FROM trending_items WHERE id = ?");
  $stmt->execute([$trending_id]);
  $item = $stmt->fetch();

  if ($item) {
    $is_edit_mode = true;
    $position = $item['position'];
    $title = $item['title'];
    $description = $item['description'];
    $content = $item['content'];
    $author = $item['author'];
    $publish_date = $item['publish_date'] ? $item['publish_date'] : date('Y-m-d');
    $link_url = $item['link_url'];
    $image = $item['image'];
    $featured_image = $item['featured_image'];
    $meta_keywords = $item['meta_keywords'];
    $meta_description = $item['meta_description'];
    $active = $item['active'];
  } else {
    $_SESSION['error_message'] = "Trending item not found.";
    header("Location: trending.php");
    exit;
  }
}

// Handle trending item deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
  $delete_id = $_GET['delete'];

  try {
    // Get image paths before deleting
    $stmt = $conn->prepare("SELECT image, featured_image FROM trending_items WHERE id = ?");
    $stmt->execute([$delete_id]);
    $item_images = $stmt->fetch();

    // Delete the database record
    $stmt = $conn->prepare("DELETE FROM trending_items WHERE id = ?");
    $deleted = $stmt->execute([$delete_id]);

    if ($deleted) {
      // Delete associated images if they exist
      if (!empty($item_images['image']) && file_exists('../' . $item_images['image'])) {
        unlink('../' . $item_images['image']);
      }
      if (!empty($item_images['featured_image']) && file_exists('../' . $item_images['featured_image'])) {
        unlink('../' . $item_images['featured_image']);
      }
      $_SESSION['success_message'] = "Trending item successfully deleted!";
    } else {
      $_SESSION['error_message'] = "Error deleting trending item.";
    }
  } catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
  }

  // Redirect to remove the GET parameter
  header("Location: trending.php");
  exit;
}

// Handle trending item status toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
  $toggle_id = $_GET['toggle'];

  try {
    // First get the current status
    $stmt = $conn->prepare("SELECT active FROM trending_items WHERE id = ?");
    $stmt->execute([$toggle_id]);
    $current = $stmt->fetch();

    if ($current) {
      // Toggle the status
      $new_status = $current['active'] ? 0 : 1;

      $stmt = $conn->prepare("UPDATE trending_items SET active = ?, updated_at = NOW() WHERE id = ?");
      $toggled = $stmt->execute([$new_status, $toggle_id]);

      if ($toggled) {
        $_SESSION['success_message'] = "Trending item status updated successfully!";
      } else {
        $_SESSION['error_message'] = "Error updating trending item status.";
      }
    } else {
      $_SESSION['error_message'] = "Trending item not found.";
    }
  } catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
  }

  // Redirect to remove the GET parameter
  header("Location: trending.php");
  exit;
}

// Handle form submission (Add/Edit)
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Process the form data
  $position = isset($_POST['position']) ? intval($_POST['position']) : $next_position;
  $title = trim($_POST['title']);
  $description = trim($_POST['description']);
  $content = trim($_POST['content']);
  $author = trim($_POST['author']);
  $publish_date = !empty($_POST['publish_date']) ? $_POST['publish_date'] : date('Y-m-d');
  $link_url = trim($_POST['link_url']);
  $meta_keywords = trim($_POST['meta_keywords']);
  $meta_description = trim($_POST['meta_description']);
  $active = isset($_POST['active']) ? 1 : 0;
  $trending_id = isset($_POST['trending_id']) ? intval($_POST['trending_id']) : 0;

  // Keep current image paths (for edit mode)
  $current_image = $image;
  $current_featured_image = $featured_image;

  // Validate form data
  if (empty($title)) {
    $error_message = "Title is required.";
  } elseif (empty($description)) {
    $error_message = "Description is required.";
  } elseif (empty($link_url)) {
    $link_url = "trending-detail.php?id={last_inserted_id}";
  } elseif (!filter_var($link_url, FILTER_VALIDATE_URL) && $link_url !== '#' && !strpos($link_url, '.php')) {
    $error_message = "Please enter a valid URL or page link (.php).";
  } elseif ($position < 1) {
    $error_message = "Position must be a positive integer.";
  } else {
    // Handle image uploads
    $upload_dir = '../uploads/trending/';

    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
      mkdir($upload_dir, 0755, true);
    }

    // Process main image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
      $image_name = time() . '_' . basename($_FILES['image']['name']);
      $target_file = $upload_dir . $image_name;

      // Check file type
      $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
      if (in_array($_FILES['image']['type'], $allowed_types)) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
          // Delete old image if exists in edit mode
          if ($trending_id > 0 && !empty($current_image) && file_exists('../' . $current_image)) {
            unlink('../' . $current_image);
          }
          $image = 'uploads/trending/' . $image_name;
        } else {
          $error_message = "Failed to upload image.";
        }
      } else {
        $error_message = "Invalid image format. Please use JPG, PNG, GIF, or WebP.";
      }
    }

    // Process featured image upload
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
      $featured_image_name = time() . '_featured_' . basename($_FILES['featured_image']['name']);
      $target_file = $upload_dir . $featured_image_name;

      // Check file type
      $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
      if (in_array($_FILES['featured_image']['type'], $allowed_types)) {
        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
          // Delete old featured image if exists in edit mode
          if ($trending_id > 0 && !empty($current_featured_image) && file_exists('../' . $current_featured_image)) {
            unlink('../' . $current_featured_image);
          }
          $featured_image = 'uploads/trending/' . $featured_image_name;
        } else {
          $error_message = "Failed to upload featured image.";
        }
      } else {
        $error_message = "Invalid featured image format. Please use JPG, PNG, GIF, or WebP.";
      }
    }

    if (empty($error_message)) {
      try {
        if ($trending_id > 0) {
          // Update existing item
          $stmt = $conn->prepare("
            UPDATE trending_items 
            SET position = ?, title = ?, description = ?, content = ?, author = ?, 
                publish_date = ?, link_url = ?, image = ?, featured_image = ?,
                meta_keywords = ?, meta_description = ?, active = ?, updated_at = NOW()
            WHERE id = ?
          ");

          $result = $stmt->execute([
            $position,
            $title,
            $description,
            $content,
            $author,
            $publish_date,
            $link_url,
            $image,
            $featured_image,
            $meta_keywords,
            $meta_description,
            $active,
            $trending_id
          ]);
          $success_message = "Trending item updated successfully!";
        } else {
          // Insert new item
          $stmt = $conn->prepare("
            INSERT INTO trending_items (
              position, title, description, content, author, publish_date,
              link_url, image, featured_image, meta_keywords, meta_description, 
              active, created_at, updated_at
            ) VALUES (
              ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
            )
          ");

          $result = $stmt->execute([
            $position,
            $title,
            $description,
            $content,
            $author,
            $publish_date,
            $link_url,
            $image,
            $featured_image,
            $meta_keywords,
            $meta_description,
            $active
          ]);
          $success_message = "Trending item added successfully!";
        }

        if ($result) {
          // Clear form values on success for new items
          if (!$is_edit_mode) {
            $position = $next_position + 1;
            $last_inserted_id = $conn->lastInsertId();
            $title = $description = $content = $author = $link_url = '';
            $image = $featured_image = $meta_keywords = $meta_description = '';
            $publish_date = date('Y-m-d');
            // Update the link_url with the actual ID
            $update_stmt = $conn->prepare("UPDATE trending_items SET link_url = ? WHERE id = ?");
            $update_stmt->execute(["trending-detail.php?id=" . $last_inserted_id, $last_inserted_id]);
            $active = 1;
            $trending_id = 0;
          }
        } else {
          $error_message = "Database error occurred.";
        }
      } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
      }
    }
  }
}

// Get active trending items
$trending_items = [];
try {
  $stmt = $conn->prepare("SELECT * FROM trending_items WHERE active = 1 ORDER BY position ASC LIMIT 4");
  $stmt->execute();
  $trending_items = $stmt->fetchAll();
} catch (PDOException $e) {
  error_log("Error fetching trending items: " . $e->getMessage());
}


// Handle session messages
if (isset($_SESSION['success_message'])) {
  $success_message = $_SESSION['success_message'];
  unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
  $error_message = $_SESSION['error_message'];
  unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $is_edit_mode ? "Edit" : "Manage"; ?> Trending Items - NewsHub Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <!-- Include TinyMCE -->
  <script src="https://cdn.tiny.cloud/1/2u3loz6azvlme3v7gibeawtm1ao7ylo096qdbp49ybl7a5aq/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
  <script>
    tinymce.init({
      selector: '#content',
      height: 400,
      plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
      toolbar: 'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
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
  <?php include 'includes/style.php' ?>
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
            <div class="md:flex md:items-center md:justify-between">
              <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-semibold text-gray-900">
                  <?php echo $is_edit_mode ? "Edit Trending Item" : "Manage Trending Items"; ?>
                </h1>
              </div>

              <!-- Trending Items Table -->
              <?php if (!$is_edit_mode): ?>
                <div class="flex flex-col">
                  <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                      <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                          <thead class="bg-gray-50">
                            <tr>
                              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Position
                              </th>
                              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Title
                              </th>
                              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Author
                              </th>
                              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                              </th>
                              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                              </th>
                              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                              </th>
                            </tr>
                          </thead>
                          <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (!empty($trending_items)): ?>
                              <?php foreach ($trending_items as $item): ?>
                                <tr>
                                  <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-3xl font-bold text-blue-600"><?php echo str_pad($item['position'], 2, '0', STR_PAD_LEFT); ?></div>
                                  </td>
                                  <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['title']); ?></div>
                                    <div class="text-sm text-gray-500 truncate max-w-xs"><?php echo htmlspecialchars($item['description']); ?></div>
                                  </td>
                                  <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo htmlspecialchars($item['author'] ?: 'Not set'); ?></div>
                                  </td>
                                  <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">
                                      <?php echo $item['publish_date'] ? date('M d, Y', strtotime($item['publish_date'])) : 'Not set'; ?>
                                    </div>
                                  </td>
                                  <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($item['active'] == 1): ?>
                                      <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                      </span>
                                    <?php else: ?>
                                      <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Inactive
                                      </span>
                                    <?php endif; ?>
                                  </td>
                                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="trending.php?edit=<?php echo $item['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                      <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="trending.php?toggle=<?php echo $item['id']; ?>" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                      <?php if ($item['active'] == 1): ?>
                                        <i class="fas fa-toggle-on"></i> Deactivate
                                      <?php else: ?>
                                        <i class="fas fa-toggle-off"></i> Activate
                                      <?php endif; ?>
                                    </a>
                                    <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $item['id']; ?>)" class="text-red-600 hover:text-red-900">
                                      <i class="fas fa-trash"></i> Delete
                                    </a>
                                  </td>
                                </tr>
                              <?php endforeach; ?>
                            <?php else: ?>
                              <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                  No trending items found. Add your first trending item using the form above.
                                </td>
                              </tr>
                            <?php endif; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
              <?php if ($is_edit_mode): ?>
                <div class="mt-4 flex md:mt-0 md:ml-4">
                  <a href="trending.php" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-arrow-left -ml-1 mr-2 h-5 w-5 text-gray-500"></i>
                    Back to List
                  </a>
                </div>
              <?php endif; ?>
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

            <!-- Add/Edit Form -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
              <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="trending_id" value="<?php echo $trending_id; ?>">

                <!-- Form Tabs -->
                <div class="bg-gray-50 border-b border-gray-200">
                  <div class="sm:flex sm:items-baseline">
                    <div class="mt-4 sm:mt-0">
                      <nav class="-mb-px flex space-x-8">
                        <a href="#" class="tab-link tab-active whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm" data-target="basic-info">
                          Basic Information
                        </a>
                        <a href="#" class="tab-link whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-target="content-tab">
                          Detailed Content
                        </a>
                        <a href="#" class="tab-link whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-target="seo-settings">
                          SEO & Media
                        </a>
                      </nav>
                    </div>
                  </div>
                </div>

                <!-- Basic Information Tab -->
                <div id="basic-info" class="tab-content p-6 bg-white">
                  <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-1">
                      <label for="position" class="block text-sm font-medium text-gray-700">Position</label>
                      <div class="mt-1">
                        <input type="number" name="position" id="position" min="1" value="<?php echo $position ?: $next_position; ?>"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                      </div>
                      <p class="mt-1 text-xs text-gray-500">Display order (1-4)</p>
                    </div>

                    <div class="sm:col-span-5">
                      <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                      <div class="mt-1">
                        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($title); ?>"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" required>
                      </div>
                    </div>

                    <div class="sm:col-span-6">
                      <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                      <div class="mt-1">
                        <textarea id="description" name="description" rows="2"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" required><?php echo htmlspecialchars($description); ?></textarea>
                      </div>
                      <p class="mt-1 text-xs text-gray-500">Brief description (recommended: 70-100 characters)</p>
                    </div>

                    <div class="sm:col-span-3">
                      <label for="author" class="block text-sm font-medium text-gray-700">Author</label>
                      <div class="mt-1">
                        <input type="text" name="author" id="author" value="<?php echo htmlspecialchars($author); ?>"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                      </div>
                    </div>

                    <div class="sm:col-span-3">
                      <label for="publish_date" class="block text-sm font-medium text-gray-700">Publish Date</label>
                      <div class="mt-1">
                        <input type="date" name="publish_date" id="publish_date" value="<?php echo htmlspecialchars($publish_date); ?>"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                      </div>
                    </div>

                    <div class="sm:col-span-6">
                      <label for="link_url" class="block text-sm font-medium text-gray-700">Link URL</label>
                      <div class="mt-1">
                        <input type="text" name="link_url" id="link_url" value="<?php echo htmlspecialchars($link_url); ?>"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                      </div>
                      <p class="mt-1 text-xs text-gray-500">Page link (example: trending-detail.php?id=1) or full URL. Use # for placeholder links</p>
                    </div>

                    <div class="sm:col-span-6">
                      <div class="flex items-start">
                        <div class="flex items-center h-5">
                          <input id="active" name="active" type="checkbox"
                            <?php echo ($active == 1) ? 'checked' : ''; ?>
                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                          <label for="active" class="font-medium text-gray-700">Active</label>
                          <p class="text-gray-500">Enable this item to be displayed in the Trending section.</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Detailed Content Tab -->
                <div id="content-tab" class="tab-content p-6 bg-white hidden">
                  <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-6">
                      <label for="content" class="block text-sm font-medium text-gray-700">Full Article Content</label>
                      <div class="mt-1">
                        <textarea id="content" name="content" rows="10"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"><?php echo htmlspecialchars($content); ?></textarea>
                      </div>
                      <p class="mt-1 text-xs text-gray-500">Use the rich text editor to format your content</p>
                    </div>
                  </div>
                </div>

                <!-- SEO & Media Tab -->
                <div id="seo-settings" class="tab-content p-6 bg-white hidden">
                  <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                      <label for="image" class="block text-sm font-medium text-gray-700">Main Image</label>
                      <div class="mt-1">
                        <?php if (!empty($image)): ?>
                          <div class="mb-2">
                            <img src="../<?php echo htmlspecialchars($image); ?>" alt="Current image" class="h-24 w-auto object-cover rounded">
                            <p class="text-xs text-gray-500 mt-1">Current image</p>
                          </div>
                        <?php endif; ?>
                        <input type="file" name="image" id="image" accept="image/*"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300">
                      </div>
                      <p class="mt-1 text-xs text-gray-500">Used within the article content</p>
                    </div>

                    <div class="sm:col-span-3">
                      <label for="featured_image" class="block text-sm font-medium text-gray-700">Featured Image</label>
                      <div class="mt-1">
                        <?php if (!empty($featured_image)): ?>
                          <div class="mb-2">
                            <img src="../<?php echo htmlspecialchars($featured_image); ?>" alt="Current featured image" class="h-24 w-auto object-cover rounded">
                            <p class="text-xs text-gray-500 mt-1">Current featured image</p>
                          </div>
                        <?php endif; ?>
                        <input type="file" name="featured_image" id="featured_image" accept="image/*"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300">
                      </div>
                      <p class="mt-1 text-xs text-gray-500">Used as hero image at the top of the article</p>
                    </div>

                    <div class="sm:col-span-6">
                      <label for="meta_keywords" class="block text-sm font-medium text-gray-700">Meta Keywords</label>
                      <div class="mt-1">
                        <input type="text" name="meta_keywords" id="meta_keywords" value="<?php echo htmlspecialchars($meta_keywords); ?>"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                      </div>
                      <p class="mt-1 text-xs text-gray-500">Comma-separated keywords for SEO</p>
                    </div>

                    <div class="sm:col-span-6">
                      <label for="meta_description" class="block text-sm font-medium text-gray-700">Meta Description</label>
                      <div class="mt-1">
                        <textarea id="meta_description" name="meta_description" rows="2"
                          class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"><?php echo htmlspecialchars($meta_description); ?></textarea>
                      </div>
                      <p class="mt-1 text-xs text-gray-500">Brief description for search engine results</p>
                    </div>
                  </div>
                </div>

                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                  <?php if ($is_edit_mode): ?>
                    <a href="trending.php" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-2">
                      Cancel
                    </a>
                    <button type="submit"
                      class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                      Update Trending Item
                    </button>
                  <?php else: ?>
                    <button type="submit"
                      class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                      Save Trending Item
                    </button>
                  <?php endif; ?>
                </div>
              </form>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Delete confirmation modal -->
  <div id="deleteModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 transition-opacity" aria-hidden="true">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
      </div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
              <i class="fas fa-exclamation-triangle text-red-600"></i>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
              <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                Delete Trending Item
              </h3>
              <div class="mt-2">
                <p class="text-sm text-gray-500">
                  Are you sure you want to delete this trending item? This action cannot be undone.
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <a href="#" id="confirmDelete" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
            Delete
          </a>
          <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
            Cancel
          </button>
        </div>
      </div>
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
          });

          // Hide all tab contents
          tabContents.forEach(content => {
            content.classList.add('hidden');
          });

          // Add active class to clicked tab
          this.classList.add('tab-active');
          this.classList.remove('text-gray-500');

          // Show corresponding tab content
          const targetId = this.getAttribute('data-target');
          document.getElementById(targetId).classList.remove('hidden');
        });
      });
    });

    function confirmDelete(id) {
      const modal = document.getElementById('deleteModal');
      const confirmLink = document.getElementById('confirmDelete');

      modal.classList.remove('hidden');
      confirmLink.href = 'trending.php?delete=' + id;
    }

    function closeModal() {
      const modal = document.getElementById('deleteModal');
      modal.classList.add('hidden');
    }

    // Close modal when clicking on the backdrop
    document.addEventListener('click', function(event) {
      const modal = document.getElementById('deleteModal');
      if (event.target === modal) {
        closeModal();
      }
    });

    // Close alert messages
    document.querySelectorAll('[role="alert"] svg').forEach(function(btn) {
      btn.addEventListener('click', function() {
        this.closest('[role="alert"]').remove();
      });
    });
  </script>
</body>

</html>