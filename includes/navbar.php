<?php
session_start();
?>
<!-- Accessibility skip link -->
<a href="#main-content" class="skip-link">Skip to content</a>

<!-- Top Bar -->
<div class="bg-gray-800 text-white py-2">
  <div class="container mx-auto px-4">
    <div class="flex justify-between items-center flex-wrap">
      <div class="flex items-center space-x-4 text-sm">
        <div>
          <i class="fas fa-calendar-alt mr-1"></i>
          <span id="current-date">Tuesday, March 11, 2025</span>
        </div>
        <div>
          <i class="fas fa-map-marker-alt mr-1"></i>
          <span>Local News</span>
        </div>
      </div>
      <div class="flex items-center space-x-4" style="z-index: 9999;">
        <div class="hidden sm:flex space-x-3 text-sm">
          <?php if (isset($_SESSION['user_id'])): ?>
            <!-- User is logged in, show user info/dropdown -->
            <div class="user-dropdown">
              <button class="flex items-center hover:text-blue-300 transition">
                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <i class="fas fa-chevron-down ml-1 text-xs"></i>
              </button>
              <div class="user-dropdown-menu">
                <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'editor')): ?>
                  <a href="admin/dashboard.php" class="user-dropdown-item">Dashboard</a>
                <?php endif; ?>
                <a href="profile.php" class="user-dropdown-item">My Profile</a>
                <a href="my-articles.php" class="user-dropdown-item">My Articles</a>
                <div class="user-dropdown-divider"></div>
                <a href="logout.php" class="user-dropdown-item">Logout</a>
              </div>
            </div>
          <?php else: ?>
            <!-- User is not logged in, show login/register links -->
            <a href="login.php" class="hover:text-blue-300 transition">Sign In</a>
            <span>|</span>
            <a href="register.php" class="hover:text-blue-300 transition">Register</a>
          <?php endif; ?>
        </div>
        <div class="flex space-x-3">
          <a href="#" class="text-gray-300 hover:text-white transition">
            <i class="fab fa-facebook-f"></i>
          </a>
          <a href="#" class="text-gray-300 hover:text-white transition">
            <i class="fab fa-twitter"></i>
          </a>
          <a href="#" class="text-gray-300 hover:text-white transition">
            <i class="fab fa-instagram"></i>
          </a>
          <a href="#" class="text-gray-300 hover:text-white transition">
            <i class="fab fa-youtube"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Main Navigation -->
