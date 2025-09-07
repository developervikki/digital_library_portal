<?php 
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin(); 
 
// Fetch stats 
$totalSeatsResult = $conn->query("SELECT COUNT(*) AS total FROM table_seats WHERE is_active = 1"); 
$totalSeats = $totalSeatsResult->fetch_assoc()['total'] ?? 0;

$today = date('Y-m-d');
$todaysBookingsResult = $conn->query("SELECT COUNT(*) AS count FROM table_bookings WHERE DATE(created_at) = '$today'");
$todaysBookings = $todaysBookingsResult->fetch_assoc()['count'] ?? 0;

$totalPaymentsResult = $conn->query("SELECT SUM(amount) AS total FROM table_payments WHERE status = 'paid'");
$totalPayments = $totalPaymentsResult->fetch_assoc()['total'] ?? 0;

// For Payments by Month Chart (Last 6 months)
$paymentsChartData = [];
$paymentsChartLabels = [];
for ($i = 5; $i >= 0; $i--) {
    $monthLabel = date('M Y', strtotime("-$i month"));
    $monthKey = date('Y-m', strtotime("-$i month"));
    $paymentsChartLabels[] = $monthLabel;

    $stmt = $conn->prepare("SELECT IFNULL(SUM(amount),0) AS total FROM table_payments WHERE status = 'paid' AND DATE_FORMAT(payment_date, '%Y-%m') = ?");
    $stmt->bind_param('s', $monthKey);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $paymentsChartData[] = (float)$res['total'];
    $stmt->close();
}
?>
<?php
// ... PHP part remains unchanged
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard - Digital Library</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

  <style>
    /* Particle background fills screen */
    #particles-js {
      position: fixed;
      width: 100%;
      height: 100%;
      z-index: -1;
      top: 0; left: 0;
      background: linear-gradient(135deg, #a78bfa, #6366f1);
    }

    /* Scrollbar for sidebar if overflow */
    aside::-webkit-scrollbar {
      width: 6px; 
    }
    aside::-webkit-scrollbar-thumb {
      background: #7c3aed;
      border-radius: 3px;
    }
  </style>
