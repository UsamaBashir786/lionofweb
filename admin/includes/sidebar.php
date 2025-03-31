<?php
// Get current page to highlight active menu item
$current_page = basename($_SERVER['SCRIPT_NAME']);
$current_directory = basename(dirname($_SERVER['SCRIPT_NAME']));
?>

<div class="hidden md:flex md:flex-shrink-0">
  <div class="flex flex-col w-64 bg-white border-r">
    <div class="flex items-center justify-center h-16 px-4 bg-blue-600">
      <span class="text-xl font-semibold text-white">NewsHub Admin</span>
    </div>
    <div class="flex flex-col flex-1 overflow-y-auto">
      <nav class="flex-1 px-2 py-4 space-y-1">
        <a href="<?php echo SITE_URL; ?>/admin/dashboard.php"
          class="flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-100 hover:text-gray-900
                  <?php echo ($current_page == 'dashboard.php') ? 'text-blue-600 sidebar-active' : 'text-gray-600'; ?>">
          <i class="fas fa-tachometer-alt mr-3 <?php echo ($current_page == 'dashboard.php') ? 'text-blue-500' : 'text-gray-500'; ?>"></i>
          Dashboard
        </a>

        <div class="space-y-1">
          <p class="px-2 pt-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">News Management</p>
          <a href="<?php echo SITE_URL; ?>/admin/news/add-news.php"
            class="flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-100 hover:text-gray-900
                    <?php echo ($current_page == 'add-news.php' && $current_directory == 'news') ? 'text-blue-600 sidebar-active' : 'text-gray-600'; ?>">
            <i class="fas fa-newspaper mr-3 <?php echo ($current_page == 'add-news.php' && $current_directory == 'news') ? 'text-blue-500' : 'text-gray-500'; ?>"></i>
            Add New Article
          </a>
          <a href="<?php echo SITE_URL; ?>/admin/news/manage-news.php"
            class="flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-100 hover:text-gray-900
                    <?php echo ($current_page == 'manage-news.php' && $current_directory == 'news') ? 'text-blue-600 sidebar-active' : 'text-gray-600'; ?>">
            <i class="fas fa-list mr-3 <?php echo ($current_page == 'manage-news.php' && $current_directory == 'news') ? 'text-blue-500' : 'text-gray-500'; ?>"></i>
            Manage Articles
          </a>
        </div>

        <div class="space-y-1">
          <p class="px-2 pt-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Categories</p>
          <a href="<?php echo SITE_URL; ?>/admin/categories/add-category.php"
            class="flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-100 hover:text-gray-900
                    <?php echo ($current_page == 'add-category.php' && $current_directory == 'categories') ? 'text-blue-600 sidebar-active' : 'text-gray-600'; ?>">
            <i class="fas fa-folder-plus mr-3 <?php echo ($current_page == 'add-category.php' && $current_directory == 'categories') ? 'text-blue-500' : 'text-gray-500'; ?>"></i>
            Add Category
          </a>
          <a href="<?php echo SITE_URL; ?>/admin/categories/manage-categories.php"
            class="flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-100 hover:text-gray-900
                    <?php echo ($current_page == 'manage-categories.php' && $current_directory == 'categories') ? 'text-blue-600 sidebar-active' : 'text-gray-600'; ?>">
            <i class="fas fa-folder-open mr-3 <?php echo ($current_page == 'manage-categories.php' && $current_directory == 'categories') ? 'text-blue-500' : 'text-gray-500'; ?>"></i>
            Manage Categories
          </a>
        </div>

        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
          <div class="space-y-1">
            <p class="px-2 pt-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">User Management</p>
            <a href="<?php echo SITE_URL; ?>/admin/users/add-user.php"
              class="flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-100 hover:text-gray-900
                    <?php echo ($current_page == 'add-user.php' && $current_directory == 'users') ? 'text-blue-600 sidebar-active' : 'text-gray-600'; ?>">
              <i class="fas fa-user-plus mr-3 <?php echo ($current_page == 'add-user.php' && $current_directory == 'users') ? 'text-blue-500' : 'text-gray-500'; ?>"></i>
              Add User
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/users/manage-users.php"
              class="flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-100 hover:text-gray-900
                    <?php echo ($current_page == 'manage-users.php' && $current_directory == 'users') ? 'text-blue-600 sidebar-active' : 'text-gray-600'; ?>">
              <i class="fas fa-users mr-3 <?php echo ($current_page == 'manage-users.php' && $current_directory == 'users') ? 'text-blue-500' : 'text-gray-500'; ?>"></i>
              Manage Users
            </a>
          </div>
        <?php endif; ?>
        <div class="space-y-1">
          <p class="px-2 pt-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Featured Content</p>
          <a href="<?php echo SITE_URL; ?>/admin/trending.php"
            class="flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-100 hover:text-gray-900
            <?php echo ($current_page == 'trending.php') ? 'text-blue-600 sidebar-active' : 'text-gray-600'; ?>">
            <i class="fas fa-fire mr-3 <?php echo ($current_page == 'trending.php') ? 'text-blue-500' : 'text-gray-500'; ?>"></i>
            Manage Trending
          </a>
        </div>

        <div class="space-y-1">
          <p class="px-2 pt-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Settings</p>
          <a href="<?php echo SITE_URL; ?>/admin/settings.php"
            class="flex items-center px-2 py-2 text-sm font-medium rounded-md hover:bg-gray-100 hover:text-gray-900
                    <?php echo ($current_page == 'settings.php') ? 'text-blue-600 sidebar-active' : 'text-gray-600'; ?>">
            <i class="fas fa-cog mr-3 <?php echo ($current_page == 'settings.php') ? 'text-blue-500' : 'text-gray-500'; ?>"></i>
            General Settings
          </a>
        </div>

        <div class="pt-4">
          <a href="<?php echo SITE_URL; ?>" target="_blank"
            class="flex items-center px-2 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-100 hover:text-gray-900">
            <i class="fas fa-external-link-alt mr-3 text-gray-500"></i>
            View Website
          </a>
          <a href="<?php echo SITE_URL; ?>/admin/logout.php"
            class="flex items-center px-2 py-2 text-sm font-medium text-red-600 rounded-md hover:bg-red-50">
            <i class="fas fa-sign-out-alt mr-3 text-red-500"></i>
            Logout
          </a>
        </div>
      </nav>
    </div>
  </div>
</div>