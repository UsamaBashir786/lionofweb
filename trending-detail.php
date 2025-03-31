<?php
// File: trending-detail.php

// Include configuration files
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if trending item ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  // No valid ID provided, redirect to homepage
  header("Location: index.php");
  exit;
}

$trending_id = (int)$_GET['id'];

// Get trending item details
try {
  $stmt = $conn->prepare("SELECT * FROM trending_items WHERE id = ? AND active = 1");
  $stmt->execute([$trending_id]);
  $trending_item = $stmt->fetch();

  if (!$trending_item) {
    // Item not found or not active, redirect to homepage
    header("Location: index.php");
    exit;
  }
} catch (PDOException $e) {
  error_log("Error fetching trending item: " . $e->getMessage());
  header("Location: index.php");
  exit;
}

// Get related trending items
$related_items = [];
try {
  $stmt = $conn->prepare("
    SELECT * FROM trending_items 
    WHERE id != ? AND active = 1 
    ORDER BY position ASC 
    LIMIT 3
  ");
  $stmt->execute([$trending_id]);
  $related_items = $stmt->fetchAll();
} catch (PDOException $e) {
  error_log("Error fetching related trending items: " . $e->getMessage());
}

// Format timestamp for display
$publish_date = date('F j, Y', strtotime($trending_item['publish_date']));

// Set page title and meta tags
$page_title = $trending_item['title'] . " - NewsHub";
$meta_description = !empty($trending_item['meta_description']) ? $trending_item['meta_description'] : $trending_item['description'];
$meta_keywords = !empty($trending_item['meta_keywords']) ? $trending_item['meta_keywords'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($page_title); ?></title>

  <!-- Meta Tags -->
  <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
  <?php if (!empty($meta_keywords)): ?>
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>">
  <?php endif; ?>

  <!-- Open Graph Tags -->
  <meta property="og:title" content="<?php echo htmlspecialchars($trending_item['title']); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($meta_description); ?>">
  <?php if (!empty($trending_item['featured_image'])): ?>
    <meta property="og:image" content="<?php echo SITE_URL . '/' . htmlspecialchars($trending_item['featured_image']); ?>">
  <?php endif; ?>
  <meta property="og:type" content="article">
  <meta property="og:url" content="<?php echo SITE_URL; ?>/trending-detail.php?id=<?php echo $trending_id; ?>">

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="node_modules/aos/dist/aos.css">
</head>

<body class="bg-gray-100 font-sans">
  <?php include 'includes/navbar.php'; ?>

  <main class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
      <!-- Breadcrumbs -->
      <nav class="text-sm mb-6 text-gray-500" aria-label="Breadcrumb">
        <ol class="list-none p-0 inline-flex">
          <li class="flex items-center">
            <a href="index.php" class="hover:text-blue-600">Home</a>
            <svg class="fill-current w-3 h-3 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
              <path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z" />
            </svg>
          </li>
          <li class="flex items-center">
            <a href="#" class="hover:text-blue-600">Trending</a>
            <svg class="fill-current w-3 h-3 mx-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
              <path d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z" />
            </svg>
          </li>
          <li>
            <span class="text-gray-700"><?php echo htmlspecialchars($trending_item['title']); ?></span>
          </li>
        </ol>
      </nav>

      <!-- Article Header -->
      <header class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($trending_item['title']); ?></h1>

        <div class="flex items-center text-gray-600 mb-6">
          <?php if (!empty($trending_item['author'])): ?>
            <div class="flex items-center mr-6">
              <i class="fas fa-user mr-2"></i>
              <span><?php echo htmlspecialchars($trending_item['author']); ?></span>
            </div>
          <?php endif; ?>

          <div class="flex items-center">
            <i class="fas fa-calendar-alt mr-2"></i>
            <span><?php echo $publish_date; ?></span>
          </div>
        </div>

        <p class="text-xl text-gray-700 leading-relaxed"><?php echo htmlspecialchars($trending_item['description']); ?></p>
      </header>

      <!-- Featured Image -->
      <?php if (!empty($trending_item['featured_image'])): ?>
        <div class="mb-8">
          <img src="<?php echo htmlspecialchars($trending_item['featured_image']); ?>" alt="<?php echo htmlspecialchars($trending_item['title']); ?>" class="rounded-lg w-full h-auto object-cover shadow-md">
        </div>
      <?php endif; ?>

      <!-- Article Content -->
      <article class="prose prose-lg max-w-none bg-white rounded-lg shadow-md p-8 mb-8">
        <?php echo $trending_item['content']; ?>
      </article>

      <!-- Tags Section -->
      <?php if (!empty($trending_item['meta_keywords'])): ?>
        <div class="mb-8">
          <h3 class="text-lg font-semibold text-gray-800 mb-3">Tags</h3>
          <div class="flex flex-wrap gap-2">
            <?php
            $keywords = explode(',', $trending_item['meta_keywords']);
            foreach ($keywords as $keyword):
              $keyword = trim($keyword);
              if (!empty($keyword)):
            ?>
                <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full"><?php echo htmlspecialchars($keyword); ?></span>
            <?php
              endif;
            endforeach;
            ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Share Buttons -->
      <div class="flex items-center py-4 border-t border-b border-gray-200 mb-8">
        <span class="text-gray-700 font-medium mr-4">Share this article:</span>
        <div class="flex space-x-3">
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL . '/trending-detail.php?id=' . $trending_id); ?>" target="_blank" class="text-blue-600 hover:text-blue-800">
            <i class="fab fa-facebook-f"></i>
          </a>
          <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL . '/trending-detail.php?id=' . $trending_id); ?>&text=<?php echo urlencode($trending_item['title']); ?>" target="_blank" class="text-blue-400 hover:text-blue-600">
            <i class="fab fa-twitter"></i>
          </a>
          <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode(SITE_URL . '/trending-detail.php?id=' . $trending_id); ?>&title=<?php echo urlencode($trending_item['title']); ?>" target="_blank" class="text-blue-800 hover:text-blue-900">
            <i class="fab fa-linkedin-in"></i>
          </a>
          <a href="mailto:?subject=<?php echo urlencode($trending_item['title']); ?>&body=<?php echo urlencode('Check out this article: ' . SITE_URL . '/trending-detail.php?id=' . $trending_id); ?>" class="text-gray-600 hover:text-gray-800">
            <i class="fas fa-envelope"></i>
          </a>
        </div>
      </div>

      <?php if (!empty($related_items)): ?>
        <!-- Related Articles -->
        <section class="mb-8">
          <h2 class="text-2xl font-bold text-gray-800 mb-6">Related Articles</h2>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($related_items as $item): ?>
              <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                <?php if (!empty($item['image'])): ?>
                  <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-48 object-cover">
                <?php endif; ?>

                <div class="p-5">
                  <h3 class="font-bold text-lg mb-2 text-gray-800"><?php echo htmlspecialchars($item['title']); ?></h3>
                  <p class="text-gray-600 text-sm mb-3"><?php echo htmlspecialchars(substr($item['description'], 0, 100) . (strlen($item['description']) > 100 ? '...' : '')); ?></p>
                  <a href="trending-detail.php?id=<?php echo $item['id']; ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Read More</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
    </div>
  </main>

  <!-- Back to Top Button -->
  <button id="back-to-top" class="fixed bottom-6 right-6 p-4 rounded-full bg-blue-600 text-white shadow-lg hover:bg-blue-700 transition-all duration-300 opacity-0 invisible">
    <i class="fas fa-arrow-up"></i>
  </button>

  <?php include 'includes/footer.php'; ?>

  <script src="assets/js/script.js"></script>
  <script src="node_modules/aos/dist/aos.js"></script>
  <script>
    AOS.init();

    // Back to top button functionality
    const backToTop = document.getElementById('back-to-top');

    window.addEventListener('scroll', () => {
      if (window.scrollY > 300) {
        backToTop.classList.remove('opacity-0', 'invisible');
        backToTop.classList.add('opacity-100', 'visible');
      } else {
        backToTop.classList.add('opacity-0', 'invisible');
        backToTop.classList.remove('opacity-100', 'visible');
      }
    });

    backToTop.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  </script>
</body>

</html>