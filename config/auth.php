<?php
// Include configuration files
require_once 'config.php';
require_once 'database.php';
require_once '../includes/functions.php';

// Ensure we have a clean session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log request source for debugging
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'unknown';
$action = isset($_POST['action']) ? $_POST['action'] : 'none';
error_log("Auth.php called from: {$referer} for action: {$action}");

// Determine if request comes from admin area
$is_admin_login = (strpos($referer, '/admin/') !== false);

// Handle login request
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember-me']) ? true : false;

    // Validate input
    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Email and password are required.";
        
        if ($is_admin_login) {
            header("Location: ../admin/login.php");
        } else {
            header("Location: ../login.php");
        }
        exit;
    }

    // Debug connection and credentials
    error_log("Attempting login for: {$email} from {$referer}");
    
    // Explicitly test for database errors
    try {
        $conn->query("SELECT 1");
        error_log("Database connection confirmed active");
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        $_SESSION['login_error'] = "System error. Please try again later.";
        
        if ($is_admin_login) {
            header("Location: ../admin/login.php");
        } else {
            header("Location: ../login.php");
        }
        exit;
    }
    
    // Authenticate user - use our own authentication code here to bypass any issues
    try {
        // Direct SQL query to get user
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch();
        
        // Check user exists
        if (!$user) {
            error_log("Login failed: No user found with email: {$email}");
            $_SESSION['login_error'] = "Invalid email or password, or your account has been deactivated.";
            
            if ($is_admin_login) {
                header("Location: ../admin/login.php");
            } else {
                header("Location: ../login.php");
            }
            exit;
        }
        
        // Check password
        if (!password_verify($password, $user['password'])) {
            error_log("Login failed: Invalid password for email: {$email}");
            $_SESSION['login_error'] = "Invalid email or password, or your account has been deactivated.";
            
            if ($is_admin_login) {
                header("Location: ../admin/login.php");
            } else {
                header("Location: ../login.php");
            }
            exit;
        }
        
        // Check account status
        if ($user['status'] != 1) {
            error_log("Login failed: Account inactive for email: {$email}");
            $_SESSION['login_error'] = "Invalid email or password, or your account has been deactivated.";
            
            if ($is_admin_login) {
                header("Location: ../admin/login.php");
            } else {
                header("Location: ../login.php");
            }
            exit;
        }
        
        // Authentication successful!
        error_log("Authentication successful for user: {$email}");
        
        // Clear any previous session data
        $_SESSION = array();
        
        // Generate new session ID
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['admin_logged_in'] = ($user['role'] === 'admin' || $user['role'] === 'editor');
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_avatar'] = $user['avatar'] ?? '';
        
        // Log session data
        error_log("Session data set: " . print_r($_SESSION, true));
        
        // Set remember me cookie if requested
        if ($remember) {
            $token = generateRandomString(32);
            $expires = time() + (30 * 24 * 60 * 60); // 30 days
            
            // Store token in database
            try {
                $stmt = $conn->prepare("INSERT INTO user_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
                $stmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
                $stmt->bindParam(':token', $token);
                $stmt->bindParam(':expires_at', date('Y-m-d H:i:s', $expires));
                $stmt->execute();
                
                // Set cookie
                setcookie('remember_token', $token, $expires, '/', '', false, true);
            } catch (Exception $e) {
                error_log("Could not set remember me token: " . $e->getMessage());
                // Continue anyway - this is not a critical error
            }
        }
        
        // Update last login time
        try {
            $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
            $stmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Could not update last login time: " . $e->getMessage());
            // Continue anyway - this is not a critical error
        }
        
        // Fix redirect path construction - this was a problem in the original code
        if ($user['role'] === 'admin' || $user['role'] === 'editor') {
            error_log("Redirecting to admin dashboard");
            header("Location: ../admin/dashboard.php");
        } else {
            error_log("Redirecting to user homepage");
            header("Location: ../index.php");
        }
        exit;
        
    } catch (Exception $e) {
        error_log("Authentication error: " . $e->getMessage());
        $_SESSION['login_error'] = "System error. Please try again later.";
        
        if ($is_admin_login) {
            header("Location: ../admin/login.php");
        } else {
            header("Location: ../login.php");
        }
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
if ($is_admin_login) {
    header("Location: ../admin/login.php");
} else {
    header("Location: ../login.php");
}
exit;