<header class="bg-white shadow-md sticky top-0 z-50">
  <div class="container mx-auto px-4">
    <nav class="flex justify-between items-center py-4">
      <!-- Logo -->
      <div class="flex-shrink-0">
        <a href="index.php" class="flex items-center">
          <div class="text-white text-2xl font-bold p-2 rounded-lg mr-2">
            <!-- <i class="fas fa-newspaper"></i> -->
            <img src="assets/images/logo.webp" alt="" class="w-10 h-10 rounded-full">
          </div>
          <span class="text-2xl font-bold text-gray-800">Lion Of<span class="text-blue-600">Web</span></span>
        </a>
      </div>

      <!-- Search bar for desktop -->
      <div class="hidden md:block w-1/3">
        <form id="search-form" action="search.php" method="get" class="relative">
          <input
            type="text"
            id="search-input"
            name="query"
            placeholder="Search for news, articles..."
            class="w-full py-2 pl-4 pr-10 rounded-full border border-gray-300 focus:outline-none focus:border-blue-500">
          <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-blue-600">
            <i class="fas fa-search"></i>
          </button>
        </form>
      </div>

      <!-- Desktop Navigation Menu -->
      <div class="hidden lg:flex items-center space-x-1">
        <a href="index.php" class="px-3 py-2 rounded hover:bg-gray-100 font-medium text-gray-800 active-link">Home</a>

        <!-- Dropdown: News -->
        <div class="dropdown">
          <button class="dropdown-trigger px-3 py-2 rounded hover:bg-gray-100 font-medium text-gray-800 flex items-center">
            News <i class="fas fa-chevron-down ml-1 text-xs"></i>
          </button>
          <div class="dropdown-menu w-48">
            <div class="py-1">
              <a href="news-english.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">English News</a>
              <a href="news-urdu.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Urdu News</a>
            </div>
          </div>
        </div>

        <!-- Improved Categories Dropdown -->
        <div class="dropdown">
          <button class="dropdown-trigger px-3 py-2 rounded hover:bg-gray-100 font-medium text-gray-800 flex items-center">
            Categories <i class="fas fa-chevron-down ml-1 text-xs"></i>
          </button>
          <div class="dropdown-menu w-max" style="width: 650px;">
            <div class="grid grid-cols-3 gap-2 p-4">
              <!-- Business & Finance Column -->
              <div class="px-2">
                <h4 class="font-semibold text-blue-600 mb-2 px-2">Business & Finance</h4>
                <a href="business.php" class="block px-2 py-1.5 text-gray-800 hover:bg-gray-100 rounded">Business</a>
                <a href="crypto.php" class="block px-2 py-1.5 text-gray-800 hover:bg-gray-100 rounded">Cryptocurrency</a>
                <a href="economy.php" class="block px-2 py-1.5 text-gray-800 hover:bg-gray-100 rounded">Economy</a>
                <a href="finance.php" class="block px-2 py-1.5 text-gray-800 hover:bg-gray-100 rounded">Personal Finance</a>
                <a href="jobs.php" class="block px-2 py-1.5 text-gray-800 hover:bg-gray-100 rounded">Jobs</a>
              </div>

              <!-- Lifestyle Column -->
              <div class="px-2">
                <h4 class="font-semibold text-blue-600 mb-2 px-2">Lifestyle</h4>
                <a href="health.php" class="block px-2 py-1.5 text-gray-800 hover:bg-gray-100 rounded">Health</a>
                <a href="women.php" class="block px-2 py-1.5 text-gray-800 hover:bg-gray-100 rounded">Women</a>
                <a href="food.php" class="block px-2 py-1.5 text-gray-800 hover:bg-gray-100 rounded">Food</a>
                <a href="recipes.php" class="block px-2 py-1.5 text-gray-800 hover:bg-gray-100 rounded">Recipes</a>
                <a href="travel.php" class="block px-2 py-1.5 text-gray-800 hover:bg-gray-100 rounded">Travel</a>
              </div>

              <!-- Tech & More Column -->
              <div class="px-2">
                <h4 class="font-semibold text-blue-600 mb-2 px-2">Tech & More</h4>
                <a href="mobile.php" class="block px-2 py-1.5 text-gray-800 hover:bg-gray-100 rounded">Mobile</a>
                <a href="tech.php" class="block px-2 py-1.5 text-gray-800 hover:bg-gray-100 rounded">Technology</a>
                <a href="education.php" class="block px-2 py-1.5 text-gray-800 hover:bg-gray-100 rounded">Education</a>
                <a href="sports.php" class="block px-2 py-1.5 text-gray-800 hover:bg-gray-100 rounded">Sports</a>
                <a href="autos.php" class="block px-2 py-1.5 text-gray-800 hover:bg-gray-100 rounded">Autos</a>
              </div>
            </div>
            <div class="border-t border-gray-200 pt-2 pb-2 px-4">
              <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                View All Categories <i class="fas fa-arrow-right text-xs ml-1"></i>
              </a>
            </div>
          </div>
        </div>

        <a href="currency.php" class="px-3 py-2 rounded hover:bg-gray-100 font-medium text-gray-800">Currency</a>
        <a href="recipes.php" class="px-3 py-2 rounded hover:bg-gray-100 font-medium text-gray-800">Recipes</a>
        <a href="contact.php" class="px-3 py-2 rounded hover:bg-gray-100 font-medium text-gray-800">Contact</a>
      </div>

      <!-- Mobile Menu Button -->
      <div class="flex items-center lg:hidden space-x-4">
        <button id="search-mobile-button" class="text-gray-700 hover:text-blue-600 focus:outline-none">
          <i class="fas fa-search text-xl"></i>
        </button>
        <button id="mobile-menu-button" class="text-gray-700 hover:text-blue-600 focus:outline-none">
          <i class="fas fa-bars text-xl"></i>
        </button>
      </div>
    </nav>
  </div>
