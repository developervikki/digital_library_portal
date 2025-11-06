<?php
ob_start();

require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();
include '../includes/header-admin.php';
 
$searchName = $_GET['name'] ?? ''; 
$searchSeat = $_GET['seat'] ?? '';
$searchMonth = $_GET['month'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  $paymentId = intval($_POST['payment_id']);

  if ($action === 'approve' || $action === 'reject') {
    $newStatus = $action === 'approve' ? 'confirmed' : 'rejected';
    $stmt = $conn->prepare("UPDATE table_payments SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $paymentId);
    $stmt->execute();

    $getStudent = $conn->prepare("SELECT b.student_id, b.id AS booking_id FROM table_bookings b JOIN table_payments p ON b.id = p.booking_id WHERE p.id = ?");
    $getStudent->bind_param("i", $paymentId);
    $getStudent->execute();
    $info = $getStudent->get_result()->fetch_assoc();

    $title = $action === 'approve' ? "Booking Approved" : "Booking Rejected";
    $message = $action === 'approve'
      ? "✅ Your seat booking (ID #{$info['booking_id']}) has been approved."
      : "❌ Your seat booking (ID #{$info['booking_id']}) was rejected.";

    $notify = $conn->prepare("INSERT INTO table_notifications (student_id, title, message) VALUES (?, ?, ?)");
    $notify->bind_param("iss", $info['student_id'], $title, $message);
    $notify->execute();
  }

  if ($action === 'delete') {
    $stmt = $conn->prepare("DELETE FROM table_payments WHERE id = ?");
    $stmt->bind_param("i", $paymentId);
    $stmt->execute();
  }

  if ($action === 'update') {
    $amount = floatval($_POST['amount']);
    $method = trim($_POST['method']);
    $stmt = $conn->prepare("UPDATE table_payments SET amount = ?, method = ?, status = 'confirmed' WHERE id = ?");
    $stmt->bind_param("dsi", $amount, $method, $paymentId);
    $stmt->execute();
  }

  header("Location: payments.php");
  exit;
}

$summaryQuery = "SELECT b.fees_type, SUM(p.amount) AS total FROM table_payments p
  JOIN table_bookings b ON p.booking_id = b.id
  WHERE 1=1";

if (!empty($searchMonth)) {
  $month = date('Y-m', strtotime($searchMonth));
  $summaryQuery .= " AND DATE_FORMAT(p.payment_date, '%Y-%m') = '" . $conn->real_escape_string($month) . "'";
}

$summaryQuery .= " GROUP BY b.fees_type";
$summaryResult = $conn->query($summaryQuery);

$chartLabels = [];
$chartData = [];
if ($summaryResult) {
  while ($row = $summaryResult->fetch_assoc()) {
    $chartLabels[] = ucwords($row['fees_type']);
    $chartData[] = (float) $row['total'];
  }
  $summaryResult->data_seek(0);
}
?>

<div id="particles-js"></div>
<style>
  #particles-js {
    position: fixed;
    width: 100%;
    height: 100%;
    z-index: -1;
    top: 0; left: 0;
    background: linear-gradient(135deg, #1e293b, #312e81); /* dark navy to indigo */
  }
</style>
<div class="max-w-6xl mx-auto px-6 py-10">
  <h2 class="text-2xl font-bold text-gray-50 mb-6">💳 Payment Records<?= $searchMonth ? " for " . date('F Y', strtotime($search_month)) : '' ?></h2>

  <form method="GET" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
    <input type="text" name="name" placeholder="Search by Student Name" value="<?= htmlspecialchars($searchName) ?>" class="px-4 py-2 border rounded" />
    <input type="text" name="seat" placeholder="Search by Seat Number" value="<?= htmlspecialchars($searchSeat) ?>" class="px-4 py-2 border rounded" />
    <input type="month" name="month" value="<?= htmlspecialchars($searchMonth) ?>" class="px-4 py-2 border rounded" />
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">🔍 Filter</button>
  </form>

  <?php if ($summaryResult && $summaryResult->num_rows > 0): ?>
  <div class="mb-6">
    <h3 class="text-lg font-semibold text-gray-50 mb-2">📊 Monthly Summary (by Fee Type)</h3>
    <ul class="list-disc pl-5 text-gray-50">
      <?php while ($row = $summaryResult->fetch_assoc()): ?>
        <li><?= htmlspecialchars(ucwords($row['fees_type'])) ?>: ₹<?= number_format($row['total'], 2) ?></li>
      <?php endwhile; ?>
    </ul>
  </div>
  <?php endif; ?>

  <canvas id="monthlyChart" height="100" class="mb-10"></canvas>

  <div class="bg-white shadow rounded overflow-x-auto">

  
  <div class="flex gap-4 mb-4  mt-px ml-px">
    <a href="../reports/generate-report.php" target="_blank" class="bg-blue-500 text-white px-4 py-2 rounded">📄 Export PDF</a>
    <a href="../reports/export-csv.php" class="bg-green-500 text-white px-4 py-2 rounded">📁 Export CSV</a>
    <form method="POST" enctype="multipart/form-data">
      <input type="file" name="import_file" accept=".csv" class="inline-block border p-1" required />
      <button type="submit" name="action" value="import_csv" class="bg-purple-500 text-white px-3 py-2 rounded ml-2">📤 Import CSV</button>
    </form>
  </div>

  <div class="bg-white shadow rounded overflow-x-auto">
    <table class="min-w-full text-sm text-left border border-gray-200">
      <thead class="bg-gray-100 text-gray-700">
        <tr>
          <th class="px-4 py-3 border-b">#</th>
          <th class="px-4 py-3 border-b">Student</th>
          <th class="px-4 py-3 border-b">Seat</th>
          <th class="px-4 py-3 border-b">Amount (₹)</th>
          <th class="px-4 py-3 border-b">Payment Mode</th>
          <th class="px-4 py-3 border-b">Payment</th>
          <th class="px-4 py-3 border-b">Date</th>
          <th class="px-4 py-3 border-b">Month</th>
          <th class="px-4 py-3 border-b">Action</th>
        </tr>
      </thead>
      <tbody class="text-gray-700 divide-y divide-gray-100">
        <?php
        $query = "SELECT p.*, s.name AS student_name, b.seat_id, t.seat_number 
          FROM table_payments p 
          JOIN table_students s ON p.student_id = s.id
          JOIN table_bookings b ON p.booking_id = b.id
          JOIN table_seats t ON b.seat_id = t.seat_id
          WHERE 1=1";

        if (!empty($search_name)) {
          $query .= " AND s.name LIKE '%" . $conn->real_escape_string($search_name) . "%'";
        }
        if (!empty($search_seat)) {
          $query .= " AND t.seat_number LIKE '%" . $conn->real_escape_string($search_seat) . "%'";
        }
        if (!empty($search_month)) {
          $month = date('Y-m', strtotime($search_month));
          $query .= " AND DATE_FORMAT(p.payment_date, '%Y-%m') = '" . $conn->real_escape_string($month) . "'";
        }

        $query .= " ORDER BY p.payment_date DESC";
        $result = $conn->query($query);
        $i = 1;

        if ($result->num_rows > 0):
          while ($row = $result->fetch_assoc()):
        ?>
        <tr>
          <td class="px-4 py-3"><?= $i++ ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($row['student_name']) ?></td>
          <td class="px-4 py-3">Seat <?= htmlspecialchars($row['seat_number']) ?></td>
          <td class="px-4 py-3 font-medium text-green-700">₹<?= number_format($row['amount'], 2) ?></td>
          <td class="px-4 py-3 capitalize"><?= htmlspecialchars($row['method']) ?></td>
          <td class="px-4 py-3">
            <span class="inline-block px-2 py-1 text-xs rounded
              <?= $row['status'] === 'confirmed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
              <?= $row['status'] === 'confirmed' ? '--' : 'Unpaid' ?>
            </span>
          </td>
          <td class="px-4 py-3 text-gray-500"><?= date('d M Y, h:i A', strtotime($row['payment_date'])) ?></td>
          <td class="px-4 py-3 text-gray-700 font-semibold"><?= date('F Y', strtotime($row['payment_date'])) ?></td>
          <td class="px-4 py-3 space-y-1">
            <?php if ($row['status'] !== 'confirmed'): ?>
              <form method="POST">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="payment_id" value="<?= $row['id'] ?>">
                <button class="bg-green-500 text-white text-xs px-3 py-1 rounded hover:bg-green-600">Approve</button>
              </form>
            <?php endif; ?>
            <?php if ($row['status'] !== 'rejected'): ?>
              <form method="POST">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="payment_id" value="<?= $row['id'] ?>">
                <button class="bg-yellow-500 text-white text-xs px-3 py-1 rounded hover:bg-yellow-600">Reject</button>
              </form>
            <?php endif; ?>
            <form method="POST">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="payment_id" value="<?= $row['id'] ?>">
              <button onclick="return confirm('Are you sure?')" class="bg-red-500 text-white text-xs px-3 py-1 rounded hover:bg-red-600">Delete</button>
            </form>
            <form method="POST">
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="payment_id" value="<?= $row['id'] ?>">
              <input type="number" name="amount" value="<?= $row['amount'] ?>" step="0.01" class="w-20 text-xs border px-2 py-1 rounded mb-1" />
              <input type="text" name="method" value="<?= htmlspecialchars($row['method']) ?>" class="w-24 text-xs border px-2 py-1 rounded mb-1" />
              <button class="bg-indigo-500 text-white text-xs px-3 py-1 rounded hover:bg-indigo-600">Paid</button>
            </form>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr>
          <td colspan="9" class="text-center py-6 text-gray-500">No payments found.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>


 </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('monthlyChart').getContext('2d');
const chart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($chartLabels) ?>,
    datasets: [{
      label: 'Total Amount (₹)',
      data: <?= json_encode($chartData) ?>,
      backgroundColor: 'rgba(59, 130, 246, 0.5)',
      borderColor: 'rgba(59, 130, 246, 1)',
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: function(value) {
            return '₹' + value;
          }
        }
      }
    }
  }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/particles.js"></script>
