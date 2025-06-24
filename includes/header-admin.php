<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Fixed 3-Dot Dropdown</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- ✅ Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100">

<!-- ✅ Header -->
<header class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white px-6 py-4 shadow-md">
  <div class="flex items-center justify-between relative">
    <h1 class="text-xl font-bold"> Admin Dashboard</h1>

    <!-- 3-dot icon (mobile) -->
    <div class="md:hidden relative">
      <button id="menu-toggle" class="focus:outline-none">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
          <path d="M10 3a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm0 5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm0 5a1.5 1.5 0 110 3 1.5 1.5 0 010-3z"/>
        </svg>
      </button>

      <!-- ✅ Dropdown Menu -->
      <div id="mobile-menu" class="hidden absolute right-0 mt-2 w-56 bg-white text-black rounded shadow-lg z-50">
        <a href="/admin/index.php" class="block px-4 py-2 hover:bg-gray-100">Dashboard</a>
        <a href="/admin/manage-seats.php" class="block px-4 py-2 hover:bg-gray-100">Manage Seats</a>
        <a href="/admin/bookings.php" class="block px-4 py-2 hover:bg-gray-100">Bookings</a>
        <a href="/admin/payments.php" class="block px-4 py-2 hover:bg-gray-100">Payments</a>
        <a href="/admin/messages.php" class="block px-4 py-2 hover:bg-gray-100">Messages</a>
        <a href="/admin/print-all-invoices.php" class="block px-4 py-2 hover:bg-gray-100">Invoice</a>
        <a href="/logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-100">Logout</a>
      </div>
    </div>

    <!-- Desktop Nav -->
     <nav class="hidden md:flex gap-4 items-center text-sm font-medium">
      <a href="/admin/index.php" class="hover:underline">Dashboard</a>
      <a href="/admin/manage-seats.php" class="hover:underline">Manage Seats</a>
      <a href="/admin/bookings.php" class="hover:underline">Bookings</a>
      <a href="/admin/payments.php" class="hover:underline">Payments</a>
      <a href="/admin/messages.php" class="hover:underline">Messages</a>
      <a href="/admin/print-all-invoices.php" class="hover:underline">Invoice</a>
      <?php include 'bell-icon.php'; ?>
      <a href="../admin/logout.php" class="text-red-300 hover:text-red-400 font-semibold">Logout</a>
    </nav>
  </div>
</header>

<!-- ✅ Toggle Script -->
<script>
  const toggleBtn = document.getElementById('menu-toggle');
  const menu = document.getElementById('mobile-menu');

  toggleBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    menu.classList.toggle('hidden');
  });

  // Click outside to close dropdown
  document.addEventListener('click', function(e) {
    if (!menu.contains(e.target) && !toggleBtn.contains(e.target)) {
      menu.classList.add('hidden');
    }
  });
</script>

