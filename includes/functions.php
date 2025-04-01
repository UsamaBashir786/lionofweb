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
  return $randomString;
}

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
 * Updates an article with enhanced fields
 *
 * @param object $conn Database connection
 * @param int $article_id Article ID
 * @param string $title Article title
 * @param string $content Article content
 * @param int $category_id Category ID
 * @param string $image Image path
 * @param string $status Article status
 * @param int $featured Featured flag
 * @param int $breaking Breaking news flag
 * @param string $subtitle Article subtitle
 * @param string $excerpt Article excerpt
 * @param string $source Original source name
 * @param string $source_url Original source URL
 * @param string $publish_date Article publish date
 * @param string $tags Article tags
 * @param string $meta_title Meta title for SEO
 * @param string $meta_description Meta description for SEO
 * @param string $meta_keywords Meta keywords for SEO
 * @param string $social_title Social media title
 * @param string $social_description Social media description
 * @param string $social_image Social media image
 * @return bool True on success, false on failure
 */
function updateArticleEnhanced(
  $conn,
  $article_id,
  $title,
  $content,
  $category_id,
  $image,
  $status,
  $featured,
  $breaking,
  $subtitle = '',
  $excerpt = '',
  $source = '',
  $source_url = '',
  $publish_date = '',
  $tags = '',
  $meta_title = '',
  $meta_description = '',
  $meta_keywords = '',
  $social_title = '',
  $social_description = '',
  $social_image = ''
) {

  // If excerpt is empty, generate from content
  if (empty($excerpt)) {
    $excerpt = generateExcerpt($content);
  }

  // If meta title is empty, use article title
  if (empty($meta_title)) {
    $meta_title = $title;
  }

  // If social title is empty, use meta title
  if (empty($social_title)) {
    $social_title = $meta_title;
  }

  // If social description is empty, use meta description
  if (empty($social_description)) {
    $social_description = $meta_description;
  }

  // Store previous version in revisions table
  $old_article = getArticleById($conn, $article_id);
  if ($old_article) {
    $revision_sql = "INSERT INTO article_revisions (article_id, title, content, modified_at, modified_by) 
                         VALUES (?, ?, ?, NOW(), ?)";
    $revision_stmt = $conn->prepare($revision_sql);
    $admin_id = $_SESSION['admin_id'] ?? 1; // Default to admin ID 1 if not set
    $revision_stmt->bind_param("issi", $article_id, $old_article['title'], $old_article['content'], $admin_id);
    $revision_stmt->execute();
  }

  // Update the article with enhanced fields
  $sql = "UPDATE articles SET 
            title = ?, 
            content = ?, 
            category_id = ?, 
            image = ?, 
            status = ?, 
            featured = ?, 
            breaking = ?,
            subtitle = ?,
            excerpt = ?,
            source = ?,
            source_url = ?,
            publish_date = ?,
            tags = ?,
            meta_title = ?,
            meta_description = ?,
            meta_keywords = ?,
            social_title = ?,
            social_description = ?,
            social_image = ?,
            updated_at = NOW() 
            WHERE id = ?";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param(
    "ssissiisssssssssssi",
    $title,
    $content,
    $category_id,
    $image,
    $status,
    $featured,
    $breaking,
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
    $social_image,
    $article_id
  );

  $result = $stmt->execute();
  $stmt->close();

  return $result;
}

/**
 * Adds an article with enhanced fields to the database
 *
 * @param object $conn Database connection
 * @param string $title Article title
 * @param string $content Article content
 * @param int $category_id Category ID
 * @param string $image Image path
 * @param string $status Article status
 * @param int $featured Featured flag
 * @param int $breaking Breaking news flag
 * @param int $author_id Author ID
 * @param string $subtitle Article subtitle
 * @param string $excerpt Article excerpt
 * @param string $source Original source name
 * @param string $source_url Original source URL
 * @param string $publish_date Article publish date
 * @param string $tags Article tags
 * @param string $meta_title Meta title for SEO
 * @param string $meta_description Meta description for SEO
 * @param string $meta_keywords Meta keywords for SEO
 * @param string $social_title Social media title
 * @param string $social_description Social media description
 * @param string $social_image Social media image
 * @return int|bool Article ID on success, false on failure
 */
function addArticleEnhanced(
  $conn,
  $title,
  $content,
  $category_id,
  $image,
  $status,
  $featured,
  $breaking,
  $author_id,
  $subtitle = '',
  $excerpt = '',
  $source = '',
  $source_url = '',
  $publish_date = '',
  $tags = '',
  $meta_title = '',
  $meta_description = '',
  $meta_keywords = '',
  $social_title = '',
  $social_description = '',
  $social_image = ''
) {

  // If excerpt is empty, generate from content
  if (empty($excerpt)) {
    $excerpt = generateExcerpt($content);
  }

  // If meta title is empty, use article title
  if (empty($meta_title)) {
    $meta_title = $title;
  }

  // If social title is empty, use meta title
  if (empty($social_title)) {
    $social_title = $meta_title;
  }

  // If social description is empty, use meta description
  if (empty($social_description)) {
    $social_description = $meta_description;
  }

  // Set publish date to current date if empty
  if (empty($publish_date)) {
    $publish_date = date('Y-m-d H:i:s');
  }

  // Insert the article with enhanced fields
  $sql = "INSERT INTO articles (
            title, 
            content, 
            category_id, 
            image, 
            status, 
            featured, 
            breaking, 
            author_id, 
            subtitle,
            excerpt,
            source,
            source_url,
            publish_date,
            tags,
            meta_title,
            meta_description,
            meta_keywords,
            social_title,
            social_description,
            social_image,
            created_at, 
            updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
        )";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param(
    "ssissiissssssssssss",
    $title,
    $content,
    $category_id,
    $image,
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

  $result = $stmt->execute();

  if ($result) {
    $article_id = $stmt->insert_id;
    $stmt->close();

    // Log first article view
    logArticleView($conn, $article_id);

    return $article_id;
  } else {
    $stmt->close();
    return false;
  }
}

