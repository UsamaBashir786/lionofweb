<?php
// File: trending-detail.php

// Include configuration files
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Initialize error message variable
$error_message = '';

// Check if trending item ID is provided and is a valid number
if (!isset($_GET['id'])) {
  $error_message = "No trending item ID was provided. Please select an article from the main page.";
  // Optional: Log this occurrence
  error_log("Trending detail access without ID: " . print_r($_SERVER, true));
} elseif (!filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
  $error_message = "Invalid trending item ID. The ID must be a valid number.";
  // Optional: Log this occurrence
  error_log("Invalid trending item ID attempted: " . htmlspecialchars($_GET['id']));
}

// If no error so far, proceed with fetching trending item
$trending_item = null;
if (empty($error_message)) {
  $trending_id = (int)$_GET['id'];

  // Get trending item details
  try {
    $stmt = $conn->prepare("SELECT * FROM trending_items WHERE id = ? AND active = 1");
    $stmt->execute([$trending_id]);
    $trending_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$trending_item) {
      // Set error message if no item found
      $error_message = "No active trending item found with the specified ID. It may have been removed or is no longer available.";
      // Optional: Log this occurrence
      error_log("Trending item not found or not active: ID " . $trending_id);
    }
  } catch (PDOException $e) {
    // Log the error and set a user-friendly error message
    error_log("Database error fetching trending item: " . $e->getMessage());
    $error_message = "A database error occurred. Please try again later.";
  }
}

// Get related trending items (only if no error and item found)
$related_items = [];
if (empty($error_message) && $trending_item) {
  try {
    $stmt = $conn->prepare("
            SELECT * FROM trending_items 
            WHERE id != ? AND active = 1 
            ORDER BY position ASC 
            LIMIT 3
        ");
    $stmt->execute([$trending_id]);
    $related_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    error_log("Error fetching related trending items: " . $e->getMessage());
    // We won't set an error message for this as it's not a critical failure
  }
}

// Prepare page title and meta information
$page_title = !empty($error_message) ? "Error - NewsHub" : ($trending_item['title'] . " - NewsHub");
$meta_description = !empty($error_message) ? "An error occurred while loading the article." : (!empty($trending_item['meta_description']) ? $trending_item['meta_description'] : $trending_item['description']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($page_title); ?></title>

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-100 font-sans">
  <main class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
      <?php if (!empty($error_message)): ?>
        <!-- Error Message Section -->
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
          <strong class="font-bold">Error: </strong>
          <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
        </div>

        <!-- Helpful Navigation -->
        <div class="mt-6 text-center">
          <a href="index.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-300">
            Return to Home Page
          </a>
        </div>

      <?php else: ?>
        <!-- Trending Item Content -->
        <article class="bg-white p-8 rounded-lg shadow-md">
          <h1 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars($trending_item['title']); ?></h1>

          <?php if (!empty($trending_item['featured_image'])): ?>
            <div class="mb-6">
              <img src="<?php echo htmlspecialchars($trending_item['featured_image']); ?>"
                alt="<?php echo htmlspecialchars($trending_item['title']); ?>"
                class="w-full h-auto rounded-lg">
            </div>
          <?php endif; ?>

          <div class="prose max-w-none">
            <?php echo $trending_item['content']; ?>
          </div>
        </article>
      <?php endif; ?>
    </div>
  </main>
</body>

</html>