</header>

<!-- Mobile Search (Hidden by default) -->
<div id="mobile-search" class="bg-white py-3 px-4 shadow-md hidden">
  <form action="search.php" method="get">
    <div class="relative">
      <input
        type="text"
        name="query"
        placeholder="Search for news, articles..."
        class="w-full py-2 pl-4 pr-10 rounded-full border border-gray-300 focus:outline-none focus:border-blue-500">
      <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-blue-600">
        <i class="fas fa-search"></i>
      </button>
    </div>
  </form>
</div>

<!-- Mobile Menu (Off-canvas) -->
<div id="mobile-menu" class="fixed top-0 left-0 bottom-0 w-4/5 max-w-xs bg-white shadow-xl z-50 mobile-menu overflow-y-auto">
  <div class="p-4 border-b border-gray-200">
    <div class="flex justify-between items-center">
      <a href="index.php" class="flex items-center">
        <div class="bg-blue-600 text-white text-xl font-bold p-1 rounded-lg mr-2">
          <i class="fas fa-newspaper"></i>
        </div>
        <span class="text-xl font-bold text-gray-800">News<span class="text-blue-600">Hub</span></span>
      </a>
      <button id="close-mobile-menu" class="text-gray-700 hover:text-red-600 focus:outline-none">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>
  </div>

  <nav class="p-4">
    <ul>
      <li class="mb-1">
        <a href="index.php" class="block px-3 py-2 rounded font-medium text-gray-800 bg-gray-100">Home</a>
      </li>
      <li class="mb-1">
        <div class="mobile-dropdown">
          <button class="mobile-dropdown-trigger w-full text-left flex justify-between items-center px-3 py-2 rounded font-medium text-gray-800 hover:bg-gray-100">
            News
            <i class="fas fa-chevron-down text-xs"></i>
          </button>
          <div class="mobile-dropdown-menu hidden pl-4 mt-1">
            <a href="news-english.php" class="block px-3 py-2 rounded text-gray-800 hover:bg-gray-100">English News</a>
            <a href="news-urdu.php" class="block px-3 py-2 rounded text-gray-800 hover:bg-gray-100">Urdu News</a>
          </div>
        </div>
      </li>
      <li class="mb-1">
        <div class="mobile-dropdown">
          <button class="mobile-dropdown-trigger w-full text-left flex justify-between items-center px-3 py-2 rounded font-medium text-gray-800 hover:bg-gray-100">
            Categories
            <i class="fas fa-chevron-down text-xs"></i>
          </button>
          <div class="mobile-dropdown-menu hidden pl-4 mt-1">
            <a href="business.php" class="block px-3 py-2 rounded text-gray-800 hover:bg-gray-100">Business</a>
            <a href="crypto.php" class="block px-3 py-2 rounded text-gray-800 hover:bg-gray-100">Crypto</a>
            <a href="sports.php" class="block px-3 py-2 rounded text-gray-800 hover:bg-gray-100">Sports</a>
            <a href="health.php" class="block px-3 py-2 rounded text-gray-800 hover:bg-gray-100">Health</a>
            <a href="mobile.php" class="block px-3 py-2 rounded text-gray-800 hover:bg-gray-100">Mobile</a>
            <a href="education.php" class="block px-3 py-2 rounded text-gray-800 hover:bg-gray-100">Education</a>
            <a href="islam.php" class="block px-3 py-2 rounded text-gray-800 hover:bg-gray-100">Islamic</a>
            <a href="women.php" class="block px-3 py-2 rounded text-gray-800 hover:bg-gray-100">Women</a>
          </div>
        </div>
      </li>
      <li class="mb-1">
        <a href="currency.php" class="block px-3 py-2 rounded font-medium text-gray-800 hover:bg-gray-100">Currency</a>
      </li>
      <li class="mb-1">
        <a href="recipes.php" class="block px-3 py-2 rounded font-medium text-gray-800 hover:bg-gray-100">Recipes</a>
      </li>
      <li class="mb-1">
        <a href="contact.php" class="block px-3 py-2 rounded font-medium text-gray-800 hover:bg-gray-100">Contact</a>
      </li>
    </ul>
  </nav>

  <div class="border-t border-gray-200 p-4">
    <div class="flex flex-col space-y-3">
      <?php if (isset($_SESSION['user_id'])): ?>
        <!-- User is logged in, show personalized options -->
        <div class="flex items-center px-3 py-2 mb-2">
          <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
            <?php if (!empty($_SESSION['user_avatar'])): ?>
              <img class="w-10 h-10 rounded-full object-cover" src="<?php echo $_SESSION['user_avatar']; ?>" alt="<?php echo htmlspecialchars($_SESSION['user_name']); ?>">
            <?php else: ?>
              <i class="fas fa-user text-blue-500"></i>
            <?php endif; ?>
          </div>
          <div>
            <div class="font-medium"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
            <div class="text-sm text-gray-500"><?php echo ucfirst($_SESSION['user_role'] ?? 'User'); ?></div>
          </div>
        </div>

        <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] == 'admin' || $_SESSION['user_role'] == 'editor')): ?>
          <a href="admin/dashboard.php" class="flex items-center px-3 py-2 rounded font-medium text-gray-800 hover:bg-gray-100">
            <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
          </a>
        <?php endif; ?>

        <a href="profile.php" class="flex items-center px-3 py-2 rounded font-medium text-gray-800 hover:bg-gray-100">
          <i class="fas fa-user-circle mr-2"></i> My Profile
        </a>

        <a href="my-articles.php" class="flex items-center px-3 py-2 rounded font-medium text-gray-800 hover:bg-gray-100">
          <i class="fas fa-newspaper mr-2"></i> My Articles
        </a>

        <a href="logout.php" class="flex items-center px-3 py-2 rounded font-medium text-gray-800 hover:bg-gray-100">
          <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </a>
      <?php else: ?>
        <!-- User is not logged in, show login/register options -->
        <a href="login.php" class="flex items-center px-3 py-2 rounded font-medium text-gray-800 hover:bg-gray-100">
          <i class="fas fa-user-circle mr-2"></i> Sign In
        </a>
        <a href="register.php" class="flex items-center px-3 py-2 rounded font-medium text-gray-800 hover:bg-gray-100">
          <i class="fas fa-user-plus mr-2"></i> Register
        </a>
      <?php endif; ?>

      <div class="flex space-x-3 px-3 py-2">
        <a href="#" class="text-gray-600 hover:text-blue-600 transition">
          <i class="fab fa-facebook-f"></i>
        </a>
        <a href="#" class="text-gray-600 hover:text-blue-600 transition">
          <i class="fab fa-twitter"></i>
        </a>
        <a href="#" class="text-gray-600 hover:text-blue-600 transition">
          <i class="fab fa-instagram"></i>
        </a>
        <a href="#" class="text-gray-600 hover:text-blue-600 transition">
          <i class="fab fa-youtube"></i>
        </a>
      </div>
    </div>
  </div>