/**
 * Generates an excerpt from the article content
 *
 * @param string $content The article content
 * @param int $length The max length of the excerpt
 * @return string The generated excerpt
 */
function generateExcerpt($content, $length = 160)
{
  // Strip HTML tags
  $text = strip_tags($content);

  // Replace multiple spaces, newlines, etc. with single space
  $text = preg_replace('/\s+/', ' ', $text);

  // Trim the text to specified length
  $text = substr($text, 0, $length);

  // Ensure the text doesn't cut off in the middle of a word
  if (strlen($text) == $length) {
    $text = substr($text, 0, strrpos($text, ' '));
  }

  // Add ellipsis if the text was trimmed
  if (strlen(strip_tags($content)) > strlen($text)) {
    $text .= '...';
  }

  return $text;
}

/**
 * Log an article view
 *
 * @param object $conn Database connection
 * @param int $article_id Article ID
 * @return bool True on success, false on failure
 */
function logArticleView($conn, $article_id)
{
  $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
  $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

  $sql = "INSERT INTO article_views (article_id, view_date, ip_address, user_agent) VALUES (?, NOW(), ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("iss", $article_id, $ip_address, $user_agent);
  $result = $stmt->execute();
  $stmt->close();

  return $result;
}

/**
 * Get article view count
 *
 * @param object $conn Database connection
 * @param int $article_id Article ID
 * @return int Number of views
 */
function getArticleViewCount($conn, $article_id)
{
  $sql = "SELECT COUNT(*) as count FROM article_views WHERE article_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $article_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['count'] ?? 0;
}

/**
 * Get article comment count
 *
 * @param object $conn Database connection
 * @param int $article_id Article ID
 * @return int Number of approved comments
 */
function getArticleCommentCount($conn, $article_id)
{
  $sql = "SELECT COUNT(*) as count FROM article_comments WHERE article_id = ? AND status = 'approved'";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $article_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['count'] ?? 0;
}

/**
 * Get article revisions
 *
 * @param object $conn Database connection
 * @param int $article_id Article ID
 * @return array Array of revision records
 */
function getArticleRevisions($conn, $article_id)
{
  $sql = "SELECT * FROM article_revisions WHERE article_id = ? ORDER BY modified_at DESC";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $article_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $revisions = [];
  while ($row = $result->fetch_assoc()) {
    $revisions[] = $row;
  }
  return $revisions;
}

/**
 * Get admin name by ID
 *
 * @param object $conn Database connection
 * @param int $admin_id Admin ID
 * @return string|null Admin username or null if not found
 */
function getAdminNameById($conn, $admin_id)
{
  $sql = "SELECT username FROM admins WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $admin_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['username'] ?? null;
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
 * @param object $conn Database connection
 * @param int $id Article ID
 * @return array|bool Article data or false if not found
 */
function getArticleById($conn, $id)
{
  if (is_a($conn, 'PDO')) {
    $stmt = $conn->prepare("SELECT * FROM articles WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
  } else {
    $sql = "SELECT * FROM articles WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      return $result->fetch_assoc();
    }

    return false;
  }
}

/**
 * Get category by ID
 * 
 * @param object $conn Database connection
 * @param int $category_id Category ID
 * @return array|false Category data or false if not found
 */
function getCategoryById($conn, $category_id)
{
  if (is_a($conn, 'PDO')) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = :id");
    $stmt->bindParam(':id', $category_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
  } else {
    $sql = "SELECT * FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      return $result->fetch_assoc();
    }

    return false;
  }
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
 * @param object $conn Database connection
 * @param bool $include_inactive Include inactive categories
 * @return array Categories
 */
function getAllCategories($conn, $include_inactive = false)
{
  if (is_a($conn, 'PDO')) {
    $sql = "SELECT c.*, (SELECT COUNT(*) FROM articles WHERE category_id = c.id) AS article_count 
            FROM categories c";

    if (!$include_inactive) {
      $sql .= " WHERE c.is_active = 1";
    }

    $sql .= " ORDER BY c.name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll();
  } else {
    $sql = "SELECT c.*, (SELECT COUNT(*) FROM articles WHERE category_id = c.id) AS article_count 
            FROM categories c";

    if (!$include_inactive) {
      $sql .= " WHERE c.is_active = 1";
    }

    $sql .= " ORDER BY c.name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $categories = [];
    while ($row = $result->fetch_assoc()) {
      $categories[] = $row;
    }

    return $categories;
  }
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