</head>
<body class="bg-gradient-to-br from-purple-200 via-indigo-200 to-blue-100 min-h-screen font-sans relative">

  <div id="particles-js"></div>

  <div class="flex min-h-screen">
    <aside class="w-64 h-screen bg-gradient-to-b from-indigo-900 via-purple-900 to-indigo-900 text-white flex flex-col shadow-lg overflow-y-auto">
      <div class="px-6 py-5 text-3xl font-bold border-b border-purple-700 select-none">
        📚 Admin Panel
      </div>
      <nav class="flex-1 px-4 py-6 space-y-4 text-lg">
        <a href="../admin/dashboard.php" class="block px-4 py-2 rounded hover:bg-purple-700 transition">🏠 Dashboard</a>
        <a href="manage-seats.php" class="block px-4 py-2 rounded hover:bg-purple-700 transition">🪑 Manage Seats</a>
        <a href="../admin/bookings.php" class="block px-4 py-2 rounded hover:bg-purple-700 transition">📖 View Bookings</a>
        <a href="../admin/payments.php" class="block px-4 py-2 rounded hover:bg-purple-700 transition">💳 Payments</a>
        <a href="../admin/messages.php" class="block px-4 py-2 rounded hover:bg-purple-700 transition">✉️ Messages</a>
        <form action="logout.php" method="POST">
          <button type="submit" class="w-full text-left px-4 py-2 mt-8 bg-gradient-to-r from-purple-700 to-indigo-700 rounded hover:from-purple-800 hover:to-indigo-800 transition">Logout</button>
        </form>
      </nav>
    </aside>

    <main class="flex-1 p-10">
      <h1 class="text-4xl font-bold text-indigo-900 mb-8">Welcome, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?> 👋</h1>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <div class="bg-white p-8 rounded-lg shadow-lg border-l-8 border-indigo-500">
          <h2 class="text-xl font-semibold mb-2 text-indigo-700">Total Seats</h2>
          <p class="text-5xl font-extrabold text-indigo-600"><?= $totalSeats ?></p>
          <p class="text-sm text-gray-600 mt-1">Active seats available</p>
        </div>

        <div class="bg-white p-8 rounded-lg shadow-lg border-l-8 border-purple-500">
          <h2 class="text-xl font-semibold mb-2 text-purple-700">Today's Bookings</h2>
          <p class="text-5xl font-extrabold text-purple-600"><?= $todaysBookings ?></p>
          <p class="text-sm text-gray-600 mt-1">New bookings today</p>
        </div>

        <div class="bg-white p-8 rounded-lg shadow-lg border-l-8 border-indigo-700">
          <h2 class="text-xl font-semibold mb-2 text-indigo-800">Total Payments</h2>
          <p class="text-5xl font-extrabold text-indigo-700">₹<?= number_format($totalPayments, 2) ?></p>
          <p class="text-sm text-gray-600 mt-1">Total paid amount received</p>
        </div>
      </div>

      <section class="mt-16 bg-white p-8 rounded-lg shadow-lg">
        <h2 class="text-3xl font-semibold mb-6 text-indigo-900">Payments Received (Last 6 Months)</h2>
        <canvas id="paymentsChart" height="120"></canvas>
      </section>

      <section class="mt-16 bg-white p-8 rounded-lg shadow-lg">
        <h2 class="text-3xl font-semibold mb-6 text-indigo-900">Quick Actions</h2>
        <div class="flex flex-wrap gap-6">
          <a href="../admin/manage-seats.php" class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-3 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition shadow">Add/Update Seats</a>
          <a href="../admin/bookings.php" class="bg-gradient-to-r from-green-600 to-green-700 text-white px-6 py-3 rounded-lg hover:from-green-700 hover:to-green-800 transition shadow">View All Bookings</a>
          <a href="../admin/payments.php" class="bg-gradient-to-r from-yellow-400 to-yellow-500 text-white px-6 py-3 rounded-lg hover:from-yellow-500 hover:to-yellow-600 transition shadow">Manage Payments</a>
          <a href="../admin/messages.php" class="bg-gradient-to-r from-pink-500 to-pink-600 text-white px-6 py-3 rounded-lg hover:from-pink-600 hover:to-pink-700 transition shadow">Check Messages</a>
        </div>
      </section>
    </main>
  </div>

  <script>
    // Particles.js with brighter visible particles
    particlesJS("particles-js", {
      particles: {
        number: {
          value: 80,
          density: { enable: true, value_area: 900 }
        },
        color: { value: "#7C3AED" },
        shape: { type: "circle" },
        opacity: {
          value: 0.3,
          random: false
        },
        size: {
          value: 5,
          random: true
        },
        move: {
          enable: true,
          speed: 3,
          direction: "none",
          random: true,
          straight: false,
          bounce: false
        }
      },
      interactivity: {
        detect_on: "canvas",
        events: {
          onhover: { enable: true, mode: "repulse" },
          onclick: { enable: true, mode: "push" }
        },
        modes: {
          repulse: { distance: 100, duration: 0.4 },
          push: { particles_nb: 4 }
        }
      },
      retina_detect: true
    });

    // Chart.js Payments Chart
    const ctx = document.getElementById('paymentsChart').getContext('2d');
    const paymentsChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: <?= json_encode($paymentsChartLabels) ?>,
        datasets: [{
          label: 'Payments Received (₹)',
          data: <?= json_encode($paymentsChartData) ?>,
          fill: true,
          backgroundColor: 'rgba(124, 58, 237, 0.15)', // purple
          borderColor: 'rgba(124, 58, 237, 1)',
          borderWidth: 3,
          tension: 0.3,
          pointRadius: 5,
          pointHoverRadius: 7,
          pointBackgroundColor: 'rgba(124, 58, 237, 1)'
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: value => '₹' + value.toLocaleString()
            }
          }
        },
        plugins: {
          legend: { labels: { font: { size: 14 } } },
          tooltip: {
            callbacks: {
              label: ctx => `₹${ctx.parsed.y.toLocaleString()}`
            }
          }
        }
      }
    });
  </script>
</body>
</html>




