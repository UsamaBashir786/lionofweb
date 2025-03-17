<?php
// Include configuration files
require_once 'config.php';
require_once 'database.php';
require_once '../includes/functions.php';

// Start session
session_start();

// Handle login request
if (isset($_POST['action']) && $_POST['action'] === 'login') {
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);
  $remember = isset($_POST['remember-me']) ? true : false;

  // Validate input
  if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = "Email and password are required.";
    header("Location: ../login.php");
    exit;
  }

  // Authenticate user
  $user = authenticateUser($conn, $email, $password);

  if ($user) {
    // Set session variables
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_avatar'] = $user['avatar'];

    // Set remember me cookie if requested
    if ($remember) {
      $token = generateRandomString(32);
      $expires = time() + REMEMBER_ME_LIFETIME;

      // Store token in database
      $stmt = $conn->prepare("INSERT INTO user_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
      $stmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
      $stmt->bindParam(':token', $token);
      $stmt->bindParam(':expires_at', date('Y-m-d H:i:s', $expires));
      $stmt->execute();

      // Set cookie
      setcookie('remember_token', $token, $expires, '/', '', false, true);
    }

    // Redirect based on user role
    if ($user['role'] === 'admin' || $user['role'] === 'editor') {
      header("Location: ../dashboard.php");
    } else {
      header("Location: ../index.php");
    }
    exit;
    exit;
  } else {
    $_SESSION['login_error'] = "Invalid email or password, or your account has been deactivated.";
    header("Location: ../login.php");
    exit;
  }
}

// Handle registration request
if (isset($_POST['action']) && $_POST['action'] === 'register') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);
  $confirm_password = trim($_POST['confirm_password']);

  // Validate input
  if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
    $_SESSION['register_error'] = "All fields are required.";
    header("Location: ../register.php");
    exit;
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['register_error'] = "Please enter a valid email address.";
    header("Location: ../register.php");
    exit;
  }

  if ($password !== $confirm_password) {
    $_SESSION['register_error'] = "Passwords do not match.";
    header("Location: ../register.php");
    exit;
  }

  if (strlen($password) < 8) {
    $_SESSION['register_error'] = "Password must be at least 8 characters long.";
    header("Location: ../register.php");
    exit;
  }

  // Check if email already exists
  if (checkEmailExists($conn, $email)) {
    $_SESSION['register_error'] = "Email address already exists. Please use a different email or login.";
    header("Location: ../register.php");
    exit;
  }

  // Hash password
  $hashed_password = password_hash($password, PASSWORD_DEFAULT);

  // Add user to database (as subscriber by default)
  $result = addUser($conn, $name, $email, $hashed_password, 'subscriber', '', 1);

  if ($result) {
    $_SESSION['login_success'] = "Registration successful! You can now login.";
    header("Location: ../login.php");
    exit;
  } else {
    $_SESSION['register_error'] = "Error creating account. Please try again.";
    header("Location: ../register.php");
    exit;
  }
}

// If no valid action was specified, redirect to login page
header("Location: ../login.php");
exit;
