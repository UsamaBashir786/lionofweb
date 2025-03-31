<?php
// File: includes/trending-section.php

// Get active trending items
$trending_items = [];
try {
  $stmt = $conn->prepare("SELECT * FROM trending_items WHERE active = 1 ORDER BY position ASC LIMIT 4");
  $stmt->execute();
  $trending_items = $stmt->fetchAll();
} catch (PDOException $e) {
  error_log("Error fetching trending items: " . $e->getMessage());
}
?>

<!-- Trending Section -->
<section class="mb-12">
  <h2 class="text-3xl font-bold text-gray-800 mb-6" data-aos="fade-up" data-aos-duration="800">Trending Now</h2>

  <div class="bg-white rounded-lg shadow-md p-6" data-aos="fade-up" data-aos-duration="1000">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php
      // Check if we have trending items
      if (!empty($trending_items)):
        $delays = [100, 200, 200, 100]; // Animation delays for each position
        $directions = ['right', 'right', 'left', 'left']; // Animation directions for each position
        $item_count = 0;

        foreach ($trending_items as $index => $item):
          // Only display the first 4 items
          if ($item_count >= 4) break;

          // Calculate animation delay and direction based on position
          $position = min(max($item['position'] - 1, 0), 3);
          $delay = $delays[$position];
          $direction = $directions[$position];

          // Apply border styles based on position (all except last have right border on md screens)
          $border_classes = 'border-b md:border-b-0';
          if ($item_count < 3) {
            $border_classes .= ' md:border-r';
          }
          $border_classes .= ' border-gray-200 pb-4 md:pb-0 md:pr-4';

          // Convert position to display format (01, 02, etc.)
          $display_position = str_pad($item['position'], 2, '0', STR_PAD_LEFT);

          // Determine the link URL
          $detail_link = !empty($item['link_url']) ? $item['link_url'] : "trending-detail.php?id={$item['id']}";
      ?>
          <!-- Trending Item -->
          <div class="<?php echo $border_classes; ?>" data-aos="fade-<?php echo $direction; ?>" data-aos-delay="<?php echo $delay; ?>" data-aos-duration="800">
            <span class="text-4xl font-bold text-blue-600"><?php echo $display_position; ?></span>
            <h3 class="font-bold text-gray-800 mt-2 mb-1"><?php echo htmlspecialchars($item['title']); ?></h3>
            <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($item['description']); ?></p>
            <a href="<?php echo htmlspecialchars($detail_link); ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2 inline-block transition duration-300">Read More</a>
          </div>
          <?php
          $item_count++;
        endforeach;

        // If we have less than 4 items, display placeholders
        if ($item_count < 4):
          for ($i = $item_count; $i < 4; $i++):
            $position = $i + 1;
            $display_position = str_pad($position, 2, '0', STR_PAD_LEFT);
            $delay = $delays[$i];
            $direction = $directions[$i];

            $border_classes = 'border-b md:border-b-0';
            if ($i < 3) {
              $border_classes .= ' md:border-r';
            }
            $border_classes .= ' border-gray-200 pb-4 md:pb-0 md:pr-4';
          ?>
            <!-- Placeholder Trending Item -->
            <div class="<?php echo $border_classes; ?>" data-aos="fade-<?php echo $direction; ?>" data-aos-delay="<?php echo $delay; ?>" data-aos-duration="800">
              <span class="text-4xl font-bold text-blue-600"><?php echo $display_position; ?></span>
              <h3 class="font-bold text-gray-800 mt-2 mb-1">Trending Item Coming Soon</h3>
              <p class="text-gray-600 text-sm">Stay tuned for more trending news and updates.</p>
              <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2 inline-block transition duration-300">Read More</a>
            </div>
        <?php
          endfor;
        endif;
      else:
        // Display default trending items if none are found in database
        ?>
        <!-- Default Trending Item 1 -->
        <div class="border-b md:border-b-0 md:border-r border-gray-200 pb-4 md:pb-0 md:pr-4" data-aos="fade-right" data-aos-delay="100" data-aos-duration="800">
          <span class="text-4xl font-bold text-blue-600">01</span>
          <h3 class="font-bold text-gray-800 mt-2 mb-1">Major Currency Fluctuations Impact Global Markets</h3>
          <p class="text-gray-600 text-sm">Financial analysts predict lasting effects on international trade.</p>
          <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2 inline-block transition duration-300">Read More</a>
        </div>

        <!-- Default Trending Item 2 -->
        <div class="border-b md:border-b-0 md:border-r border-gray-200 pb-4 md:pb-0 md:pr-4" data-aos="fade-right" data-aos-delay="200" data-aos-duration="800">
          <span class="text-4xl font-bold text-blue-600">02</span>
          <h3 class="font-bold text-gray-800 mt-2 mb-1">New Electric Vehicle Models Set to Transform Auto Industry</h3>
          <p class="text-gray-600 text-sm">Innovations in battery technology promise extended range capabilities.</p>
          <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2 inline-block transition duration-300">Read More</a>
        </div>

        <!-- Default Trending Item 3 -->
        <div class="border-b md:border-b-0 md:border-r border-gray-200 pb-4 md:pb-0 md:pr-4" data-aos="fade-left" data-aos-delay="200" data-aos-duration="800">
          <span class="text-4xl font-bold text-blue-600">03</span>
          <h3 class="font-bold text-gray-800 mt-2 mb-1">Nationwide Education Reform Initiative Announced</h3>
          <p class="text-gray-600 text-sm">Comprehensive changes aim to address learning gaps and improve outcomes.</p>
          <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2 inline-block transition duration-300">Read More</a>
        </div>

        <!-- Default Trending Item 4 -->
        <div data-aos="fade-left" data-aos-delay="100" data-aos-duration="800">
          <span class="text-4xl font-bold text-blue-600">04</span>
          <h3 class="font-bold text-gray-800 mt-2 mb-1">International Cricket Tournament Reaches Final Stage</h3>
          <p class="text-gray-600 text-sm">Top teams compete for championship title in sold-out stadium.</p>
          <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2 inline-block transition duration-300">Read More</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>