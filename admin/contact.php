<?php
// Include configuration files
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header("Location: login.php");
  exit;
}

// Handle contact message status toggle (read/unread)
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
  $message_id = $_GET['toggle'];
  try {
    // First get the current status
    $stmt = $conn->prepare("SELECT status FROM contact_submissions WHERE id = ?");
    $stmt->execute([$message_id]);
    $current = $stmt->fetch();

    if ($current) {
      // Toggle the status - assuming 'new' is unread and 'read' is read
      $new_status = ($current['status'] == 'new') ? 'read' : 'new';

      $stmt = $conn->prepare("UPDATE contact_submissions SET status = ?, updated_at = NOW() WHERE id = ?");
      $toggled = $stmt->execute([$new_status, $message_id]);

      if ($toggled) {
        $_SESSION['success_message'] = "Message status updated successfully!";
      } else {
        $_SESSION['error_message'] = "Error updating message status.";
      }
    } else {
      $_SESSION['error_message'] = "Message not found.";
    }
  } catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
  }

  // Redirect to remove the GET parameter
  header("Location: contact.php");
  exit;
}

// Handle contact message deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
  $message_id = $_GET['delete'];
  try {
    $stmt = $conn->prepare("DELETE FROM contact_submissions WHERE id = ?");
    $deleted = $stmt->execute([$message_id]);

    if ($deleted) {
      $_SESSION['success_message'] = "Message successfully deleted!";
    } else {
      $_SESSION['error_message'] = "Error deleting message.";
    }
  } catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
  }

  // Redirect to remove the GET parameter
  header("Location: contact.php");
  exit;
}

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$subject_filter = isset($_GET['subject']) ? $_GET['subject'] : '';
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Get total number of messages
$total_count_sql = "SELECT COUNT(*) as count FROM contact_submissions WHERE 1=1";
$params = [];

if ($status_filter !== '') {
  $total_count_sql .= " AND status = ?";
  $params[] = ($status_filter === 'read') ? 'read' : 'new';
}

if ($subject_filter !== '') {
  $total_count_sql .= " AND subject = ?";
  $params[] = $subject_filter;
}

if ($search_query !== '') {
  $search_param = "%$search_query%";
  $total_count_sql .= " AND (name LIKE ? OR email LIKE ? OR message LIKE ?)";
  $params[] = $search_param;
  $params[] = $search_param;
  $params[] = $search_param;
}

$stmt = $conn->prepare($total_count_sql);
$stmt->execute($params);
$total_messages = $stmt->fetch()['count'];
$total_pages = ceil($total_messages / $items_per_page);

// Get messages with pagination and filtering
$sql = "SELECT * FROM contact_submissions WHERE 1=1";
$params = [];

if ($status_filter !== '') {
  $sql .= " AND status = ?";
  $params[] = ($status_filter === 'read') ? 'read' : 'new';
}

if ($subject_filter !== '') {
  $sql .= " AND subject = ?";
  $params[] = $subject_filter;
}

if ($search_query !== '') {
  $search_param = "%$search_query%";
  $sql .= " AND (name LIKE ? OR email LIKE ? OR message LIKE ?)";
  $params[] = $search_param;
  $params[] = $search_param;
  $params[] = $search_param;
}

$sql .= " ORDER BY created_at DESC LIMIT $offset, $items_per_page";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

// Get message details for view modal
$message_details = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
  $view_id = $_GET['view'];
  $stmt = $conn->prepare("SELECT * FROM contact_submissions WHERE id = ?");
  $stmt->execute([$view_id]);
  $message_details = $stmt->fetch();

  // Mark as read if not already
  if ($message_details && $message_details['status'] == 'new') {
    $stmt = $conn->prepare("UPDATE contact_submissions SET status = 'read', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$view_id]);
    $message_details['status'] = 'read';
  }
}

// Get unique subjects for filter dropdown
$stmt = $conn->query("SELECT DISTINCT subject FROM contact_submissions WHERE subject != '' ORDER BY subject");
$subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get unread count for the badge
$stmt = $conn->query("SELECT COUNT(*) as count FROM contact_submissions WHERE status = 'new'");
$unread_count = $stmt->fetch()['count'];