</div>
<style>
  /* Add your custom styles here */
  .mobile-menu {
    transform: translateX(-100%);
    transition: transform 0.3s ease-in-out;
  }

  .mobile-menu.active {
    transform: translateX(0);
  }

  .skip-link {
    position: absolute;
    left: -999px;
    width: 1px;
    height: 1px;
    overflow: hidden;
  }

  .skip-link:focus {
    left: 0;
    top: 0;
    width: auto;
    height: auto;
    padding: 10px;
    background: white;
    color: black;
    z-index: 1000;
  }

  /* Custom dropdown styling to prevent quick hiding */
  .user-dropdown {
    position: relative;
  }

  .user-dropdown-menu {
    position: absolute;
    right: 0;
    width: 200px;
    margin-top: 8px;
    background-color: white;
    border-radius: 6px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: opacity 0.2s ease, transform 0.2s ease, visibility 0s linear 0.2s;
    z-index: 50;
  }

  .user-dropdown:hover .user-dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
    transition: opacity 0.3s ease, transform 0.3s ease, visibility 0s linear 0s;
  }

  /* Add a invisible extender to prevent mouseout when moving to dropdown */
  .user-dropdown-menu::before {
    content: '';
    position: absolute;
    top: -16px;
    /* This creates an invisible bridge between trigger and menu */
    left: 0;
    right: 0;
    height: 16px;
    background-color: transparent;
  }

  /* Menu items styling */
  .user-dropdown-item {
    display: block;
    padding: 8px 16px;
    color: #4b5563;
    font-size: 0.875rem;
    transition: background-color 0.15s ease;
  }

  .user-dropdown-item:hover {
    background-color: #f3f4f6;
  }

  .user-dropdown-divider {
    height: 1px;
    margin: 4px 0;
    background-color: #e5e7eb;
  }

  /* Improved general dropdown styling for main navigation */
  .dropdown {
    position: relative;
  }

  .dropdown-menu {
    position: absolute;
    left: 0;
    margin-top: 8px;
    background-color: white;
    border-radius: 6px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: opacity 0.2s ease, transform 0.2s ease, visibility 0s linear 0.2s;
    z-index: 50;
  }

  .dropdown:hover .dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
    transition: opacity 0.3s ease, transform 0.3s ease, visibility 0s linear 0s;
  }

  /* Add a invisible extender to prevent mouseout when moving to dropdown */
  .dropdown-menu::before {
    content: '';
    position: absolute;
    top: -16px;
    left: 0;
    right: 0;
    height: 16px;
    background-color: transparent;
  }

  /* Mobile dropdown menu improvements */
  .mobile-user-section {
    padding: 16px;
    border-top: 1px solid #e5e7eb;
  }

  /* Mobile dropdown improvements */
  .mobile-dropdown-menu {
    transition: all 0.3s ease;
  }
