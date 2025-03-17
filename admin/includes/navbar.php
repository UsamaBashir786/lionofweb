<?php
// Get user data from session
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Admin User';
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'admin';
$user_avatar = isset($_SESSION['user_avatar']) ? $_SESSION['user_avatar'] : '';
?>

<div class="relative z-10 flex h-16 bg-white shadow">
  <button id="sidebar-toggle" class="px-4 border-r border-gray-200 text-gray-500 focus:outline-none focus:bg-gray-100 focus:text-gray-600 md:hidden">
    <i class="fas fa-bars"></i>
  </button>
  <div class="flex justify-between flex-1 px-4">
    <div class="flex flex-1">
      <div class="flex w-full md:ml-0">
        <div class="relative w-full text-gray-400 focus-within:text-gray-600">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3">
            <i class="fas fa-search"></i>
          </div>
          <form action="<?php echo SITE_URL; ?>/admin/search.php" method="GET">
            <input name="q" class="block w-full h-full py-2 pl-10 pr-3 text-gray-900 placeholder-gray-500 border-transparent focus:outline-none focus:placeholder-gray-400 focus:ring-0 focus:border-transparent sm:text-sm" placeholder="Search...">
          </form>
        </div>
      </div>
    </div>
    <div class="flex items-center ml-4 md:ml-6">
      <!-- Notification dropdown -->
      <div class="relative ml-3">
        <button id="notification-button" class="p-1 text-gray-400 bg-white rounded-full hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
          <span class="sr-only">Notifications</span>
          <i class="fas fa-bell"></i>
        </button>
        <!-- Dropdown menu -->
        <div id="notification-dropdown" class="absolute right-0 hidden w-80 py-1 mt-2 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5">
          <div class="px-4 py-2 border-b border-gray-200">
            <h3 class="text-sm font-medium text-gray-800">Notifications</h3>
          </div>
          <div class="max-h-60 overflow-y-auto">
            <!-- Example notifications, replace with dynamic content -->
            <a href="#" class="block px-4 py-3 hover:bg-gray-50">
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <i class="fas fa-user-edit text-blue-500"></i>
                </div>
                <div class="ml-3 w-0 flex-1">
                  <p class="text-sm font-medium text-gray-900">User profile updated</p>
                  <p class="text-sm text-gray-500">Editor Sarah updated her profile</p>
                  <p class="mt-1 text-xs text-gray-400">5 min ago</p>
                </div>
              </div>
            </a>
            <a href="#" class="block px-4 py-3 hover:bg-gray-50">
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <i class="fas fa-newspaper text-green-500"></i>
                </div>
                <div class="ml-3 w-0 flex-1">
                  <p class="text-sm font-medium text-gray-900">New article published</p>
                  <p class="text-sm text-gray-500">Global Economic Trends for 2025</p>
                  <p class="mt-1 text-xs text-gray-400">30 min ago</p>
                </div>
              </div>
            </a>
            <a href="#" class="block px-4 py-3 hover:bg-gray-50">
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <i class="fas fa-comment text-yellow-500"></i>
                </div>
                <div class="ml-3 w-0 flex-1">
                  <p class="text-sm font-medium text-gray-900">New comment requires approval</p>
                  <p class="text-sm text-gray-500">On "New Technology Breakthroughs"</p>
                  <p class="mt-1 text-xs text-gray-400">1 hour ago</p>
                </div>
              </div>
            </a>
          </div>
          <div class="px-4 py-2 border-t border-gray-200 text-center">
            <a href="#" class="text-sm font-medium text-blue-600 hover:text-blue-500">View all notifications</a>
          </div>
        </div>
      </div>

      <!-- Profile dropdown -->
      <div class="relative ml-3">
        <div>
          <button id="profile-button" class="flex items-center max-w-xs text-sm bg-white rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <?php if (!empty($user_avatar) && file_exists('../../' . $user_avatar)): ?>
              <img class="w-8 h-8 rounded-full" src="<?php echo SITE_URL . '/' . $user_avatar; ?>" alt="<?php echo htmlspecialchars($user_name); ?>">
            <?php else: ?>
              <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="fas fa-user text-blue-500"></i>
              </div>
            <?php endif; ?>
            <span class="ml-2 text-gray-700"><?php echo htmlspecialchars($user_name); ?></span>
            <i class="ml-1 fas fa-chevron-down text-gray-400"></i>
          </button>
        </div>
        <div id="profile-dropdown" class="absolute right-0 hidden w-48 py-1 mt-2 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5">
          <a href="<?php echo SITE_URL; ?>/admin/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a>
          <a href="<?php echo SITE_URL; ?>/admin/settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
          <div class="border-t border-gray-100"></div>
          <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign out</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // Toggle notifications dropdown
  document.getElementById('notification-button').addEventListener('click', function() {
    document.getElementById('notification-dropdown').classList.toggle('hidden');
    // Close profile dropdown if open
    document.getElementById('profile-dropdown').classList.add('hidden');
  });
  
  // Toggle profile dropdown
  document.getElementById('profile-button').addEventListener('click', function() {
    document.getElementById('profile-dropdown').classList.toggle('hidden');
    // Close notification dropdown if open
    document.getElementById('notification-dropdown').classList.add('hidden');
  });
  
  // Close dropdowns when clicking outside
  document.addEventListener('click', function(event) {
    const notificationButton = document.getElementById('notification-button');
    const notificationDropdown = document.getElementById('notification-dropdown');
    const profileButton = document.getElementById('profile-button');
    const profileDropdown = document.getElementById('profile-dropdown');
    
    if (notificationButton && notificationDropdown && !notificationButton.contains(event.target) && !notificationDropdown.contains(event.target)) {
      notificationDropdown.classList.add('hidden');
    }
    
    if (profileButton && profileDropdown && !profileButton.contains(event.target) && !profileDropdown.contains(event.target)) {
      profileDropdown.classList.add('hidden');
    }
  });

  // Mobile sidebar toggle
  const sidebarToggle = document.getElementById('sidebar-toggle');
  if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
      const sidebar = document.querySelector('.hidden.md\\:flex.md\\:flex-shrink-0');
      if (sidebar) {
        sidebar.classList.toggle('hidden');
        sidebar.classList.toggle('fixed');
        sidebar.classList.toggle('inset-0');
        sidebar.classList.toggle('z-40');
        sidebar.classList.toggle('md:hidden');
        
        // Add close button if it doesn't exist
        if (!document.getElementById('sidebar-close-btn')) {
          const closeBtn = document.createElement('button');
          closeBtn.className = 'absolute top-4 right-4 text-white bg-blue-600 rounded-full p-2';
          closeBtn.innerHTML = '<i class="fas fa-times"></i>';
          closeBtn.id = 'sidebar-close-btn';
          
          closeBtn.addEventListener('click', function() {
            sidebar.classList.add('hidden');
            sidebar.classList.remove('fixed', 'inset-0', 'z-40', 'md:hidden');
            this.remove();
          });
          
          const sidebarHeader = sidebar.querySelector('.flex.items-center.justify-center.h-16.px-4.bg-blue-600');
          if (sidebarHeader) {
            sidebarHeader.appendChild(closeBtn);
          }
        }
      }
    });
  }
</script>