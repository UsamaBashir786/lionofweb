<?php
// Include configuration files
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and has admin privileges
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

// Handle user status toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
  $user_id = $_GET['toggle'];
  // Prevent deactivating own account
  if ($user_id == $_SESSION['user_id']) {
    $_SESSION['error_message'] = "You cannot deactivate your own account.";
  } else {
    $toggled = toggleUserStatus($conn, $user_id);
    if ($toggled) {
      $_SESSION['success_message'] = "User status updated successfully!";
    } else {
      $_SESSION['error_message'] = "Error updating user status.";
    }
  }
  // Redirect to remove the get parameter
  header("Location: manage-users.php");
  exit;
}

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
  $user_id = $_GET['delete'];
  // Prevent deleting own account
  if ($user_id == $_SESSION['user_id']) {
    $_SESSION['error_message'] = "You cannot delete your own account.";
  } else {
    $deleted = deleteUser($conn, $user_id);
    if ($deleted) {
      $_SESSION['success_message'] = "User successfully deleted!";
    } else {
      $_SESSION['error_message'] = "Error deleting user.";
    }
  }
  // Redirect to remove the get parameter
  header("Location: manage-users.php");
  exit;
}

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Filter parameters
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$status_filter = isset($_GET['status']) ? (int)$_GET['status'] : -1; // -1 means all
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Get total number of users
$total_users = getTotalUsers($conn, $role_filter, $status_filter, $search_query);
$total_pages = ceil($total_users / $items_per_page);