// Handle session messages
if (isset($_SESSION['success_message'])) {
  $success_message = $_SESSION['success_message'];
  unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
  $error_message = $_SESSION['error_message'];
  unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Contact Messages - NewsHub Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    .sidebar-active {
      border-left: 4px solid #3B82F6;
      background-color: rgba(59, 130, 246, 0.1);
    }
  </style>
  <?php include 'includes/style.php' ?>
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
                <h1 class="text-2xl font-semibold text-gray-900">Contact Messages</h1>
                <?php if ($unread_count > 0): ?>
                  <p class="mt-1 text-sm text-gray-500">
                    You have <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"><?php echo $unread_count; ?> unread</span> messages
                  </p>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="px-4 mx-auto max-w-7xl sm:px-6 md:px-8 mt-5">
            <!-- Success message -->
            <?php if (isset($success_message)): ?>
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
            <?php if (isset($error_message)): ?>
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

            <!-- Filters -->
            <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 mb-5">
              <div class="md:flex md:items-center">
                <div class="md:flex-1">
                  <h2 class="text-lg font-medium text-gray-900 mb-4">Filter Messages</h2>
                  <form action="" method="GET" class="grid grid-cols-1 gap-y-4 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-2">
                      <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                      <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                          <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search_query); ?>"
                          class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md"
                          placeholder="Search by name, email, or message...">
                      </div>
                    </div>

                    <div class="sm:col-span-2">
                      <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                      <select id="status" name="status"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">All Messages</option>
                        <option value="unread" <?php echo $status_filter === 'unread' ? 'selected' : ''; ?>>Unread</option>
                        <option value="read" <?php echo $status_filter === 'read' ? 'selected' : ''; ?>>Read</option>
                      </select>
                    </div>

                    <div class="sm:col-span-2">
                      <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                      <select id="subject" name="subject"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">All Subjects</option>
                        <?php foreach ($subjects as $subject): ?>
                          <option value="<?php echo htmlspecialchars($subject); ?>" <?php echo $subject_filter === $subject ? 'selected' : ''; ?>>
                            <?php echo ucfirst(htmlspecialchars($subject)); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <div class="sm:col-span-6">
                      <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Apply Filters
                      </button>
                      <a href="contact.php"
                        class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Reset
                      </a>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Messages Table -->
            <div class="flex flex-col">
              <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                  <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                      <thead class="bg-gray-50">
                        <tr>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                          </th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Sender
                          </th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Subject
                          </th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Message
                          </th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                          </th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                          </th>
                        </tr>
                      </thead>
                      <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($messages)): ?>
                          <?php foreach ($messages as $message): ?>
                            <tr class="<?php echo $message['status'] == 'read' ? '' : 'bg-blue-50'; ?>">
                              <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($message['status'] == 'read'): ?>
                                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-envelope-open mr-1"></i> Read
                                  </span>
                                <?php else: ?>
                                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-envelope mr-1"></i> New
                                  </span>
                                <?php endif; ?>
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($message['name']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($message['email']); ?></div>
                                <?php if (!empty($message['phone'])): ?>
                                  <div class="text-sm text-gray-500"><?php echo htmlspecialchars($message['phone']); ?></div>
                                <?php endif; ?>
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (!empty($message['subject'])): ?>
                                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <?php echo ucfirst(htmlspecialchars($message['subject'])); ?>
                                  </span>
                                <?php else: ?>
                                  <span class="text-gray-400">No subject</span>
                                <?php endif; ?>
                              </td>
                              <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 truncate max-w-xs">
                                  <?php echo htmlspecialchars(substr($message['message'], 0, 80)) . (strlen($message['message']) > 80 ? '...' : ''); ?>
                                </div>
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?>
                              </td>
                              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="contact.php?view=<?php echo $message['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                  <i class="fas fa-eye"></i> View
                                </a>
                                <a href="contact.php?toggle=<?php echo $message['id']; ?>" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                  <?php if ($message['status'] == 'read'): ?>
                                    <i class="fas fa-envelope"></i> Mark as Unread
                                  <?php else: ?>
                                    <i class="fas fa-envelope-open"></i> Mark as Read
                                  <?php endif; ?>
                                </a>
                                <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $message['id']; ?>)" class="text-red-600 hover:text-red-900">
                                  <i class="fas fa-trash"></i> Delete
                                </a>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        <?php else: ?>
                          <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                              No messages found.
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
                    <a href="?page=<?php echo $page - 1; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($subject_filter) ? '&subject=' . urlencode($subject_filter) : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
                      class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                      Previous
                    </a>
                  <?php endif; ?>
                  <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($subject_filter) ? '&subject=' . urlencode($subject_filter) : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
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
                      <span class="font-medium"><?php echo min($page * $items_per_page, $total_messages); ?></span>
                      of
                      <span class="font-medium"><?php echo $total_messages; ?></span>
                      results
                    </p>
                  </div>
                  <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                      <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($subject_filter) ? '&subject=' . urlencode($subject_filter) : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
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
                          <a href="?page=<?php echo $i; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($subject_filter) ? '&subject=' . urlencode($subject_filter) : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                            <?php echo $i; ?>
                          </a>
                        <?php endif; ?>
                      <?php endfor; ?>

                      <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo !empty($status_filter) ? '&status=' . urlencode($status_filter) : ''; ?><?php echo !empty($subject_filter) ? '&subject=' . urlencode($subject_filter) : ''; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>"
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

  <!-- View Message Modal -->
  <?php if ($message_details): ?>
    <div id="viewModal" class="fixed z-10 inset-0 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
          <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                <i class="fas fa-envelope-open text-blue-600"></i>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                  Message Details
                </h3>
                <div class="mt-4 border-t border-gray-200">
                  <dl class="divide-y divide-gray-200">
                    <div class="py-3 flex justify-between">
                      <dt class="text-sm font-medium text-gray-500">From</dt>
                      <dd class="text-sm text-gray-900"><?php echo htmlspecialchars($message_details['name']); ?></dd>
                    </div>
                    <div class="py-3 flex justify-between">
                      <dt class="text-sm font-medium text-gray-500">Email</dt>
                      <dd class="text-sm text-gray-900">
                        <a href="mailto:<?php echo htmlspecialchars($message_details['email']); ?>" class="text-blue-600 hover:text-blue-900">
                          <?php echo htmlspecialchars($message_details['email']); ?>
                        </a>
                      </dd>
                    </div>
                    <?php if (!empty($message_details['phone'])): ?>
                      <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                        <dd class="text-sm text-gray-900">
                          <a href="tel:<?php echo htmlspecialchars($message_details['phone']); ?>" class="text-blue-600 hover:text-blue-900">
                            <?php echo htmlspecialchars($message_details['phone']); ?>
                          </a>
                        </dd>
                      </div>
                    <?php endif; ?>
                    <div class="py-3 flex justify-between">
                      <dt class="text-sm font-medium text-gray-500">Subject</dt>
                      <dd class="text-sm text-gray-900">
                        <?php echo !empty($message_details['subject']) ? ucfirst(htmlspecialchars($message_details['subject'])) : '<span class="text-gray-400">No subject</span>'; ?>
                      </dd>
                    </div>
                    <div class="py-3 flex justify-between">
                      <dt class="text-sm font-medium text-gray-500">Date</dt>
                      <dd class="text-sm text-gray-900">
                        <?php echo date('F d, Y H:i:s', strtotime($message_details['created_at'])); ?>
                      </dd>
                    </div>
                    <div class="py-3">
                      <dt class="text-sm font-medium text-gray-500">Message</dt>
                      <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">
                        <?php echo htmlspecialchars($message_details['message']); ?>
                      </dd>
                    </div>
                  </dl>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <a href="contact.php" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
              Close
            </a>
            <a href="contact.php?toggle=<?php echo $message_details['id']; ?>" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
              <?php echo $message_details['status'] == 'read' ? 'Mark as Unread' : 'Mark as Read'; ?>
            </a>
            <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $message_details['id']; ?>)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-red-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
              Delete
            </a>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Delete Confirmation Modal -->
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
                Delete Message
              </h3>
              <div class="mt-2">
                <p class="text-sm text-gray-500">
                  Are you sure you want to delete this message? This action cannot be undone.
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <a href="#" id="confirmDeleteBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
            Delete
          </a>
          <button type="button" onclick="closeDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Handle view modal (if already opened by GET parameter)
    <?php if ($message_details): ?>
      document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('viewModal').classList.remove('hidden');
      });
    <?php endif; ?>

    // Delete confirmation
    function confirmDelete(id) {
      const modal = document.getElementById('deleteModal');
      const confirmBtn = document.getElementById('confirmDeleteBtn');

      // Set the delete URL
      confirmBtn.setAttribute('href', `contact.php?delete=${id}`);

      // Show the modal
      modal.classList.remove('hidden');
    }

    function closeDeleteModal() {
      const modal = document.getElementById('deleteModal');
      modal.classList.add('hidden');
    }

    // Close alerts after 5 seconds
    setTimeout(function() {
      const alerts = document.querySelectorAll('[role="alert"]');
      alerts.forEach(function(alert) {
        alert.style.display = 'none';
      });
    }, 5000);
  </script>

</body>

</html>