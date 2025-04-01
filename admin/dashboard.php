<?php

// Start session and check authentication
session_start();

// Debug session status
error_log("Dashboard.php - Session status: " . (session_status() === PHP_SESSION_ACTIVE ? 'active' : 'inactive'));
error_log("Dashboard.php - Admin logged in: " . (isset($_SESSION['admin_logged_in']) ? ($_SESSION['admin_logged_in'] ? 'true' : 'false') : 'not set'));
error_log("Dashboard.php - User role: " . (isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'not set'));

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  error_log("Dashboard.php - Access denied: admin_logged_in check failed");
  header("Location: login.php");
  exit;
}

// Check if user has admin or editor role
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'editor')) {
  error_log("Dashboard.php - Access denied: role check failed");
  header("Location: login.php");
  exit;
}

error_log("Dashboard.php - Access granted for user: " . $_SESSION['user_name']);

// Include configuration files
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Rest of your dashboard code continues...


function getTotalUsers($conn) {
  try {
      $stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users");
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return $result['total_users'] ?? 0;
  } catch (PDOException $e) {
      // Log the error
      error_log("Error getting total users: " . $e->getMessage());
      return 0;
  }
}

// Get dashboard statistics
$total_articles = getTotalArticles($conn);
$total_users = getTotalUsers($conn);
$total_categories = count(getAllCategories($conn, true));

// Get today's views (demo data for now, replace with actual analytics)
$today_views = rand(15000, 30000);

// Get recent articles
$recent_articles = getArticles($conn, 0, 4);

// Get recent user activity (demo data for now, replace with actual logs)
$recent_activity = [
  [
    'user' => 'Admin User',
    'action' => 'Published a new article: "Global Economic Trends for 2025"',
    'time' => '2h ago'
  ],
  [
    'user' => 'Editor Sarah',
    'action' => 'Updated the category: "Technology"',
    'time' => '5h ago'
  ],
  [
    'user' => 'Editor John',
    'action' => 'Created a draft: "New Mobile Technology Breakthroughs"',
    'time' => '1d ago'
  ],
  [
    'user' => 'Admin User',
    'action' => 'Added new user: "Editor Sarah"',
    'time' => '2d ago'
  ]
];

// Get traffic data for chart (demo data for now, replace with actual analytics)
$traffic_data = [
  'Jan' => ['views' => 65000, 'visitors' => 28000],
  'Feb' => ['views' => 59000, 'visitors' => 48000],
  'Mar' => ['views' => 80000, 'visitors' => 40000],
  'Apr' => ['views' => 81000, 'visitors' => 39000],
  'May' => ['views' => 56000, 'visitors' => 36000],
  'Jun' => ['views' => 85000, 'visitors' => 47000],
  'Jul' => ['views' => 90000, 'visitors' => 57000]
];