<script>
const ctx1 = document.getElementById('monthlyChart').getContext('2d');
const chart1 = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?= json_encode($chartLabels) ?>,
    datasets: [{
      label: 'Total Amount (₹)',
      data: <?= json_encode($chartData) ?>,
      backgroundColor: 'rgba(59, 130, 246, 0.5)',
      borderColor: 'rgba(59, 130, 246, 1)',
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: function(value) {
            return '₹' + value;
          }
        }
      }
    }
  }
});

particlesJS("particles-js", {
  particles: {
    number: { value: 60, density: { enable: true, value_area: 800 } },
    color: { value: ["#c7d2fe", "#e0e7ff", "#f0f9ff"] },
    shape: { type: ["circle", "star"] },
    opacity: { value: 0.4, random: true },
    size: { value: 3, random: true },
    move: {
      enable: true,
      speed: 1.5,
      direction: "none",
      random: true,
      straight: false,
      bounce: false
    },
    line_linked: {
      enable: true,
      distance: 120,
      color: "#93c5fd",
      opacity: 0.2,
      width: 1
    }
  },
  interactivity: {
    detect_on: "canvas",
    events: {
      onhover: { enable: true, mode: "grab" },
      onclick: { enable: true, mode: "push" }
    },
    modes: {
      grab: { distance: 140, line_linked: { opacity: 0.3 } },
      push: { particles_nb: 4 }
    }
  },
  retina_detect: true
});
</script>

<?php include '../includes/footer.php'; ?>
<?php ob_end_flush(); ?>

