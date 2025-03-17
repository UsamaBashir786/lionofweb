<?php
// Include configuration files
require_once '../config/config.php';
require_once '../config/auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header("Location: login.php");
  exit;
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
  // If user doesn't exist, log them out
  session_destroy();
  header("Location: login.php");
  exit;
}

// Process form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Get form data
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $current_password = trim($_POST['current_password']);
  $new_password = trim($_POST['new_password']);
  $confirm_password = trim($_POST['confirm_password']);

  // Validate input
  if (empty($name)) {
    $error_message = "Name is required.";
  } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error_message = "Valid email is required.";
  } elseif ($email !== $user['email']) {
    // Check if email already exists
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()['count'] > 0) {
      $error_message = "Email address is already in use by another account.";
    }
  }

  // If password change is requested
  if (!empty($current_password)) {
    if (!password_verify($current_password, $user['password'])) {
      $error_message = "Current password is incorrect.";
    } elseif (empty($new_password)) {
      $error_message = "New password is required.";
    } elseif (strlen($new_password) < 8) {
      $error_message = "New password must be at least 8 characters long.";
    } elseif ($new_password !== $confirm_password) {
      $error_message = "New password and confirmation do not match.";
    }
  }

  // Upload avatar if provided
  $avatar = $user['avatar']; // Keep existing avatar by default
  if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB

    if (!in_array($_FILES['avatar']['type'], $allowed_types)) {
      $error_message = "Only JPG, PNG and GIF images are allowed.";
    } elseif ($_FILES['avatar']['size'] > $max_size) {
      $error_message = "Image size exceeds the maximum limit (2MB).";
    } else {
      $upload_dir = '../' . USER_AVATARS_DIR;
      if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
      }

      $filename = time() . '_' . basename($_FILES['avatar']['name']);
      $target_file = $upload_dir . $filename;

      if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
        // Delete previous avatar if it exists
        if (!empty($user['avatar']) && file_exists('../' . $user['avatar'])) {
          unlink('../' . $user['avatar']);
        }
        $avatar = USER_AVATARS_DIR . $filename;
      } else {
        $error_message = "Failed to upload image. Please try again.";
      }
    }
  }

  // Update user data if no errors
  if (empty($error_message)) {
    try {
      $conn->beginTransaction();

      // Update user details
      $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, avatar = ?, updated_at = NOW() WHERE id = ?");
      $stmt->execute([$name, $email, $avatar, $user_id]);

      // Update password if changed
      if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $user_id]);
      }

      $conn->commit();

      // Update session data
      $_SESSION['user_name'] = $name;
      $_SESSION['user_email'] = $email;
      $_SESSION['user_avatar'] = $avatar;

      $success_message = "Profile updated successfully!";

      // Refresh user data
      $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
      $stmt->execute([$user_id]);
      $user = $stmt->fetch();
    } catch (PDOException $e) {
      $conn->rollBack();
      $error_message = "Database error: " . $e->getMessage();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile - NewsHub Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    .sidebar-active {
      border-left: 4px solid #3B82F6;
      background-color: rgba(59, 130, 246, 0.1);
    }

    #avatar-preview {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid #EEE;
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
            <h1 class="text-2xl font-semibold text-gray-900">My Profile</h1>
          </div>
          <div class="px-4 mx-auto max-w-7xl sm:px-6 md:px-8 mt-5">
            <!-- Success message -->
            <?php if (!empty($success_message)): ?>
              <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $success_message; ?></span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                  <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>Close</title>
                    <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                  </svg>
                </span>
              </div>
            <?php endif; ?>

            <!-- Error message -->
            <?php if (!empty($error_message)): ?>
              <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error_message; ?></span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                  <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>Close</title>
                    <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                  </svg>
                </span>
              </div>
            <?php endif; ?>

            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
              <div class="p-6">
                <div class="md:grid md:grid-cols-3 md:gap-6">
                  <div class="md:col-span-1">
                    <div class="flex flex-col items-center justify-center">
                      <?php if (!empty($user['avatar']) && file_exists('../' . $user['avatar'])): ?>
                        <img id="avatar-preview" src="<?php echo SITE_URL . '/' . $user['avatar']; ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
                      <?php else: ?>
                        <div id="avatar-preview" class="bg-blue-100 flex items-center justify-center">
                          <i class="fas fa-user text-blue-500 text-5xl"></i>
                        </div>
                      <?php endif; ?>

                      <div class="mt-6 w-full">
                        <h3 class="text-lg font-medium text-gray-900">Account Information</h3>
                        <p class="mt-1 text-sm text-gray-600">
                          Update your account details and profile picture.
                        </p>
                        <div class="mt-4 border-t border-gray-200 pt-4">
                          <dl class="divide-y divide-gray-200">
                            <div class="py-3 flex justify-between">
                              <dt class="text-sm font-medium text-gray-500">Account Type</dt>
                              <dd class="text-sm font-medium">
                                <span class="px-2 py-1 rounded-full text-xs font-medium 
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
                              </dd>
                            </div>
                            <div class="py-3 flex justify-between">
                              <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                              <dd class="text-sm text-gray-900"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></dd>
                            </div>
                            <div class="py-3 flex justify-between">
                              <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                              <dd class="text-sm text-gray-900"><?php echo date('M d, Y', strtotime($user['updated_at'])); ?></dd>
                            </div>
                          </dl>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="mt-5 md:mt-0 md:col-span-2">
                    <form action="" method="POST" enctype="multipart/form-data">
                      <div class="grid grid-cols-6 gap-6">
                        <div class="col-span-6 sm:col-span-3">
                          <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                          <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" required
                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                          <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                          <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div class="col-span-6">
                          <label for="avatar" class="block text-sm font-medium text-gray-700">Profile Picture</label>
                          <div class="mt-1 flex items-center">
                            <input type="file" name="avatar" id="avatar" accept="image/*" class="sr-only">
                            <label for="avatar" class="relative cursor-pointer bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                              <span>Choose file</span>
                              <span id="file-name" class="ml-1 text-gray-500">No file selected</span>
                            </label>
                          </div>
                          <p class="mt-2 text-xs text-gray-500">
                            PNG, JPG, GIF up to 2MB. Recommended size: 200x200px.
                          </p>
                        </div>

                        <div class="col-span-6">
                          <hr class="my-4">
                          <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Change Password</h3>
                            <p class="text-sm text-gray-500">Leave blank to keep current password</p>
                          </div>
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                          <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                          <input type="password" name="current_password" id="current_password" autocomplete="current-password"
                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div class="col-span-6 sm:col-span-3"></div>

                        <div class="col-span-6 sm:col-span-3">
                          <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                          <input type="password" name="new_password" id="new_password" autocomplete="new-password"
                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                          <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                          <input type="password" name="confirm_password" id="confirm_password" autocomplete="new-password"
                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                      </div>

                      <div class="mt-6 flex justify-end">
                        <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                          Save Changes
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <script>
    // Display selected file name
    document.getElementById('avatar').addEventListener('change', function() {
      const fileName = this.files[0] ? this.files[0].name : 'No file selected';
      document.getElementById('file-name').textContent = fileName;

      // Update avatar preview
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const preview = document.getElementById('avatar-preview');
          preview.style.backgroundImage = 'none';
          preview.innerHTML = '';

          if (preview.tagName === 'IMG') {
            preview.src = e.target.result;
          } else {
            // If it's a div, create an img element
            const img = document.createElement('img');
            img.src = e.target.result;
            img.id = 'avatar-preview';
            img.className = 'w-24 h-24 rounded-full object-cover border-3 border-gray-200';

            // Replace the div with the img
            preview.parentNode.replaceChild(img, preview);
          }
        };
        reader.readAsDataURL(this.files[0]);
      }
    });

    // Password validation
    document.querySelector('form').addEventListener('submit', function(e) {
      const currentPassword = document.getElementById('current_password').value;
      const newPassword = document.getElementById('new_password').value;
      const confirmPassword = document.getElementById('confirm_password').value;

      // If any password field is filled, validate the others
      if (currentPassword || newPassword || confirmPassword) {
        if (!currentPassword) {
          e.preventDefault();
          alert('Please enter your current password.');
        } else if (!newPassword) {
          e.preventDefault();
          alert('Please enter a new password.');
        } else if (newPassword.length < 8) {
          e.preventDefault();
          alert('New password must be at least 8 characters long.');
        } else if (newPassword !== confirmPassword) {
          e.preventDefault();
          alert('New password and confirm password do not match.');
        }
      }
    });

    // Close alert messages
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