  <?php
require_once '../includes/db.php';

// Mark all as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
  $conn->query("UPDATE table_notifications SET is_read = 1");
  header("Location: " . $_SERVER['PHP_SELF']);
  exit; 
 

// Get unread notifications
$notifyQuery = "SELECT * FROM table_notifications WHERE is_read = 0 ORDER BY created_at DESC LIMIT 10";
$notifyResult = $conn->query($notifyQuery);
$unreadCount = $notifyResult ? $notifyResult->num_rows : 0;
?>

<!-- 🔔 Notification Bell Icon with Dropdown -->
<div class="relative group inline-block text-left">
  <!-- Bell Button -->
  <button class="relative focus:outline-none">
    <!-- Bell Icon -->
    <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round"
            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
    </svg>

    <!-- Count Badge -->
    <?php if ($unreadCount > 0): ?>
      <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs rounded-full px-1.5 py-0.5">
        <?= $unreadCount ?>
      </span>
    <?php endif; ?>
  </button>

  <!-- Dropdown on Hover -->
  <div class="absolute right-0 z-50 w-80 mt-2 origin-top-right bg-white border border-gray-200 rounded-lg shadow-lg hidden group-hover:block">
    <div class="p-3">
      <h4 class="font-semibold text-gray-700 mb-2">🔔 Notifications</h4>
      <ul class="space-y-2 max-h-60 overflow-y-auto">
        <?php if ($unreadCount > 0): ?>
          <?php while ($row = $notifyResult->fetch_assoc()): ?>
            <li class="text-sm text-gray-800 border-b pb-2">
              <?= $row['message'] ?>
              <div class="text-xs text-gray-500"><?= date('d M Y, h:i A', strtotime($row['created_at'])) ?></div>
            </li>
          <?php endwhile; ?>
        <?php else: ?>
          <li class="text-sm text-gray-500">No new notifications</li>
        <?php endif; ?>
      </ul>
    </div>

    <!-- Mark as Read Button -->
    <form method="post" class="border-t p-2 text-center">
      <button name="mark_all_read" class="text-sm text-blue-600 hover:underline">✅ Mark all as read</button>
    </form>
  </div>
</div>

<!-- Optional CSS if Tailwind group-hover isn't working -->
<style>
.group:hover .group-hover\:block {
  display: block;
}
</style>