// Get category distribution data for chart (demo data for now, replace with actual counts)
$category_data = [
  'News' => 523,
  'Business' => 345,
  'Technology' => 438,
  'Health' => 256,
  'Sports' => 317,
  'Education' => 189,
  'Entertainment' => 274
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - NewsHub Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
  <style>
    .sidebar-active {
      border-left: 4px solid #3B82F6;
      background-color: rgba(59, 130, 246, 0.1);
    }

    .hover-trigger:hover .hover-target {
      display: block;
    }

    .hover-target {
      display: none;
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
            <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
          </div>
          <div class="px-4 mx-auto max-w-7xl sm:px-6 md:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-5 mt-6 sm:grid-cols-2 lg:grid-cols-4">
              <!-- Total Articles -->
              <div class="overflow-hidden bg-white rounded-lg shadow">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-blue-500 rounded-md">
                      <i class="fas fa-newspaper text-white"></i>
                    </div>
                    <div class="flex-1 w-0 ml-5">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Articles</dt>
                        <dd class="flex items-center text-2xl font-semibold text-gray-900">
                          <?php echo number_format($total_articles); ?>
                          <span class="ml-2 text-sm font-medium text-green-600">
                            <i class="fas fa-arrow-up"></i> 12%
                          </span>
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
                <div class="px-5 py-3 bg-gray-50">
                  <div class="text-sm">
                    <a href="news/manage-news.php" class="font-medium text-blue-700 hover:text-blue-900">View all</a>
                  </div>
                </div>
              </div>

              <!-- Active Users -->
              <div class="overflow-hidden bg-white rounded-lg shadow">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-green-500 rounded-md">
                      <i class="fas fa-users text-white"></i>
                    </div>
                    <div class="flex-1 w-0 ml-5">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Active Users</dt>
                        <dd class="flex items-center text-2xl font-semibold text-gray-900">
                          <?php echo number_format($total_users); ?>
                          <span class="ml-2 text-sm font-medium text-green-600">
                            <i class="fas fa-arrow-up"></i> 8.2%
                          </span>
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
                <div class="px-5 py-3 bg-gray-50">
                  <div class="text-sm">
                    <a href="users/manage-users.php" class="font-medium text-green-700 hover:text-green-900">View all</a>
                  </div>
                </div>
              </div>

              <!-- Today's Views -->
              <div class="overflow-hidden bg-white rounded-lg shadow">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-yellow-500 rounded-md">
                      <i class="fas fa-eye text-white"></i>
                    </div>
                    <div class="flex-1 w-0 ml-5">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Today's Views</dt>
                        <dd class="flex items-center text-2xl font-semibold text-gray-900">
                          <?php echo number_format($today_views); ?>
                          <span class="ml-2 text-sm font-medium text-red-600">
                            <i class="fas fa-arrow-down"></i> 3.2%
                          </span>
                        </dd>
                      </dl>
                    </div>
                  </div>
                </div>
                <div class="px-5 py-3 bg-gray-50">
                  <div class="text-sm">
                    <a href="#" class="font-medium text-yellow-700 hover:text-yellow-900">View analytics</a>
                  </div>
                </div>
              </div>

              <!-- Total Categories -->
              <div class="overflow-hidden bg-white rounded-lg shadow">
                <div class="p-5">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 p-3 bg-purple-500 rounded-md">
                      <i class="fas fa-folder text-white"></i>
                    </div>
                    <div class="flex-1 w-0 ml-5">
                      <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Total Categories</dt>
                        <dd class="text-2xl font-semibold text-gray-900"><?php echo $total_categories; ?></dd>
                      </dl>
                    </div>
                  </div>
                </div>
                <div class="px-5 py-3 bg-gray-50">
                  <div class="text-sm">
                    <a href="categories/manage-categories.php" class="font-medium text-purple-700 hover:text-purple-900">View all</a>
                  </div>
                </div>
              </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 gap-5 mt-8 lg:grid-cols-2">
              <!-- Traffic Chart -->
              <div class="p-4 bg-white rounded-lg shadow">
                <h2 class="text-lg font-medium text-gray-900">Traffic Overview</h2>
                <div class="mt-2">
                  <canvas id="trafficChart" height="300"></canvas>
                </div>
              </div>

              <!-- Category Distribution Chart -->
              <div class="p-4 bg-white rounded-lg shadow">
                <h2 class="text-lg font-medium text-gray-900">Content Categories</h2>
                <div class="mt-2">
                  <canvas id="categoryChart" height="300"></canvas>
                </div>
              </div>
            </div>

            <!-- Recent Articles and Activity -->
            <div class="grid grid-cols-1 gap-5 mt-8 lg:grid-cols-2">
              <!-- Recent Articles -->
              <div class="flex flex-col bg-white rounded-lg shadow">
                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                  <h3 class="text-lg font-medium leading-6 text-gray-900">Recent Articles</h3>
                </div>
                <div class="flex-1 min-h-0 overflow-y-auto">
                  <ul class="divide-y divide-gray-200">
                    <?php if (!empty($recent_articles)): ?>
                      <?php foreach ($recent_articles as $article): ?>
                        <li class="px-6 py-4 hover:bg-gray-50">
                          <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                              <?php if (!empty($article['image'])): ?>
                                <img class="w-12 h-12 rounded-md object-cover" src="<?php echo $article['image']; ?>" alt="">
                              <?php else: ?>
                                <div class="w-12 h-12 bg-blue-100 rounded-md flex items-center justify-center">
                                  <i class="fas fa-newspaper text-blue-500"></i>
                                </div>
                              <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                              <p class="text-sm font-medium text-gray-900 truncate"><?php echo htmlspecialchars($article['title']); ?></p>
                              <p class="text-sm text-gray-500 truncate"><?php echo htmlspecialchars($article['category_name']); ?></p>
                            </div>
                            <div>
                              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                <?php echo $article['status'] == 'published' ? 'bg-green-100 text-green-800' : ($article['status'] == 'draft' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                <?php echo ucfirst($article['status']); ?>
                              </span>
                            </div>
                          </div>
                        </li>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <li class="px-6 py-4 text-center text-gray-500">No articles found.</li>
                    <?php endif; ?>
                  </ul>
                </div>
                <div class="px-4 py-4 border-t border-gray-200 sm:px-6">
                  <a href="news/manage-news.php" class="text-sm font-medium text-blue-600 hover:text-blue-500">View all articles</a>
                </div>
              </div>

              <!-- Recent Activity -->
              <div class="flex flex-col bg-white rounded-lg shadow">
                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                  <h3 class="text-lg font-medium leading-6 text-gray-900">Recent Activity</h3>
                </div>
                <div class="flex-1 min-h-0 overflow-y-auto">
                  <ul class="divide-y divide-gray-200">
                    <?php foreach ($recent_activity as $activity): ?>
                      <li class="px-6 py-4">
                        <div class="flex space-x-3">
                          <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                              <i class="fas fa-user text-blue-500"></i>
                            </div>
                          </div>
                          <div class="flex-1 space-y-1">
                            <div class="flex items-center justify-between">
                              <h3 class="text-sm font-medium"><?php echo htmlspecialchars($activity['user']); ?></h3>
                              <p class="text-sm text-gray-500"><?php echo $activity['time']; ?></p>
                            </div>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($activity['action']); ?></p>
                          </div>
                        </div>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </div>
                <div class="px-4 py-4 border-t border-gray-200 sm:px-6">
                  <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-500">View all activity</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script>
    // Traffic Chart Data
    const trafficData = {
      labels: <?php echo json_encode(array_keys($traffic_data)); ?>,
      datasets: [{
        label: 'Page Views',
        backgroundColor: 'rgba(59, 130, 246, 0.2)',
        borderColor: 'rgba(59, 130, 246, 1)',
        data: <?php echo json_encode(array_column($traffic_data, 'views')); ?>,
        tension: 0.4,
        fill: true
      }, {
        label: 'Unique Visitors',
        backgroundColor: 'rgba(16, 185, 129, 0.2)',
        borderColor: 'rgba(16, 185, 129, 1)',
        data: <?php echo json_encode(array_column($traffic_data, 'visitors')); ?>,
        tension: 0.4,
        fill: true
      }]
    };

    // Category Chart Data
    const categoryData = {
      labels: <?php echo json_encode(array_keys($category_data)); ?>,
      datasets: [{
        label: 'Article Count',
        backgroundColor: [
          'rgba(59, 130, 246, 0.7)',
          'rgba(16, 185, 129, 0.7)',
          'rgba(245, 158, 11, 0.7)',
          'rgba(239, 68, 68, 0.7)',
          'rgba(139, 92, 246, 0.7)',
          'rgba(14, 165, 233, 0.7)',
          'rgba(236, 72, 153, 0.7)'
        ],
        borderColor: 'rgba(255, 255, 255, 0.5)',
        data: <?php echo json_encode(array_values($category_data)); ?>
      }]
    };

    // Initialize charts
    window.onload = function() {
      // Traffic Chart
      const trafficCtx = document.getElementById('trafficChart').getContext('2d');
      new Chart(trafficCtx, {
        type: 'line',
        data: trafficData,
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'top',
            }
          }
        }
      });

      // Category Chart
      const categoryCtx = document.getElementById('categoryChart').getContext('2d');
      new Chart(categoryCtx, {
        type: 'pie',
        data: categoryData,
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'right',
            }
          }
        }
      });
    };
  </script>
</body>

</html>