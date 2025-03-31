<?php
// Include database connection
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NewsHub - Your Source for Latest Information</title>
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

  <!-- Hero Section -->
  <?php include 'includes/hero.php'; ?>

  <!-- Breaking News Ticker -->
  <div class="bg-red-600 text-white py-3 mb-8">
    <div class="container mx-auto px-4">
      <div class="flex items-center overflow-hidden">
        <span class="font-bold text-lg mr-4 whitespace-nowrap">BREAKING:</span>
        <div class="news-ticker overflow-hidden whitespace-nowrap">
          <span class="inline-block animate-marquee">
            Global markets respond to recent economic developments • New health research reveals breakthrough findings • Major tech companies announce new partnership • Sports championship final results announced • Weather alert: Storm warning for coastal regions
          </span>
        </div>
      </div>
    </div>
  </div>

  <main class="container mx-auto px-4 mb-12">
    <!-- Featured Stories Section -->
    <!-- Featured Stories Section -->
    <section id="featured" class="mb-12 pt-8">
      <div class="flex items-center justify-between mb-6" data-aos="fade-up" data-aos-duration="800">
        <h2 class="text-3xl font-bold text-gray-800">Featured Stories</h2>
        <a href="articles.php" class="text-blue-600 hover:text-blue-800 font-semibold transition duration-300">View All <i class="fas fa-chevron-right text-xs ml-1"></i></a>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php
        // Get featured articles from the database
        $featured_articles = $conn->prepare("
      SELECT a.*, c.name as category_name, c.slug as category_slug 
      FROM articles a
      LEFT JOIN categories c ON a.category_id = c.id
      WHERE a.featured = 1 AND a.status = 'published'
      ORDER BY a.created_at DESC
      LIMIT 3
    ");
        $featured_articles->execute();

        // Check if we have any featured articles
        if ($featured_articles->rowCount() > 0) {
          $delay = 100;
          while ($article = $featured_articles->fetch()) {
            // Determine category color classes based on category name or id
            $category_colors = [
              'Business' => 'bg-blue-100 text-blue-600',
              'Health' => 'bg-green-100 text-green-600',
              'Technology' => 'bg-purple-100 text-purple-600',
              'Sports' => 'bg-red-100 text-red-600',
              'Education' => 'bg-yellow-100 text-yellow-600'
              // Add more categories as needed
            ];

            // Default color if category not found in the mapping
            $color_class = $category_colors[$article['category_name']] ?? 'bg-gray-100 text-gray-600';

            // Format date/time for display
            $time_ago = timeAgo($article['created_at']);

            // Generate article URL - assuming article.php with slug parameter
            $article_url = "article.php?slug=" . $article['slug'];
            $category_url = $article['category_slug'] . ".php";

            // Image path with fallback
            $image_path = !empty($article['image']) ? $article['image'] : "admin/";
        ?>
            <!-- Featured Story -->
            <div class="bg-white rounded-lg overflow-hidden shadow-md hover:shadow-lg transition duration-300" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>" data-aos-duration="800">
              <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full h-48 object-cover">
              <div class="p-5">
                <span class="inline-block px-3 py-1 <?php echo $color_class; ?> rounded-full text-sm font-semibold mb-3">
                  <?php echo htmlspecialchars($article['category_name']); ?>
                </span>
                <h3 class="text-xl font-bold mb-2 text-gray-800"><?php echo htmlspecialchars($article['title']); ?></h3>
                <p class="text-gray-600 mb-4"><?php echo limitWords(strip_tags($article['content']), 15); ?></p>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-500"><i class="far fa-clock mr-1"></i> <?php echo $time_ago; ?></span>
                  <a href="<?php echo $article_url; ?>" class="text-blue-600 hover:text-blue-800 font-medium transition duration-300">Read More</a>
                </div>
              </div>
            </div>
          <?php
            $delay += 100;
          }
        } else {
          // Fallback content if no featured articles exist
          ?>
          <div class="col-span-full text-center py-8">
            <p class="text-gray-500">No featured stories available at the moment. Check back soon!</p>
          </div>
        <?php
        }
        ?>
      </div>
    </section>

    <!-- Latest News Section -->
    <section class="mb-12">
      <div class="flex items-center justify-between mb-6" data-aos="fade-up" data-aos-duration="800">
        <h2 class="text-3xl font-bold text-gray-800">Latest News</h2>
        <div class="flex gap-2">
          <button id="latest-prev" class="p-2 bg-gray-200 hover:bg-gray-300 rounded-full transition">
            <i class="fas fa-chevron-left"></i>
          </button>
          <button id="latest-next" class="p-2 bg-gray-200 hover:bg-gray-300 rounded-full transition">
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Latest News Column 1 -->
        <div>
          <?php
          // Get latest 3 articles for the left column
          $latest_articles1 = $conn->prepare("
        SELECT a.*, c.name as category_name, c.slug as category_slug 
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.status = 'published'
        ORDER BY a.created_at DESC
        LIMIT 0, 3
      ");
          $latest_articles1->execute();

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

          if ($latest_articles1->rowCount() > 0) {
            $delay = 100;
            while ($article = $latest_articles1->fetch()) {
              // Get color class for the category
              $color_class = $category_colors[$article['category_name']] ?? 'bg-gray-100 text-gray-600';

              // Get article image or use placeholder
              $image_path = !empty($article['image']) ? $article['image'] : "/api/placeholder/120/120";

              // Create article URL
              $article_url = "article.php?slug=" . $article['slug'];

              // Get short excerpt for preview
              $excerpt = limitWords(strip_tags($article['content']), 15);
          ?>
              <!-- News Item -->
              <div class="flex gap-4 mb-6 bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition duration-300" data-aos="fade-right" data-aos-delay="<?php echo $delay; ?>" data-aos-duration="800">
                <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-24 h-24 object-cover rounded">
                <div>
                  <span class="inline-block px-2 py-1 <?php echo $color_class; ?> rounded-full text-xs font-semibold mb-2"><?php echo htmlspecialchars($article['category_name']); ?></span>
                  <h3 class="font-bold text-gray-800 mb-1"><?php echo htmlspecialchars($article['title']); ?></h3>
                  <p class="text-gray-600 text-sm"><?php echo $excerpt; ?></p>
                  <a href="<?php echo $article_url; ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2 inline-block transition duration-300">Read More</a>
                </div>
              </div>
            <?php
              $delay += 100;
            }
          } else {
            // If no articles found, show a message
            ?>
            <div class="flex gap-4 mb-6 bg-white p-4 rounded-lg shadow-sm">
              <p class="text-gray-600 text-sm">No recent articles available at the moment.</p>
            </div>
          <?php
          }
          ?>
        </div>

        <!-- Latest News Column 2 -->
        <div>
          <?php
          // Get next 3 articles for the right column
          $latest_articles2 = $conn->prepare("
        SELECT a.*, c.name as category_name, c.slug as category_slug 
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.status = 'published'
        ORDER BY a.created_at DESC
        LIMIT 3, 3
      ");
          $latest_articles2->execute();

          if ($latest_articles2->rowCount() > 0) {
            $delay = 100;
            while ($article = $latest_articles2->fetch()) {
              // Get color class for the category
              $color_class = $category_colors[$article['category_name']] ?? 'bg-gray-100 text-gray-600';

              // Get article image or use placeholder
              $image_path = !empty($article['image']) ? $article['image'] : "/api/placeholder/120/120";

              // Create article URL
              $article_url = "article.php?slug=" . $article['slug'];

              // Get short excerpt for preview
              $excerpt = limitWords(strip_tags($article['content']), 15);
          ?>
              <!-- News Item -->
              <div class="flex gap-4 mb-6 bg-white p-4 rounded-lg shadow-sm hover:shadow-md transition duration-300" data-aos="fade-left" data-aos-delay="<?php echo $delay; ?>" data-aos-duration="800">
                <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-24 h-24 object-cover rounded">
                <div>
                  <span class="inline-block px-2 py-1 <?php echo $color_class; ?> rounded-full text-xs font-semibold mb-2"><?php echo htmlspecialchars($article['category_name']); ?></span>
                  <h3 class="font-bold text-gray-800 mb-1"><?php echo htmlspecialchars($article['title']); ?></h3>
                  <p class="text-gray-600 text-sm"><?php echo $excerpt; ?></p>
                  <a href="<?php echo $article_url; ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2 inline-block transition duration-300">Read More</a>
                </div>
              </div>
            <?php
              $delay += 100;
            }
          } else {
            // If no articles found, show a message
            ?>
            <div class="flex gap-4 mb-6 bg-white p-4 rounded-lg shadow-sm">
              <p class="text-gray-600 text-sm">No additional articles available at the moment.</p>
            </div>
          <?php
          }
          ?>
        </div>
      </div>
    </section>

    <!-- Categories Section -->
    <section id="categories" class="mb-12">
      <h2 class="text-3xl font-bold text-gray-800 mb-6" data-aos="fade-up" data-aos-duration="800">Browse Categories</h2>

      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <!-- Category 1 -->
        <a href="news-english.php" class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md text-center transition duration-300" data-aos="zoom-in" data-aos-delay="100" data-aos-duration="600">
          <div class="bg-blue-100 text-blue-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-newspaper text-2xl"></i>
          </div>
          <h3 class="font-semibold text-gray-800">English News</h3>
        </a>

        <!-- Category 2 -->
        <a href="news-urdu.php" class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md text-center transition duration-300" data-aos="zoom-in" data-aos-delay="150" data-aos-duration="600">
          <div class="bg-green-100 text-green-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-language text-2xl"></i>
          </div>
          <h3 class="font-semibold text-gray-800">Urdu News</h3>
        </a>

        <!-- Category 3 -->
        <a href="business.php" class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md text-center transition duration-300" data-aos="zoom-in" data-aos-delay="200" data-aos-duration="600">
          <div class="bg-yellow-100 text-yellow-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-chart-line text-2xl"></i>
          </div>
          <h3 class="font-semibold text-gray-800">Business</h3>
        </a>

        <!-- Category 4 -->
        <a href="sports.php" class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md text-center transition duration-300" data-aos="zoom-in" data-aos-delay="250" data-aos-duration="600">
          <div class="bg-red-100 text-red-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-futbol text-2xl"></i>
          </div>
          <h3 class="font-semibold text-gray-800">Sports</h3>
        </a>

        <!-- Category 5 -->
        <a href="health.php" class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md text-center transition duration-300" data-aos="zoom-in" data-aos-delay="300" data-aos-duration="600">
          <div class="bg-green-100 text-green-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-heartbeat text-2xl"></i>
          </div>
          <h3 class="font-semibold text-gray-800">Health</h3>
        </a>

        <!-- Category 6 -->
        <a href="technology.php" class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md text-center transition duration-300" data-aos="zoom-in" data-aos-delay="350" data-aos-duration="600">
          <div class="bg-purple-100 text-purple-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-mobile-alt text-2xl"></i>
          </div>
          <h3 class="font-semibold text-gray-800">Technology</h3>
        </a>

        <!-- Category 7 -->
        <a href="education.php" class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md text-center transition duration-300" data-aos="zoom-in" data-aos-delay="400" data-aos-duration="600">
          <div class="bg-blue-100 text-blue-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-graduation-cap text-2xl"></i>
          </div>
          <h3 class="font-semibold text-gray-800">Education</h3>
        </a>

        <!-- Category 8 -->
        <a href="jobs.php" class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md text-center transition duration-300" data-aos="zoom-in" data-aos-delay="450" data-aos-duration="600">
          <div class="bg-green-100 text-green-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-briefcase text-2xl"></i>
          </div>
          <h3 class="font-semibold text-gray-800">Jobs</h3>
        </a>

        <!-- Category 9 -->
        <a href="eshop.php" class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md text-center transition duration-300" data-aos="zoom-in" data-aos-delay="500" data-aos-duration="600">
          <div class="bg-pink-100 text-pink-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-shopping-cart text-2xl"></i>
          </div>
          <h3 class="font-semibold text-gray-800">E-Shop</h3>
        </a>

        <!-- Category 10 -->
        <a href="crypto.php" class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md text-center transition duration-300" data-aos="zoom-in" data-aos-delay="550" data-aos-duration="600">
          <div class="bg-yellow-100 text-yellow-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fab fa-bitcoin text-2xl"></i>
          </div>
          <h3 class="font-semibold text-gray-800">Crypto</h3>
        </a>

        <!-- Category 11 -->
        <a href="travel.php" class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md text-center transition duration-300" data-aos="zoom-in" data-aos-delay="600" data-aos-duration="600">
          <div class="bg-blue-100 text-blue-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-plane text-2xl"></i>
          </div>
          <h3 class="font-semibold text-gray-800">Travel</h3>
        </a>

        <!-- Category 12 -->
        <a href="islam.php" class="bg-white p-4 rounded-lg shadow-sm hover:shadow-md text-center transition duration-300" data-aos="zoom-in" data-aos-delay="650" data-aos-duration="600">
          <div class="bg-green-100 text-green-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-mosque text-2xl"></i>
          </div>
          <h3 class="font-semibold text-gray-800">Islamic</h3>
        </a>
      </div>

      <div class="text-center mt-6" data-aos="fade-up" data-aos-duration="800">
        <a href="#" class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-6 rounded-full transition duration-300">
          View All Categories <i class="fas fa-arrow-right ml-2"></i>
        </a>
      </div>
    </section>

    <!-- Trending Section -->
    <?php include 'includes/trending-section.php'; ?>

    <!-- Newsletter Section -->
    <section class="bg-blue-600 text-white rounded-lg p-8 mb-12" data-aos="fade-up" data-aos-duration="1000">
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
    </section>
  </main>

  <!-- Footer will be loaded here -->
  <?php include 'includes/footer.php'; ?>
  <script src="assets/js/script.js"></script>
  <script src="assets/js/api.js"></script>
  <script src="node_modules/aos/dist/aos.js"></script>
  <script>
    AOS.init();
  </script>
</body>

</html>