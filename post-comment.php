<?php
// Enable full error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'config/config.php';
require_once 'config/database.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// Log function for debugging
function logDebug($message)
{
  $log_file = 'comment_debug.log';
  $log_message = date('[Y-m-d H:i:s] ') . $message . PHP_EOL;
  file_put_contents($log_file, $log_message, FILE_APPEND);
}

// Log all incoming POST data
logDebug('Incoming POST data: ' . print_r($_POST, true));

// Validate and sanitize input
$article_id = filter_input(INPUT_POST, 'article_id', FILTER_VALIDATE_INT);
$article_slug = trim(filter_input(INPUT_POST, 'article_slug', FILTER_SANITIZE_STRING));
$name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$comment_text = trim(filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING));

logDebug("Processed Inputs - Article ID: $article_id, Slug: $article_slug, Name: $name, Email: $email");

// Validation checks
$errors = [];

// Check article ID
if (!$article_id) {
  $errors[] = 'Invalid article.';
  logDebug('Validation Error: Invalid article ID');
}

// Check name
if (empty($name)) {
  $errors[] = 'Name is required.';
  logDebug('Validation Error: Name is empty');
}

// Check email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $errors[] = 'Invalid email address.';
  logDebug('Validation Error: Invalid email');
}

// Check comment text
if (empty($comment_text)) {
  $errors[] = 'Comment is required.';
  logDebug('Validation Error: Comment is empty');
}

// If there are validation errors
if (!empty($errors)) {
  logDebug('Validation errors occurred: ' . print_r($errors, true));

  $_SESSION['comment_errors'] = $errors;
  $_SESSION['comment_form_data'] = [
    'name' => $name,
    'email' => $email,
    'comment' => $comment_text
  ];

  header("Location: articles.php?slug=" . urlencode($article_slug));
  exit;
}

try {
  // Begin a transaction
  $conn->beginTransaction();

  // Verify article exists
  $check_stmt = $conn->prepare("SELECT id FROM articles WHERE id = :article_id");
  $check_stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
  $check_stmt->execute();

  if ($check_stmt->rowCount() == 0) {
    logDebug("Article not found with ID: $article_id");
    throw new Exception("Article not found.");
  }

  // Prepare SQL to insert comment
  $stmt = $conn->prepare("
        INSERT INTO comments (
            article_id, 
            name, 
            email, 
            comment_text, 
            status,
            created_at
        ) VALUES (
            :article_id, 
            :name, 
            :email, 
            :comment_text, 
            'approved',
            NOW()
        )
    ");

  // Bind parameters
  $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
  $stmt->bindParam(':name', $name, PDO::PARAM_STR);
  $stmt->bindParam(':email', $email, PDO::PARAM_STR);
  $stmt->bindParam(':comment_text', $comment_text, PDO::PARAM_STR);

  // Execute the statement
  $insert_result = $stmt->execute();

  // Log insert result
  if ($insert_result) {
    logDebug('Comment inserted successfully. Rows affected: ' . $stmt->rowCount());
  } else {
    $error_info = $stmt->errorInfo();
    logDebug('Insert failed. Error Info: ' . print_r($error_info, true));
  }

  // Update comment count in articles table
  $update_stmt = $conn->prepare("
        UPDATE articles 
        SET comment_count = comment_count + 1 
        WHERE id = :article_id
    ");
  $update_stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
  $update_result = $update_stmt->execute();

  // Log update result
  if ($update_result) {
    logDebug('Comment count updated successfully.');
  } else {
    $error_info = $update_stmt->errorInfo();
    logDebug('Update failed. Error Info: ' . print_r($error_info, true));
  }

  // Commit the transaction
  $conn->commit();

  // Set success message
  $_SESSION['comment_success'] = 'Your comment has been submitted successfully!';

  // Redirect back to the article page
  header("Location: articles.php?slug=" . urlencode($article_slug));
  exit;
} catch (PDOException $e) {
  // Rollback the transaction
  $conn->rollBack();

  // Log detailed error information
  logDebug('PDO Exception: ' . $e->getMessage());
  logDebug('Error Details: ' . print_r($e, true));

  // Set error message
  $_SESSION['comment_error'] = 'A database error occurred while submitting your comment. Please try again.';

  // Redirect back to the article page
  header("Location: articles.php?slug=" . urlencode($article_slug));
  exit;
} catch (Exception $e) {
  // Rollback the transaction
  $conn->rollBack();

  // Log error
  logDebug('General Exception: ' . $e->getMessage());

  // Set error message
  $_SESSION['comment_error'] = $e->getMessage();

  // Redirect back to the article page
  header("Location: articles.php?slug=" . urlencode($article_slug));
  exit;
}
