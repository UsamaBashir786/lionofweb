<?php

/**
 * Helper Functions
 */

/**
 * Generate a random string
 * 
 * @param int $length Length of the string
 * @return string Random string
 */
function generateRandomString($length = 10)
{
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[random_int(0, $charactersLength - 1)];
  }
  return $randomString;
}





// =========================================================================




/**
 * Page checker function for Lion Of Web
 * Checks if the requested page exists and redirects to under-development.php if not
 */
function checkPageExists()
{
  // Skip for under-development.php page itself to avoid redirect loops
  if (strpos($_SERVER['REQUEST_URI'], 'under-development.php') !== false) {
    return true;
  }

  // Get the current request URI
  $requestUri = $_SERVER['REQUEST_URI'];

  // Remove query parameters if any
  $requestPath = parse_url($requestUri, PHP_URL_PATH);

  // Get the document root
  $documentRoot = $_SERVER['DOCUMENT_ROOT'];

  // Normalize the path
  $requestPath = rtrim($requestPath, '/');
  if (empty($requestPath)) {
    $requestPath = '/';
  }

  // Define possible file extensions to check
  $possibleExtensions = ['', '.php', '.html', '.htm'];

  // Base physical path
  $basePath = $documentRoot . $requestPath;

  // Check if it's the homepage
  if ($requestPath == '/') {
    return true; // Assume homepage exists
  }

  // Check if the requested path exists with any of the possible extensions
  foreach ($possibleExtensions as $ext) {
    if (file_exists($basePath . $ext)) {
      return true; // Page exists
    }
  }

  // Check if it's a directory with an index file
  if (is_dir($basePath)) {
    foreach (['index.php', 'index.html', 'index.htm'] as $indexFile) {
      if (file_exists($basePath . '/' . $indexFile)) {
        return true; // Directory with index file exists
      }
    }
  }

  // If we've reached here, the page doesn't exist
  // Redirect to under-development.php
  header("Location: under-development.php");
  exit;
}









// =========================================================================








/**
 * Generate a slug from a string
 * 
 * @param string $string String to convert
 * @return string Slug
 */
function generateSlug($string)
{
  // Convert to lowercase and remove whitespace from beginning and end
  $string = strtolower(trim($string));

  // Replace non letter or digits with -
  $string = preg_replace('/[^a-z0-9\-]/', '-', $string);

  // Replace multiple - with single -
  $string = preg_replace('/-+/', '-', $string);

  // Remove - from beginning and end
  $string = trim($string, '-');

  return $string;
}

/**
 * Sanitize user input
 * 
 * @param string $data Data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data)
{
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

/**
 * Limit text to a specific number of words
 * 
 * @param string $text Text to limit
 * @param int $limit Word limit
 * @param string $trail String to append
 * @return string Limited text
 */
function limitWords($text, $limit = 20, $trail = '...')
{
  $text = strip_tags($text);
  $words = explode(' ', $text);

  if (count($words) <= $limit) {
    return $text;
  }

  return implode(' ', array_slice($words, 0, $limit)) . $trail;
}

/**
 * Format date
 * 
 * @param string $date Date to format
 * @param string $format Format string
 * @return string Formatted date
 */
function formatDate($date, $format = 'F j, Y')
{
  return date($format, strtotime($date));
}

/**
 * Get time ago
 * 
 * @param string $date Date to convert
 * @return string Time ago string
 */
function timeAgo($date)
{
  $timestamp = strtotime($date);
  $difference = time() - $timestamp;

  if ($difference < 60) {
    return $difference . ' seconds ago';
  } elseif ($difference < 3600) {
    return round($difference / 60) . ' minutes ago';
  } elseif ($difference < 86400) {
    return round($difference / 3600) . ' hours ago';
  } elseif ($difference < 604800) {
    return round($difference / 86400) . ' days ago';
  } elseif ($difference < 2592000) {
    return round($difference / 604800) . ' weeks ago';
  } elseif ($difference < 31536000) {
    return round($difference / 2592000) . ' months ago';
  } else {
    return round($difference / 31536000) . ' years ago';
  }
}

/**
 * Check if email exists
 * 
 * @param PDO $conn Database connection
 * @param string $email Email to check
 * @return bool True if email exists
 */
