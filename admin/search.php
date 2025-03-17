<?php
// Include configuration files
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

// Check if user is logged in and has admin privileges
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Get search query
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$content_type = isset($_GET['type']) ? $_GET['type'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Initialize results arrays
$articles = [];
$categories = [];
$users = [];
$total_results = 0;

// Perform search if query is not empty
if (!empty($search_query)) {
    // Search articles
    if ($content_type == 'all' || $content_type == 'articles') {
        $search_param = '%' . $search_query . '%';
        
        // Use the limit without parameters to avoid PDO issue
        $sql = "
            SELECT a.*, c.name as category_name, u.name as author_name 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN users u ON a.author_id = u.id 
            WHERE a.title LIKE ? 
            OR a.content LIKE ? 
            ORDER BY a.created_at DESC
            LIMIT " . intval($offset) . ", " . intval($limit);
            
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(1, $search_param, PDO::PARAM_STR);
        $stmt->bindParam(2, $search_param, PDO::PARAM_STR);
        $stmt->execute();
        $articles = $stmt->fetchAll();
        
        // Get total count for pagination
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM articles 
            WHERE title LIKE ? 
            OR content LIKE ?
        ");
        $stmt->bindParam(1, $search_param, PDO::PARAM_STR);
        $stmt->bindParam(2, $search_param, PDO::PARAM_STR);
        $stmt->execute();
        $article_count = $stmt->fetch()['count'];
        $total_results += $article_count;
    }
    
    // Search categories
    if ($content_type == 'all' || $content_type == 'categories') {
        $search_param = '%' . $search_query . '%';
        
        // Use the limit without parameters
        $sql = "
            SELECT * 
            FROM categories 
            WHERE name LIKE ? 
            OR description LIKE ? 
            ORDER BY name ASC
            LIMIT " . intval($offset) . ", " . intval($limit);
            
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(1, $search_param, PDO::PARAM_STR);
        $stmt->bindParam(2, $search_param, PDO::PARAM_STR);
        $stmt->execute();
        $categories = $stmt->fetchAll();
        
        // Get total count for pagination
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM categories 
            WHERE name LIKE ? 
            OR description LIKE ?
        ");
        $stmt->bindParam(1, $search_param, PDO::PARAM_STR);
        $stmt->bindParam(2, $search_param, PDO::PARAM_STR);
        $stmt->execute();
        $category_count = $stmt->fetch()['count'];
        $total_results += $category_count;
    }
    
    // Search users
    if ($content_type == 'all' || $content_type == 'users') {
        $search_param = '%' . $search_query . '%';
        
        // Use the limit without parameters
        $sql = "
            SELECT * 
            FROM users 
            WHERE name LIKE ? 
            OR email LIKE ? 
            ORDER BY name ASC
            LIMIT " . intval($offset) . ", " . intval($limit);
            
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(1, $search_param, PDO::PARAM_STR);
        $stmt->bindParam(2, $search_param, PDO::PARAM_STR);
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        // Get total count for pagination
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM users 
            WHERE name LIKE ? 
            OR email LIKE ?
        ");
        $stmt->bindParam(1, $search_param, PDO::PARAM_STR);
        $stmt->bindParam(2, $search_param, PDO::PARAM_STR);
        $stmt->execute();
        $user_count = $stmt->fetch()['count'];
        $total_results += $user_count;
    }
}

// Calculate total pages for pagination
$total_pages = ceil($total_results / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - NewsHub Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .sidebar-active {
            border-left: 4px solid #3B82F6;
            background-color: rgba(59, 130, 246, 0.1);
        }
        .highlight {
            background-color: rgba(255, 255, 0, 0.3);
            padding: 0 2px;
            border-radius: 2px;
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
                        <div class="md:flex md:items-center md:justify-between">
                            <div class="flex-1 min-w-0">
                                <h1 class="text-2xl font-semibold text-gray-900">Search Results</h1>
                            </div>
                        </div>
                    </div>

                    <div class="px-4 mx-auto max-w-7xl sm:px-6 md:px-8 mt-5">
                        <!-- Search Form -->
                        <div class="bg-white shadow rounded-lg p-4 mb-6">
                            <form action="search.php" method="GET" class="md:flex md:items-center">
                                <div class="md:w-1/2 mb-4 md:mb-0 md:mr-4">
                                    <label for="q" class="block text-sm font-medium text-gray-700 mb-1">Search Query</label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-search text-gray-400"></i>
                                        </div>
                                        <input type="text" name="q" id="q" value="<?php echo htmlspecialchars($search_query); ?>" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md" placeholder="Search articles, categories, users...">
                                    </div>
                                </div>
                                <div class="md:w-1/4 mb-4 md:mb-0 md:mr-4">
                                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Content Type</label>
                                    <select id="type" name="type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                        <option value="all" <?php echo $content_type == 'all' ? 'selected' : ''; ?>>All Content</option>
                                        <option value="articles" <?php echo $content_type == 'articles' ? 'selected' : ''; ?>>Articles</option>
                                        <option value="categories" <?php echo $content_type == 'categories' ? 'selected' : ''; ?>>Categories</option>
                                        <option value="users" <?php echo $content_type == 'users' ? 'selected' : ''; ?>>Users</option>
                                    </select>
                                </div>
                                <div class="md:w-1/4 md:flex md:items-end">
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <i class="fas fa-search mr-2"></i> Search
                                    </button>
                                </div>
                            </form>
                        </div>

                        <?php if (!empty($search_query)): ?>
                            <!-- Search Results Summary -->
                            <div class="bg-white shadow rounded-lg p-4 mb-6">
                                <h2 class="text-lg font-medium text-gray-900 mb-2">
                                    Search Results for "<?php echo htmlspecialchars($search_query); ?>"
                                </h2>
                                <p class="text-gray-600">
                                    Found <?php echo $total_results; ?> result<?php echo $total_results != 1 ? 's' : ''; ?> 
                                    <?php if ($content_type != 'all'): ?>
                                        in <?php echo $content_type; ?>
                                    <?php endif; ?>
                                </p>
                            </div>

                            <!-- Results Section -->
                            <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
                                <?php if (($content_type == 'all' || $content_type == 'articles') && !empty($articles)): ?>
                                    <div class="border-b border-gray-200">
                                        <div class="px-6 py-4 bg-gray-50">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                <i class="fas fa-newspaper text-blue-500 mr-2"></i> Articles
                                            </h3>
                                        </div>
                                        <ul class="divide-y divide-gray-200">
                                            <?php foreach ($articles as $article): ?>
                                                <li class="p-4 hover:bg-gray-50">
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <a href="../news/edit-news.php?id=<?php echo $article['id']; ?>" class="text-lg font-medium text-blue-600 hover:text-blue-800">
                                                                <?php 
                                                                // Highlight search terms in title
                                                                $highlighted_title = preg_replace('/(' . preg_quote($search_query, '/') . ')/i', '<span class="highlight">$1</span>', htmlspecialchars($article['title']));
                                                                echo $highlighted_title;
                                                                ?>
                                                            </a>
                                                            <div class="mt-1 flex items-center text-sm text-gray-500">
                                                                <span class="mr-2">
                                                                    <i class="fas fa-folder text-gray-400 mr-1"></i> 
                                                                    <?php echo htmlspecialchars($article['category_name']); ?>
                                                                </span>
                                                                <span class="mr-2">
                                                                    <i class="fas fa-user text-gray-400 mr-1"></i> 
                                                                    <?php echo htmlspecialchars($article['author_name']); ?>
                                                                </span>
                                                                <span>
                                                                    <i class="fas fa-calendar text-gray-400 mr-1"></i> 
                                                                    <?php echo date('M d, Y', strtotime($article['created_at'])); ?>
                                                                </span>
                                                            </div>
                                                            <div class="mt-2 text-sm text-gray-600">
                                                                <?php 
                                                                // Get excerpt from content and highlight search terms
                                                                $content_excerpt = strip_tags($article['content']);
                                                                $content_excerpt = substr($content_excerpt, 0, 200) . '...';
                                                                $highlighted_excerpt = preg_replace('/(' . preg_quote($search_query, '/') . ')/i', '<span class="highlight">$1</span>', htmlspecialchars($content_excerpt));
                                                                echo $highlighted_excerpt;
                                                                ?>
                                                            </div>
                                                        </div>
                                                        <div class="ml-4 flex-shrink-0 flex">
                                                            <a href="../news/edit-news.php?id=<?php echo $article['id']; ?>" class="mr-2 inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                                <i class="fas fa-edit mr-1"></i> Edit
                                                            </a>
                                                            <a href="../../article.php?id=<?php echo $article['id']; ?>" target="_blank" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                                <i class="fas fa-eye mr-1"></i> View
                                                            </a>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <?php if (($content_type == 'all' || $content_type == 'categories') && !empty($categories)): ?>
                                    <div class="border-b border-gray-200">
                                        <div class="px-6 py-4 bg-gray-50">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                <i class="fas fa-folder text-yellow-500 mr-2"></i> Categories
                                            </h3>
                                        </div>
                                        <ul class="divide-y divide-gray-200">
                                            <?php foreach ($categories as $category): ?>
                                                <li class="p-4 hover:bg-gray-50">
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <a href="../admin/categories/edit-category.php?id=<?php echo $category['id']; ?>" class="text-lg font-medium text-blue-600 hover:text-blue-800">
                                                                <?php 
                                                                // Highlight search terms in name
                                                                $highlighted_name = preg_replace('/(' . preg_quote($search_query, '/') . ')/i', '<span class="highlight">$1</span>', htmlspecialchars($category['name']));
                                                                echo $highlighted_name;
                                                                ?>
                                                            </a>
                                                            <div class="mt-1 flex items-center text-sm text-gray-500">
                                                                <span class="mr-2">
                                                                    <i class="fas fa-link text-gray-400 mr-1"></i> 
                                                                    <?php echo htmlspecialchars($category['slug']); ?>
                                                                </span>
                                                                <span>
                                                                    <i class="fas fa-calendar text-gray-400 mr-1"></i> 
                                                                    <?php echo date('M d, Y', strtotime($category['created_at'])); ?>
                                                                </span>
                                                            </div>
                                                            <?php if (!empty($category['description'])): ?>
                                                                <div class="mt-2 text-sm text-gray-600">
                                                                    <?php 
                                                                    // Highlight search terms in description
                                                                    $highlighted_description = preg_replace('/(' . preg_quote($search_query, '/') . ')/i', '<span class="highlight">$1</span>', htmlspecialchars($category['description']));
                                                                    echo $highlighted_description;
                                                                    ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="ml-4 flex-shrink-0">
                                                            <a href="../categories/edit-category.php?id=<?php echo $category['id']; ?>" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                                <i class="fas fa-edit mr-1"></i> Edit
                                                            </a>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <?php if (($content_type == 'all' || $content_type == 'users') && !empty($users)): ?>
                                    <div class="border-b border-gray-200">
                                        <div class="px-6 py-4 bg-gray-50">
                                            <h3 class="text-lg font-medium text-gray-900">
                                                <i class="fas fa-users text-green-500 mr-2"></i> Users
                                            </h3>
                                        </div>
                                        <ul class="divide-y divide-gray-200">
                                            <?php foreach ($users as $user): ?>
                                                <li class="p-4 hover:bg-gray-50">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center">
                                                            <div class="flex-shrink-0 h-10 w-10">
                                                                <?php if (!empty($user['avatar']) && file_exists('../../' . $user['avatar'])): ?>
                                                                    <img class="h-10 w-10 rounded-full" src="<?php echo SITE_URL . '/' . $user['avatar']; ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
                                                                <?php else: ?>
                                                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                                        <i class="fas fa-user text-blue-500"></i>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="ml-4">
                                                                <a href="../users/edit-user.php?id=<?php echo $user['id']; ?>" class="text-lg font-medium text-blue-600 hover:text-blue-800">
                                                                    <?php 
                                                                    // Highlight search terms in name
                                                                    $highlighted_name = preg_replace('/(' . preg_quote($search_query, '/') . ')/i', '<span class="highlight">$1</span>', htmlspecialchars($user['name']));
                                                                    echo $highlighted_name;
                                                                    ?>
                                                                </a>
                                                                <div class="text-sm text-gray-500">
                                                                    <?php 
                                                                    // Highlight search terms in email
                                                                    $highlighted_email = preg_replace('/(' . preg_quote($search_query, '/') . ')/i', '<span class="highlight">$1</span>', htmlspecialchars($user['email']));
                                                                    echo $highlighted_email;
                                                                    ?>
                                                                </div>
                                                                <div class="mt-1 flex items-center text-xs text-gray-500">
                                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium 
                                                                        <?php 
                                                                        switch ($user['role']) {
                                                                            case 'admin':
                                                                                echo 'bg-red-100 text-red-800';
                                                                                break;
                                                                            case 'editor':
                                                                                echo 'bg-blue-100 text-blue-800';
                                                                                break;
                                                                            case 'author':
                                                                                echo 'bg-green-100 text-green-800';
                                                                                break;
                                                                            default:
                                                                                echo 'bg-gray-100 text-gray-800';
                                                                        }
                                                                        ?>">
                                                                        <?php echo ucfirst($user['role']); ?>
                                                                    </span>
                                                                    <span class="ml-2">
                                                                        <i class="fas fa-calendar text-gray-400 mr-1"></i> 
                                                                        <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="ml-4 flex-shrink-0">
                                                            <a href="../users/edit-user.php?id=<?php echo $user['id']; ?>" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                                <i class="fas fa-edit mr-1"></i> Edit
                                                            </a>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <?php if (empty($articles) && empty($categories) && empty($users)): ?>
                                    <div class="px-6 py-12 text-center">
                                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 text-gray-500 mb-4">
                                            <i class="fas fa-search text-3xl"></i>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">No results found</h3>
                                        <p class="text-gray-500">We couldn't find any results for "<?php echo htmlspecialchars($search_query); ?>"</p>
                                        <p class="text-gray-500 mt-1">Try adjusting your search term or content type.</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-lg shadow">
                                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm text-gray-700">
                                                Showing page <span class="font-medium"><?php echo $page; ?></span> of <span class="font-medium"><?php echo $total_pages; ?></span>
                                            </p>
                                        </div>
                                        <div>
                                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                                <?php if ($page > 1): ?>
                                                    <a href="?q=<?php echo urlencode($search_query); ?>&type=<?php echo $content_type; ?>&page=<?php echo $page - 1; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                        <span class="sr-only">Previous</span>
                                                        <i class="fas fa-chevron-left"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                                        <span class="sr-only">Previous</span>
                                                        <i class="fas fa-chevron-left"></i>
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <?php
                                                // Display at most 5 page links
                                                $start_page = max(1, $page - 2);
                                                $end_page = min($total_pages, $start_page + 4);
                                                if ($end_page - $start_page < 4 && $start_page > 1) {
                                                    $start_page = max(1, $end_page - 4);
                                                }
                                                
                                                for ($i = $start_page; $i <= $end_page; $i++):
                                                ?>
                                                    <?php if ($i == $page): ?>
                                                        <span class="relative inline-flex items-center px-4 py-2 border border-blue-500 bg-blue-50 text-sm font-medium text-blue-600">
                                                            <?php echo $i; ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <a href="?q=<?php echo urlencode($search_query); ?>&type=<?php echo $content_type; ?>&page=<?php echo $i; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                                                            <?php echo $i; ?>
                                                        </a>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                                
                                                <?php if ($page < $total_pages): ?>
                                                    <a href="?q=<?php echo urlencode($search_query); ?>&type=<?php echo $content_type; ?>&page=<?php echo $page + 1; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                        <span class="sr-only">Next</span>
                                                        <i class="fas fa-chevron-right"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-100 text-sm font-medium text-gray-400 cursor-not-allowed">
                                                        <span class="sr-only">Next</span>
                                                        <i class="fas fa-chevron-right"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- No search yet message -->
                            <div class="bg-white shadow rounded-lg p-12 text-center">
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-blue-100 text-blue-500 mb-6">
                                    <i class="fas fa-search text-4xl"></i>
                                </div>
                                <h3 class="text-xl font-medium text-gray-900 mb-3">Search Across All Content</h3>
                                <p class="text-gray-500 max-w-md mx-auto mb-6">
                                    Quickly find articles, categories, users, and more using the search bar above.
                                </p>
                                <div class="max-w-lg mx-auto grid grid-cols-1 gap-4 sm:grid-cols-3">
                                    <div class="bg-gray-50 p-4 rounded-lg text-center">
                                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 text-blue-500 mb-2">
                                            <i class="fas fa-newspaper"></i>
                                        </div>
                                        <h4 class="text-sm font-medium text-gray-900 mb-1">Articles</h4>
                                        <p class="text-xs text-gray-500">Search by title or content</p>
                                    </div>
                                    <div class="bg-gray-50 p-4 rounded-lg text-center">
                                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 text-yellow-500 mb-2">
                                            <i class="fas fa-folder"></i>
                                        </div>
                                        <h4 class="text-sm font-medium text-gray-900 mb-1">Categories</h4>
                                        <p class="text-xs text-gray-500">Search by name or description</p>
                                    </div>
                                    <div class="bg-gray-50 p-4 rounded-lg text-center">
                                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 text-green-500 mb-2">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <h4 class="text-sm font-medium text-gray-900 mb-1">Users</h4>
                                        <p class="text-xs text-gray-500">Search by name or email</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Close alert messages when close button is clicked
        document.addEventListener('DOMContentLoaded', function() {
            const closeButtons = document.querySelectorAll('[role="alert"] svg');
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('[role="alert"]').remove();
                });
            });
        });
    </script>
</body>

</html>