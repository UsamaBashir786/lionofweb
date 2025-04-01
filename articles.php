<?php
// Include database connection
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get article slug from URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($slug)) {
  // Redirect to homepage if no slug provided
  header('Location: index.php');
  exit;
}

// Fetch article data
try {
  $stmt = $conn->prepare("
        SELECT a.*, c.name as category_name, c.slug as category_slug, u.name as author_name 
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        LEFT JOIN users u ON a.author_id = u.id
        WHERE a.slug = :slug AND a.status = 'published'
    ");
  $stmt->bindParam(':slug', $slug);
  $stmt->execute();

  // Check if article exists
  if ($stmt->rowCount() == 0) {
    // Article not found, redirect to 404 page
    header('Location: 404.php');
    exit;
  }

  // Get article data
  $article = $stmt->fetch();

  // Log article view
  logArticleView($conn, $article['id']);

  // Get article view count
  $view_count = getArticleViewCount($conn, $article['id']);

  // Format dates
  $published_date = formatDate($article['publish_date'] ?? $article['created_at']);
  $time_ago = timeAgo($article['publish_date'] ?? $article['created_at']);

  // Get social sharing URLs
  $page_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
  $twitter_url = "https://twitter.com/intent/tweet?url=" . urlencode($page_url) . "&text=" . urlencode($article['title']);
  $facebook_url = "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($page_url);
  $linkedin_url = "https://www.linkedin.com/shareArticle?mini=true&url=" . urlencode($page_url) . "&title=" . urlencode($article['title']);
  $whatsapp_url = "https://api.whatsapp.com/send?text=" . urlencode($article['title'] . ' ' . $page_url);

  // Get related articles
  $stmt = $conn->prepare("
        SELECT a.*, c.name as category_name, c.slug as category_slug
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.category_id = :category_id 
        AND a.id != :article_id 
        AND a.status = 'published'
        ORDER BY a.created_at DESC
        LIMIT 3
    ");
  $stmt->bindParam(':category_id', $article['category_id']);
  $stmt->bindParam(':article_id', $article['id']);
  $stmt->execute();
  $related_articles = $stmt->fetchAll();

  // Category colors mapping
  $category_colors = [
    'Breaking' => 'bg-red-100 text-red-600',
    'Finance' => 'bg-yellow-100 text-yellow-600',
    'Education' => 'bg-blue-100 text-blue-600',
    'Health' => 'bg-green-100 text-green-600',
    'Fashion' => 'bg-pink-100 text-pink-600',
    'Technology' => 'bg-purple-100 text-purple-600',
    'Sports' => 'bg-red-100 text-red-600',
    'Business' => 'bg-blue-100 text-blue-600'
  ];

  // Get color class for the category
  $color_class = $category_colors[$article['category_name']] ?? 'bg-gray-100 text-gray-600';
} catch (PDOException $e) {
  // Handle database error
  die("Database error: " . $e->getMessage());
}

// Meta tags for SEO
$meta_title = !empty($article['meta_title']) ? $article['meta_title'] : $article['title'];
$meta_description = !empty($article['meta_description']) ? $article['meta_description'] : generateExcerpt($article['content'], 160);
$meta_keywords = !empty($article['meta_keywords']) ? $article['meta_keywords'] : '';

// Social media meta tags
$social_title = !empty($article['social_title']) ? $article['social_title'] : $meta_title;
$social_description = !empty($article['social_description']) ? $article['social_description'] : $meta_description;
$social_image = !empty($article['social_image']) ? $article['social_image'] : $article['image'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($meta_title); ?> - NewsHub</title>

  <!-- Meta tags for SEO -->
  <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
  <?php if (!empty($meta_keywords)): ?>
    <meta name="keywords" content="<?php echo htmlspecialchars($meta_keywords); ?>">
  <?php endif; ?>

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="article">
  <meta property="og:url" content="<?php echo htmlspecialchars($page_url); ?>">
  <meta property="og:title" content="<?php echo htmlspecialchars($social_title); ?>">
  <meta property="og:description" content="<?php echo htmlspecialchars($social_description); ?>">
  <?php if (!empty($social_image)): ?>
    <meta property="og:image" content="<?php echo htmlspecialchars($social_image); ?>">
  <?php endif; ?>

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="<?php echo htmlspecialchars($page_url); ?>">
  <meta property="twitter:title" content="<?php echo htmlspecialchars($social_title); ?>">
  <meta property="twitter:description" content="<?php echo htmlspecialchars($social_description); ?>">
  <?php if (!empty($social_image)): ?>
    <meta property="twitter:image" content="<?php echo htmlspecialchars($social_image); ?>">
  <?php endif; ?>

  <link rel="shortcut icon" href="assets/images/logo.png" type="image/x-icon">
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
      <!-- Breadcrumb -->
      <nav class="text-sm text-gray-500 mb-6" aria-label="Breadcrumb">
        <ol class="list-none p-0 inline-flex">
          <li class="flex items-center">
            <a href="index.php" class="hover:text-blue-600 transition duration-300">Home</a>
            <svg class="w-3 h-3 mx-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
          </li>
          <li class="flex items-center">
            <a href="category.php?slug=<?php echo htmlspecialchars($article['category_slug']); ?>" class="hover:text-blue-600 transition duration-300"><?php echo htmlspecialchars($article['category_name']); ?></a>
            <svg class="w-3 h-3 mx-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
          </li>
          <li>
            <span class="text-gray-700" aria-current="page"><?php echo htmlspecialchars($article['title']); ?></span>
          </li>
        </ol>
      </nav>

      <!-- Article Header -->
      <div class="mb-6" data-aos="fade-up" data-aos-duration="800">
        <span class="inline-block px-3 py-1 <?php echo $color_class; ?> rounded-full text-sm font-semibold mb-3">
          <?php echo htmlspecialchars($article['category_name']); ?>
        </span>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3"><?php echo htmlspecialchars($article['title']); ?></h1>

        <?php if (!empty($article['subtitle'])): ?>
          <h2 class="text-xl text-gray-600 mb-4"><?php echo htmlspecialchars($article['subtitle']); ?></h2>
        <?php endif; ?>

        <div class="flex flex-wrap items-center text-gray-500 text-sm mb-4">
          <?php if (!empty($article['author_name'])): ?>
            <span class="mr-4 mb-2"><i class="fas fa-user mr-1"></i> <?php echo htmlspecialchars($article['author_name']); ?></span>
          <?php endif; ?>
          <span class="mr-4 mb-2"><i class="far fa-calendar mr-1"></i> <?php echo $published_date; ?></span>
          <span class="mr-4 mb-2"><i class="far fa-clock mr-1"></i> <?php echo $time_ago; ?></span>
          <span class="mb-2"><i class="far fa-eye mr-1"></i> <?php echo number_format($view_count); ?> views</span>
        </div>

        <!-- Social Share Buttons -->
        <div class="flex items-center mt-4">
          <span class="text-gray-600 mr-3">Share:</span>
          <div class="flex space-x-2">
            <a href="<?php echo $facebook_url; ?>" target="_blank" rel="noopener noreferrer" class="bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700 transition duration-300">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="<?php echo $twitter_url; ?>" target="_blank" rel="noopener noreferrer" class="bg-blue-400 text-white p-2 rounded-full hover:bg-blue-500 transition duration-300">
              <i class="fab fa-twitter"></i>
            </a>
            <a href="<?php echo $linkedin_url; ?>" target="_blank" rel="noopener noreferrer" class="bg-blue-700 text-white p-2 rounded-full hover:bg-blue-800 transition duration-300">
              <i class="fab fa-linkedin-in"></i>
            </a>
            <a href="<?php echo $whatsapp_url; ?>" target="_blank" rel="noopener noreferrer" class="bg-green-500 text-white p-2 rounded-full hover:bg-green-600 transition duration-300">
              <i class="fab fa-whatsapp"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- Featured Image -->
      <?php if (!empty($article['image'])): ?>
        <div class="mb-6 rounded-lg overflow-hidden shadow-lg" data-aos="fade-up" data-aos-duration="800">
          <img src="<?php echo htmlspecialchars($article['image']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full h-auto">
        </div>
      <?php endif; ?>

      <!-- Article Content -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-8 article-content" data-aos="fade-up" data-aos-duration="800">
        <?php if (!empty($article['excerpt'])): ?>
          <div class="text-lg text-gray-700 font-medium italic mb-6 border-l-4 border-blue-500 pl-4 py-2 bg-blue-50">
            <?php echo htmlspecialchars($article['excerpt']); ?>
          </div>
        <?php endif; ?>

        <div class="prose prose-lg max-w-none text-gray-800">
          <?php echo $article['content']; ?>
        </div>

        <?php if (!empty($article['source']) || !empty($article['source_url'])): ?>
          <div class="mt-6 text-sm text-gray-500">
            <p>
              Source:
              <?php if (!empty($article['source_url'])): ?>
                <a href="<?php echo htmlspecialchars($article['source_url']); ?>" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">
                  <?php echo !empty($article['source']) ? htmlspecialchars($article['source']) : 'Link'; ?>
                </a>
              <?php else: ?>
                <?php echo htmlspecialchars($article['source']); ?>
              <?php endif; ?>
            </p>
          </div>
        <?php endif; ?>

        <?php if (!empty($article['tags'])): ?>
          <div class="mt-8 pt-4 border-t border-gray-200">
            <h3 class="text-gray-600 text-sm mb-2">Tags:</h3>
            <div class="flex flex-wrap gap-2">
              <?php foreach (explode(',', $article['tags']) as $tag): ?>
                <a href="tag.php?tag=<?php echo urlencode(trim($tag)); ?>" class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-3 py-1 rounded-full text-sm transition duration-300">
                  <?php echo htmlspecialchars(trim($tag)); ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Author Box (if author is available) -->
      <?php if (!empty($article['author_name'])): ?>
        <div class="bg-white rounded-lg shadow-md p-6 mb-8" data-aos="fade-up" data-aos-duration="800">
          <h3 class="text-xl font-bold text-gray-800 mb-4">About the Author</h3>
          <div class="flex items-center">
            <div class="flex-shrink-0 mr-4">
              <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center text-gray-500">
                <i class="fas fa-user text-2xl"></i>
              </div>
            </div>
            <div>
              <h4 class="text-lg font-semibold"><?php echo htmlspecialchars($article['author_name']); ?></h4>
              <p class="text-gray-600">Author at NewsHub</p>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Related Articles -->
      <?php if (count($related_articles) > 0): ?>
        <div class="mb-8" data-aos="fade-up" data-aos-duration="800">
          <h3 class="text-2xl font-bold text-gray-800 mb-4">Related Articles</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($related_articles as $related):
              // Get color class for the category
              $rel_color_class = $category_colors[$related['category_name']] ?? 'bg-gray-100 text-gray-600';

              // Create article URL
              $rel_article_url = "article.php?slug=" . $related['slug'];

              // Get article image or use placeholder
              $rel_image_path = !empty($related['image']) ? $related['image'] : "/api/placeholder/400/200";
            ?>
              <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition duration-300">
                <a href="<?php echo $rel_article_url; ?>">
                  <img src="<?php echo htmlspecialchars($rel_image_path); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>" class="w-full h-40 object-cover">
                </a>
                <div class="p-4">
                  <span class="inline-block px-2 py-1 <?php echo $rel_color_class; ?> rounded-full text-xs font-semibold mb-2">
                    <?php echo htmlspecialchars($related['category_name']); ?>
                  </span>
                  <h4 class="font-bold text-gray-800 mb-2">
                    <a href="<?php echo $rel_article_url; ?>" class="hover:text-blue-600 transition duration-300">
                      <?php echo htmlspecialchars($related['title']); ?>
                    </a>
                  </h4>
                  <p class="text-gray-600 text-sm mb-3"><?php echo limitWords(strip_tags($related['content']), 10); ?></p>
                  <a href="<?php echo $rel_article_url; ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium transition duration-300">Read More</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Comments Section -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-8" data-aos="fade-up" data-aos-duration="800">
        <h3 class="text-2xl font-bold text-gray-800 mb-4">Comments</h3>

        <!-- Comments Form -->
        <form action="post-comment.php" method="post" class="mb-8">
          <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
          <div class="mb-4">
            <label for="name" class="block text-gray-700 font-medium mb-2">Your Name</label>
            <input type="text" id="name" name="name" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div class="mb-4">
            <label for="email" class="block text-gray-700 font-medium mb-2">Your Email</label>
            <input type="email" id="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div class="mb-4">
            <label for="comment" class="block text-gray-700 font-medium mb-2">Your Comment</label>
            <textarea id="comment" name="comment" rows="4" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
          </div>
          <button type="submit" class="bg-blue-600 text-white font-semibold py-2 px-6 rounded-md hover:bg-blue-700 transition duration-300">
            Post Comment
          </button>
        </form>

        <!-- Placeholder for comments - in a real application, you would load comments from database -->
        <div class="space-y-6">
          <p class="text-gray-500 text-center py-4">No comments yet. Be the first to comment!</p>

          <!-- Example Comment (hidden, just for structure) -->
          <div class="border-b border-gray-200 pb-6 hidden">
            <div class="flex items-center mb-2">
              <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 mr-3">
                <i class="fas fa-user"></i>
              </div>
              <div>
                <h4 class="font-semibold">John Doe</h4>
                <p class="text-sm text-gray-500">2 days ago</p>
              </div>
            </div>
            <p class="text-gray-700">This is an example comment. Great article!</p>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <?php include 'includes/footer.php'; ?>

  <script src="assets/js/script.js"></script>
  <script src="node_modules/aos/dist/aos.js"></script>
  <script>
    AOS.init();
  </script>
</body>

</html>