function checkEmailExists($conn, $email)
{
  $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
  $stmt->bindParam(':email', $email);
  $stmt->execute();

  return $stmt->rowCount() > 0;
}

/**
 * ARTICLE FUNCTIONS
 */

/**
 * Get all articles with pagination and filters
 * 
 * @param PDO $conn Database connection
 * @param int $offset Pagination offset
 * @param int $limit Number of items per page
 * @param string $status Status filter
 * @param int $category_id Category ID filter
 * @param string $search Search query
 * @return array Articles
 */
function getArticles($conn, $offset = 0, $limit = 10, $status = '', $category_id = 0, $search = '')
{
  $params = [];
  $sql = "SELECT a.*, c.name as category_name, u.name as author_name 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN users u ON a.author_id = u.id 
            WHERE 1=1";

  // Add status filter
  if (!empty($status)) {
    $sql .= " AND a.status = :status";
    $params[':status'] = $status;
  }

  // Add category filter
  if ($category_id > 0) {
    $sql .= " AND a.category_id = :category_id";
    $params[':category_id'] = $category_id;
  }

  // Add search filter
  if (!empty($search)) {
    $sql .= " AND (a.title LIKE :search OR a.content LIKE :search)";
    $params[':search'] = "%{$search}%";
  }

  // Add ordering and limit
  $sql .= " ORDER BY a.created_at DESC LIMIT :offset, :limit";
  $params[':offset'] = (int)$offset;
  $params[':limit'] = (int)$limit;

  $stmt = $conn->prepare($sql);

  // Bind parameters
  foreach ($params as $key => $value) {
    if (in_array($key, [':offset', ':limit', ':category_id'])) {
      $stmt->bindValue($key, $value, PDO::PARAM_INT);
    } else {
      $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
  }

  $stmt->execute();
  return $stmt->fetchAll();
}

/**
 * Get total articles count
 * 
 * @param PDO $conn Database connection
 * @param string $status Status filter
 * @param int $category_id Category ID filter
 * @param string $search Search query
 * @return int Total articles
 */
function getTotalArticles($conn, $status = '', $category_id = 0, $search = '')
{
  $params = [];
  $sql = "SELECT COUNT(*) FROM articles a WHERE 1=1";

  // Add status filter
  if (!empty($status)) {
    $sql .= " AND a.status = :status";
    $params[':status'] = $status;
  }

  // Add category filter
  if ($category_id > 0) {
    $sql .= " AND a.category_id = :category_id";
    $params[':category_id'] = $category_id;
  }

  // Add search filter
  if (!empty($search)) {
    $sql .= " AND (a.title LIKE :search OR a.content LIKE :search)";
    $params[':search'] = "%{$search}%";
  }

  $stmt = $conn->prepare($sql);

  // Bind parameters
  foreach ($params as $key => $value) {
    if ($key === ':category_id') {
      $stmt->bindValue($key, $value, PDO::PARAM_INT);
    } else {
      $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
  }

  $stmt->execute();
  return $stmt->fetchColumn();
}

/**
 * Get article by ID
 * 
 * @param PDO $conn Database connection
 * @param int $id Article ID
 * @return array|bool Article data or false if not found
 */
function getArticleById($conn, $id)
{
  $stmt = $conn->prepare("SELECT * FROM articles WHERE id = :id");
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  $stmt->execute();

  return $stmt->fetch();
}

/**
 * Add new article
 * 
 * @param PDO $conn Database connection
 * @param string $title Article title
 * @param string $content Article content
 * @param int $category_id Category ID
 * @param string $image Image path
 * @param string $status Article status
 * @param bool $featured Featured article
 * @param bool $breaking Breaking news
 * @param int $author_id Author ID
 * @return int|bool New article ID or false on failure
 */
function addArticle($conn, $title, $content, $category_id, $image = '', $status = 'published', $featured = 0, $breaking = 0, $author_id = 0)
{
  // Generate slug from title
  $slug = generateSlug($title);

  // Check if slug exists, add random string if needed
  $stmt = $conn->prepare("SELECT id FROM articles WHERE slug = :slug");
  $stmt->bindParam(':slug', $slug);
  $stmt->execute();

  if ($stmt->rowCount() > 0) {
    $slug = $slug . '-' . generateRandomString(5);
  }

  $sql = "INSERT INTO articles (title, slug, content, category_id, image, status, featured, breaking, author_id, created_at, updated_at) 
            VALUES (:title, :slug, :content, :category_id, :image, :status, :featured, :breaking, :author_id, NOW(), NOW())";

  $stmt = $conn->prepare($sql);
  $stmt->bindParam(':title', $title);
  $stmt->bindParam(':slug', $slug);
  $stmt->bindParam(':content', $content);
  $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
  $stmt->bindParam(':image', $image);
  $stmt->bindParam(':status', $status);
  $stmt->bindParam(':featured', $featured, PDO::PARAM_INT);
  $stmt->bindParam(':breaking', $breaking, PDO::PARAM_INT);
  $stmt->bindParam(':author_id', $author_id, PDO::PARAM_INT);

  if ($stmt->execute()) {
    return $conn->lastInsertId();
  }

  return false;
}

/**
 * Update article
 * 
 * @param PDO $conn Database connection
 * @param int $id Article ID
 * @param string $title Article title
 * @param string $content Article content
 * @param int $category_id Category ID
 * @param string $image Image path
 * @param string $status Article status
 * @param bool $featured Featured article
 * @param bool $breaking Breaking news
 * @return bool Success or failure
 */
function updateArticle($conn, $id, $title, $content, $category_id, $image = '', $status = 'published', $featured = 0, $breaking = 0)
{
  $sql = "UPDATE articles SET 
            title = :title, 
            content = :content, 
            category_id = :category_id, 
            status = :status, 
            featured = :featured, 
            breaking = :breaking, 
            updated_at = NOW()";

  // Only update image if provided
  if (!empty($image)) {
    $sql .= ", image = :image";
  }

  $sql .= " WHERE id = :id";

  $stmt = $conn->prepare($sql);
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  $stmt->bindParam(':title', $title);
  $stmt->bindParam(':content', $content);
  $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
  $stmt->bindParam(':status', $status);
  $stmt->bindParam(':featured', $featured, PDO::PARAM_INT);
  $stmt->bindParam(':breaking', $breaking, PDO::PARAM_INT);

  if (!empty($image)) {
    $stmt->bindParam(':image', $image);
  }

  return $stmt->execute();
}

/**
 * Delete article
 * 
 * @param PDO $conn Database connection
 * @param int $id Article ID
 * @return bool Success or failure
 */
function deleteArticle($conn, $id)
{
  // Get the article image path before deleting
  $stmt = $conn->prepare("SELECT image FROM articles WHERE id = :id");
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  $stmt->execute();
  $article = $stmt->fetch();

  // Delete the article
  $stmt = $conn->prepare("DELETE FROM articles WHERE id = :id");
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  $result = $stmt->execute();

  // Delete the image file if exists
  if ($result && !empty($article['image']) && file_exists('../../' . $article['image'])) {
    unlink('../../' . $article['image']);
  }

  return $result;
}

/**
 * CATEGORY FUNCTIONS
 */

/**
 * Get all categories
 * 
 * @param PDO $conn Database connection
 * @param bool $include_inactive Include inactive categories
 * @return array Categories
 */
function getAllCategories($conn, $include_inactive = false)
{
  $sql = "SELECT c.*, (SELECT COUNT(*) FROM articles WHERE category_id = c.id) AS article_count 
            FROM categories c";

  if (!$include_inactive) {
    $sql .= " WHERE c.is_active = 1";
  }

  $sql .= " ORDER BY c.name ASC";

  $stmt = $conn->prepare($sql);
  $stmt->execute();

  return $stmt->fetchAll();
}

/**
 * Get category by ID
 * 
 * @param PDO $conn Database connection
 * @param int $id Category ID
 * @return array|bool Category data or false if not found
 */
function getCategoryById($conn, $id)
{
  $stmt = $conn->prepare("SELECT * FROM categories WHERE id = :id");
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  $stmt->execute();

  return $stmt->fetch();
}

/**
 * Add new category
 * 
 * @param PDO $conn Database connection
 * @param string $name Category name
 * @param string $slug Category slug
 * @param string $description Category description
 * @param string $icon Icon path
 * @param bool $is_active Category status
 * @return int|bool New category ID or false on failure
 */
function addCategory($conn, $name, $slug, $description = '', $icon = '', $is_active = 1)
{
  // Check if slug exists
  $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = :slug");
  $stmt->bindParam(':slug', $slug);
  $stmt->execute();

  if ($stmt->rowCount() > 0) {
    return false;
  }

  $sql = "INSERT INTO categories (name, slug, description, icon, is_active, created_at, updated_at) 
            VALUES (:name, :slug, :description, :icon, :is_active, NOW(), NOW())";

  $stmt = $conn->prepare($sql);
  $stmt->bindParam(':name', $name);
  $stmt->bindParam(':slug', $slug);
  $stmt->bindParam(':description', $description);
  $stmt->bindParam(':icon', $icon);
  $stmt->bindParam(':is_active', $is_active, PDO::PARAM_INT);

  if ($stmt->execute()) {
    return $conn->lastInsertId();
  }

  return false;
}

/**
 * Update category
 * 
 * @param PDO $conn Database connection
 * @param int $id Category ID
 * @param string $name Category name
 * @param string $slug Category slug
 * @param string $description Category description
 * @param string $icon Icon path
 * @param bool $is_active Category status
 * @return bool Success or failure
 */
function updateCategory($conn, $id, $name, $slug, $description = '', $icon = '', $is_active = 1)
{
  // Check if slug exists and is not current category
  $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = :slug AND id != :id");
  $stmt->bindParam(':slug', $slug);
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  $stmt->execute();

  if ($stmt->rowCount() > 0) {
    return false;
  }

  $sql = "UPDATE categories SET 
            name = :name, 
            slug = :slug, 
            description = :description, 
            is_active = :is_active, 
            updated_at = NOW()";

  // Only update icon if provided
  if (!empty($icon)) {
    $sql .= ", icon = :icon";
  }

  $sql .= " WHERE id = :id";

  $stmt = $conn->prepare($sql);
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  $stmt->bindParam(':name', $name);
  $stmt->bindParam(':slug', $slug);
  $stmt->bindParam(':description', $description);
  $stmt->bindParam(':is_active', $is_active, PDO::PARAM_INT);

  if (!empty($icon)) {
    $stmt->bindParam(':icon', $icon);
  }

  return $stmt->execute();
}

/**
 * Delete category
 * 
 * @param PDO $conn Database connection
 * @param int $id Category ID
 * @return bool Success or failure
 */
function deleteCategory($conn, $id)
{
  // Get the default category ID
  $stmt = $conn->prepare("SELECT id FROM categories WHERE slug = 'uncategorized' LIMIT 1");
  $stmt->execute();
  $default_category = $stmt->fetch();

  // If default category doesn't exist, create it
  if (!$default_category) {
    $stmt = $conn->prepare("INSERT INTO categories (name, slug, description, is_active, created_at, updated_at) 
                                VALUES ('Uncategorized', 'uncategorized', 'Default category', 1, NOW(), NOW())");
    $stmt->execute();
    $default_category_id = $conn->lastInsertId();
  } else {
    $default_category_id = $default_category['id'];
  }

  // Don't delete the default category
  if ($id == $default_category_id) {
    return false;
  }

  // Get the category icon path before deleting
  $stmt = $conn->prepare("SELECT icon FROM categories WHERE id = :id");
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  $stmt->execute();
  $category = $stmt->fetch();

  // Move articles to default category
  $stmt = $conn->prepare("UPDATE articles SET category_id = :default_id WHERE category_id = :id");
  $stmt->bindParam(':default_id', $default_category_id, PDO::PARAM_INT);
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  $stmt->execute();

  // Delete the category
  $stmt = $conn->prepare("DELETE FROM categories WHERE id = :id");
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  $result = $stmt->execute();

  // Delete the icon file if exists
  if ($result && !empty($category['icon']) && file_exists('../../' . $category['icon'])) {
    unlink('../../' . $category['icon']);
  }

  return $result;
}

/**
 * Toggle category status
 * 
 * @param PDO $conn Database connection
 * @param int $id Category ID
 * @return bool Success or failure
 */
function toggleCategoryStatus($conn, $id)
{
  $stmt = $conn->prepare("UPDATE categories SET is_active = NOT is_active, updated_at = NOW() WHERE id = :id");
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);

  return $stmt->execute();
}

/**
 * USER FUNCTIONS
 */

/**
 * Get all users with pagination and filters
 * 
 * @param PDO $conn Database connection
 * @param int $offset Pagination offset
 * @param int $limit Number of items per page
 * @param string $role Role filter
 * @param int $status Status filter (-1 for all)
 * @param string $search Search query
 * @return array Users
 */
function getUsers($conn, $offset = 0, $limit = 10, $role = '', $status = -1, $search = '')
{
  $params = [];
  $sql = "SELECT u.*, (SELECT COUNT(*) FROM articles WHERE author_id = u.id) AS article_count 
            FROM users u 
            WHERE 1=1";

  // Add role filter
  if (!empty($role)) {
    $sql .= " AND u.role = :role";
    $params[':role'] = $role;
  }

  // Add status filter
  if ($status !== -1) {
    $sql .= " AND u.status = :status";
    $params[':status'] = $status;
  }

  // Add search filter
  if (!empty($search)) {
    $sql .= " AND (u.name LIKE :search OR u.email LIKE :search)";
    $params[':search'] = "%{$search}%";
  }

  // Add ordering and limit
  $sql .= " ORDER BY u.created_at DESC LIMIT :offset, :limit";
  $params[':offset'] = (int)$offset;
  $params[':limit'] = (int)$limit;

  $stmt = $conn->prepare($sql);

  // Bind parameters
  foreach ($params as $key => $value) {
    if (in_array($key, [':offset', ':limit', ':status']) && $key !== ':search') {
      $stmt->bindValue($key, $value, PDO::PARAM_INT);
    } else {
      $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
  }

  $stmt->execute();
  return $stmt->fetchAll();
}

/**
 * Get total users count
 * 
 * @param PDO $conn Database connection
 * @param string $role Role filter
 * @param int $status Status filter (-1 for all)
 * @param string $search Search query
 * @return int Total users
 */
function getTotalUsers($conn, $role = '', $status = -1, $search = '')
{
  $params = [];
  $sql = "SELECT COUNT(*) FROM users u WHERE 1=1";

  // Add role filter
  if (!empty($role)) {
    $sql .= " AND u.role = :role";
    $params[':role'] = $role;
  }

  // Add status filter
  if ($status !== -1) {
    $sql .= " AND u.status = :status";
    $params[':status'] = $status;
  }

  // Add search filter
  if (!empty($search)) {
    $sql .= " AND (u.name LIKE :search OR u.email LIKE :search)";
    $params[':search'] = "%{$search}%";
  }

  $stmt = $conn->prepare($sql);

  // Bind parameters
  foreach ($params as $key => $value) {
    if ($key === ':status') {
      $stmt->bindValue($key, $value, PDO::PARAM_INT);
    } else {
      $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
  }

  $stmt->execute();
  return $stmt->fetchColumn();
}

/**
 * Get user by ID
 * 
 * @param PDO $conn Database connection
 * @param int $id User ID
 * @return array|bool User data or false if not found
 */
function getUserById($conn, $id)
{
  $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  $stmt->execute();

  return $stmt->fetch();
}

/**
 * Get user by email
 * 
 * @param PDO $conn Database connection
 * @param string $email User email
 * @return array|bool User data or false if not found
 */
function getUserByEmail($conn, $email)
{
  $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
  $stmt->bindParam(':email', $email);
  $stmt->execute();

  return $stmt->fetch();
}

/**
 * Add new user
 * 
 * @param PDO $conn Database connection
 * @param string $name User name
 * @param string $email User email
 * @param string $password User password (hashed)
 * @param string $role User role
 * @param string $avatar Avatar path
 * @param bool $status User status
 * @return int|bool New user ID or false on failure
 */
function addUser($conn, $name, $email, $password, $role = 'subscriber', $avatar = '', $status = 1)
{
  $sql = "INSERT INTO users (name, email, password, role, avatar, status, created_at, updated_at) 
            VALUES (:name, :email, :password, :role, :avatar, :status, NOW(), NOW())";

  $stmt = $conn->prepare($sql);
  $stmt->bindParam(':name', $name);
  $stmt->bindParam(':email', $email);
  $stmt->bindParam(':password', $password);
  $stmt->bindParam(':role', $role);
  $stmt->bindParam(':avatar', $avatar);
  $stmt->bindParam(':status', $status, PDO::PARAM_INT);

  if ($stmt->execute()) {
    return $conn->lastInsertId();
  }

  return false;
}

/**
 * Update user
 * 
 * @param PDO $conn Database connection
 * @param int $id User ID
 * @param string $name User name
 * @param string $email User email
 * @param string $password User password (hashed)
 * @param string $role User role
 * @param string $avatar Avatar path
 * @param bool $status User status
 * @return bool Success or failure
 */
function updateUser($conn, $id, $name, $email, $password, $role = 'subscriber', $avatar = '', $status = 1)
{
  $sql = "UPDATE users SET 
            name = :name, 
            email = :email, 
            password = :password, 
            role = :role, 
            status = :status, 
            updated_at = NOW()";

  // Only update avatar if provided
  if (!empty($avatar)) {
    $sql .= ", avatar = :avatar";
  }

  $sql .= " WHERE id = :id";

  $stmt = $conn->prepare($sql);
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  $stmt->bindParam(':name', $name);
  $stmt->bindParam(':email', $email);
  $stmt->bindParam(':password', $password);
  $stmt->bindParam(':role', $role);
  $stmt->bindParam(':status', $status, PDO::PARAM_INT);

  if (!empty($avatar)) {
    $stmt->bindParam(':avatar', $avatar);
  }

  return $stmt->execute();
}

/**
 * Delete user
 * 
 * @param PDO $conn Database connection
 * @param int $id User ID
 * @return bool Success or failure
 */
function deleteUser($conn, $id)
{
  // Get the user avatar path before deleting
  $stmt = $conn->prepare("SELECT avatar FROM users WHERE id = :id");
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  $stmt->execute();
  $user = $stmt->fetch();

  // Delete the user
  $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);
  $result = $stmt->execute();

  // Delete the avatar file if exists
  if ($result && !empty($user['avatar']) && file_exists('../../' . $user['avatar'])) {
    unlink('../../' . $user['avatar']);
  }

  return $result;
}

/**
 * Toggle user status
 * 
 * @param PDO $conn Database connection
 * @param int $id User ID
 * @return bool Success or failure
 */
function toggleUserStatus($conn, $id)
{
  $stmt = $conn->prepare("UPDATE users SET status = NOT status, updated_at = NOW() WHERE id = :id");
  $stmt->bindParam(':id', $id, PDO::PARAM_INT);

  return $stmt->execute();
}

/**
 * Authentication function
 * 
 * @param PDO $conn Database connection
 * @param string $email User email
 * @param string $password User password (plain text)
 * @return array|bool User data or false if authentication fails
 */
/**
 * Authenticates a user by email and password
 * 
 * @param PDO $conn Database connection
 * @param string $email User email
 * @param string $password User password (plain text)
 * @return array|bool User data array if authenticated, false otherwise
 */
function authenticateUser($conn, $email, $password)
{
  try {
    error_log("Attempting to authenticate: " . $email);

    // First, check if the user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    $user = $stmt->fetch();

    // Debug user retrieval
    if (!$user) {
      error_log("Authentication failed: No user found with email: " . $email);
      return false;
    }

    error_log("User found with ID: " . $user['id'] . ", Role: " . $user['role'] . ", Status: " . $user['status']);

    // Debug password verification
    error_log("Stored password hash: " . substr($user['password'], 0, 20) . "...");
    $password_verify_result = password_verify($password, $user['password']);
    error_log("Password verification result: " . ($password_verify_result ? "SUCCESS" : "FAILED"));

    // Check if account is active
    if ($user['status'] != 1) {
      error_log("Authentication failed: User account is inactive");
      return false;
    }

    // Check password
    if (!$password_verify_result) {
      error_log("Authentication failed: Invalid password");
      return false;
    }

    // Authentication successful
    error_log("Authentication successful for user: " . $email);

    // Update last login timestamp
    $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
    $updateStmt->bindParam(':id', $user['id']);
    $updateStmt->execute();

    return $user;
  } catch (PDOException $e) {
    error_log("Database error during authentication: " . $e->getMessage());
    return false;
  } catch (Exception $e) {
    error_log("General error during authentication: " . $e->getMessage());
    return false;
  }
}