</style>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Set current date
    const date = new Date();
    const options = {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    };

    const currentDateElement = document.getElementById('current-date');
    if (currentDateElement) {
      currentDateElement.textContent = date.toLocaleDateString('en-US', options);
    }

    // Mobile menu functionality
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    const closeMenuButton = document.getElementById('close-mobile-menu');

    if (mobileMenuButton && mobileMenu) {
      mobileMenuButton.addEventListener('click', function() {
        mobileMenu.classList.add('active');
        document.body.classList.add('overflow-hidden');
      });
    }

    if (closeMenuButton && mobileMenu) {
      closeMenuButton.addEventListener('click', function() {
        mobileMenu.classList.remove('active');
        document.body.classList.remove('overflow-hidden');
      });
    }

    // Mobile search functionality
    const searchMobileButton = document.getElementById('search-mobile-button');
    const mobileSearch = document.getElementById('mobile-search');

    if (searchMobileButton && mobileSearch) {
      searchMobileButton.addEventListener('click', function() {
        mobileSearch.classList.toggle('hidden');
      });
    }

    // Mobile dropdown functionality
    const mobileDropdownTriggers = document.querySelectorAll('.mobile-dropdown-trigger');

    mobileDropdownTriggers.forEach(trigger => {
      trigger.addEventListener('click', function() {
        const menu = this.nextElementSibling;
        const icon = this.querySelector('i');

        if (!menu || !icon) return;

        menu.classList.toggle('hidden');

        if (menu.classList.contains('hidden')) {
          icon.classList.remove('fa-chevron-up');
          icon.classList.add('fa-chevron-down');
        } else {
          icon.classList.remove('fa-chevron-down');
          icon.classList.add('fa-chevron-up');
        }
      });
    });

    // Handle window resize events to manage dropdown behavior
    window.addEventListener('resize', function() {
      const isDesktop = window.innerWidth >= 1024; // lg breakpoint

      // Reset any open dropdowns when resizing between mobile and desktop
      if (!isDesktop) {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
          if (!menu.classList.contains('hidden')) {
            menu.classList.add('hidden');
          }
        });
      }
    });
  });
</script>