// Get users with pagination and filtering
$users = getUsers($conn, $offset, $items_per_page, $role_filter, $status_filter, $search_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users - NewsHub Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    .sidebar-active {
      border-left: 4px solid #3B82F6;
      background-color: rgba(59, 130, 246, 0.1);
    }
  </style>
  <?php include '../includes/style.php' ?>
</head>

<body class="bg-gray-100 font-sans">
  <div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <?php include_once '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex flex-col flex-1 w-0 overflow-hidden">
      <!-- Top Navigation -->
      <?php include_once '../includes/navbar.php'; ?>

      <!-- Main Content Area -->
      <main class="relative flex-1 overflow-y-auto focus:outline-none">
        <div class="py-6">
          <div class="px-4 mx-auto max-w-7xl sm:px-6 md:px-8">
            <div class="md:flex md:items-center md:justify-between">
              <div class="flex-1 min-w-0">
                <h1 class="text-2xl font-semibold text-gray-900">Manage Users</h1>
              </div>
              <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="add-user.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                  <i class="fas fa-user-plus -ml-1 mr-2 h-5 w-5"></i>
                  Add New User
                </a>
              </div>
            </div>
          </div>

          <div class="px-4 mx-auto max-w-7xl sm:px-6 md:px-8 mt-5">
            <!-- Success message -->
            <?php if (isset($_SESSION['success_message'])): ?>
              <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['success_message']; ?></span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                  <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>Close</title>
                    <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                  </svg>
                </span>
              </div>
            <?php unset($_SESSION['success_message']);
            endif; ?>

            <!-- Error message -->
            <?php if (isset($_SESSION['error_message'])): ?>
              <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['error_message']; ?></span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                  <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <title>Close</title>
                    <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
                  </svg>
                </span>
              </div>
            <?php unset($_SESSION['error_message']);
            endif; ?>

            <!-- Filters -->
            <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 mb-5">
              <div class="md:flex md:items-center">
                <div class="md:flex-1">
                  <h2 class="text-lg font-medium text-gray-900 mb-4">Filter Users</h2>
                  <form action="" method="GET" class="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-2">
                      <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                      <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                          <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search_query); ?>"
                          class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md"
                          placeholder="Search by name or email...">
                      </div>
                    </div>

                    <div class="sm:col-span-2">
                      <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                      <select id="role" name="role"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">All Roles</option>
                        <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="editor" <?php echo $role_filter === 'editor' ? 'selected' : ''; ?>>Editor</option>
                        <option value="author" <?php echo $role_filter === 'author' ? 'selected' : ''; ?>>Author</option>
                        <option value="subscriber" <?php echo $role_filter === 'subscriber' ? 'selected' : ''; ?>>Subscriber</option>
                      </select>
                    </div>

                    <div class="sm:col-span-2">
                      <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                      <select id="status" name="status"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="-1">All Statuses</option>
                        <option value="1" <?php echo $status_filter === 1 ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo $status_filter === 0 ? 'selected' : ''; ?>>Inactive</option>
                      </select>
                    </div>

                    <div class="sm:col-span-6">
                      <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Apply Filters
                      </button>
                      <a href="manage-users.php"
                        class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Reset
                      </a>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Users Table -->
            <div class="flex flex-col">
              <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                  <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                      <thead class="bg-gray-50">
                        <tr>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            User
                          </th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Role
                          </th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                          </th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Articles
                          </th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Registered
                          </th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                          </th>
                        </tr>
                      </thead>
                      <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($users)): ?>
                          <?php foreach ($users as $user): ?>
                            <tr>
                              <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                  <div class="flex-shrink-0 h-10 w-10">
                                    <?php if (!empty($user['avatar'])): ?>
                                      <img class="h-10 w-10 rounded-full object-cover" src="../../<?php echo htmlspecialchars($user['avatar']); ?>" alt="">
                                    <?php else: ?>
                                      <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-user text-blue-500"></i>
                                      </div>
                                    <?php endif; ?>
                                  </div>
                                  <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                      <?php echo htmlspecialchars($user['name']); ?>
                                      <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                          You
                                        </span>
                                      <?php endif; ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                      <?php echo htmlspecialchars($user['email']); ?>
                                    </div>
                                  </div>
                                </div>
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                  <?php
                                  switch ($user['role']) {
                                    case 'admin':
                                      echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Admin</span>';
                                      break;
                                    case 'editor':
                                      echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Editor</span>';
                                      break;
                                    case 'author':
                                      echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Author</span>';
                                      break;
                                    default:
                                      echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Subscriber</span>';
                                  }
                                  ?>
                                </div>
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($user['status'] == 1): ?>
                                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                  </span>
                                <?php else: ?>
                                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Inactive
                                  </span>
                                <?php endif; ?>
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo isset($user['article_count']) ? $user['article_count'] : 0; ?>
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                  <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-edit"></i> Edit
                                  </a>
                                  <a href="manage-users.php?toggle=<?php echo $user['id']; ?>" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                    <?php if ($user['status'] == 1): ?>
                                      <i class="fas fa-toggle-on"></i> Deactivate
                                    <?php else: ?>
                                      <i class="fas fa-toggle-off"></i> Activate
                                    <?php endif; ?>
                                  </a>
                                  <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $user['id']; ?>)" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i> Delete
                                  </a>
                                <?php else: ?>
                                  <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-edit"></i> Edit
                                  </a>
                                  <span class="text-gray-400"><i class="fas fa-ban"></i> Cannot modify own account</span>
                                <?php endif; ?>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                              No users found. <a href="add-user.php" class="text-blue-600 hover:text-blue-900">Add a new user</a>.
                            </td>
                          </tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
              <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 mt-4 rounded-lg">
                <div class="flex-1 flex justify-between sm:hidden">
                  <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo !empty($role_filter) ? '&role=' . urlencode($role_filter) : ''; ?><?php echo $status_filter !== -1 ? '&status=' . $status_filter : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
                      class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                      Previous
                    </a>
                  <?php endif; ?>
                  <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo !empty($role_filter) ? '&role=' . urlencode($role_filter) : ''; ?><?php echo $status_filter !== -1 ? '&status=' . $status_filter : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
                      class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                      Next
                    </a>
                  <?php endif; ?>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                  <div>
                    <p class="text-sm text-gray-700">
                      Showing
                      <span class="font-medium"><?php echo ($page - 1) * $items_per_page + 1; ?></span>
                      to
                      <span class="font-medium"><?php echo min($page * $items_per_page, $total_users); ?></span>
                      of
                      <span class="font-medium"><?php echo $total_users; ?></span>
                      results
                    </p>
                  </div>
                  <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                      <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo !empty($role_filter) ? '&role=' . urlencode($role_filter) : ''; ?><?php echo $status_filter !== -1 ? '&status=' . $status_filter : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
                          class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                          <span class="sr-only">Previous</span>
                          <i class="fas fa-chevron-left"></i>
                        </a>
                      <?php endif; ?>

                      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i === $page): ?>
                          <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-blue-50 text-sm font-medium text-blue-600">
                            <?php echo $i; ?>
                          </span>
                        <?php else: ?>
                          <a href="?page=<?php echo $i; ?><?php echo !empty($role_filter) ? '&role=' . urlencode($role_filter) : ''; ?><?php echo $status_filter !== -1 ? '&status=' . $status_filter : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                            <?php echo $i; ?>
                          </a>
                        <?php endif; ?>
                      <?php endfor; ?>

                      <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo !empty($role_filter) ? '&role=' . urlencode($role_filter) : ''; ?><?php echo $status_filter !== -1 ? '&status=' . $status_filter : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
                          class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                          <span class="sr-only">Next</span>
                          <i class="fas fa-chevron-right"></i>
                        </a>
                      <?php endif; ?>
                    </nav>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Delete confirmation modal -->
  <div id="deleteModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 transition-opacity" aria-hidden="true">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
      </div>

      <!-- Modal panel -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
              <i class="fas fa-exclamation-triangle text-red-600"></i>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
              <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                Delete User
              </h3>
              <div class="mt-2">
                <p class="text-sm text-gray-500">
                  Are you sure you want to delete this user? All associated data will be permanently removed. This action cannot be undone.
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <a href="#" id="confirmDelete" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
            Delete
          </a>
          <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    function confirmDelete(id) {
      const modal = document.getElementById('deleteModal');
      const confirmLink = document.getElementById('confirmDelete');

      modal.classList.remove('hidden');
      confirmLink.href = 'manage-users.php?delete=' + id;
    }

    function closeModal() {
      const modal = document.getElementById('deleteModal');
      modal.classList.add('hidden');
    }

    // Close modal when clicking on the backdrop
    document.addEventListener('click', function(event) {
      const modal = document.getElementById('deleteModal');
      if (event.target === modal) {
        closeModal();
      }
    });
  </script>
</body>

</html>