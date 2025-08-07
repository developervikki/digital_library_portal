<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();

include '../includes/header-admin.php'; 

// Fetch Admin Info
$admin_id = $_SESSION['admin_id'] ?? null;
$admin = ['name' => 'Admin', 'profile_photo' => 'default.png'];
if ($admin_id) {
  $stmt = $conn->prepare("SELECT name, profile_photo FROM table_admins WHERE id = ?");
  $stmt->bind_param("i", $admin_id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) {
    $admin = $row;
  }
  $stmt->close();
}

// Handle Add/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
  $seat_number = trim($_POST['seat_number']);
  $shift = $_POST['shift'] ?? '';
  $start_time = $_POST['start_time'] ?? '';
  $end_time = $_POST['end_time'] ?? '';
  $price = floatval($_POST['price'] ?? 0);

  if (isset($_POST['seat_id']) && $_POST['seat_id'] !== '') {
    $seat_id = intval($_POST['seat_id']);
    $stmt = $conn->prepare("UPDATE table_seats SET seat_number=?, shift=?, start_time=?, end_time=?, price=?, is_active=1 WHERE seat_id=?");
    $stmt->bind_param("ssssdi", $seat_number, $shift, $start_time, $end_time, $price, $seat_id);
  } else {
    $stmt = $conn->prepare("INSERT INTO table_seats (seat_number, shift, start_time, end_time, price, is_active) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("ssssd", $seat_number, $shift, $start_time, $end_time, $price);
  }
  $stmt->execute();
}

// Handle Delete
if (isset($_GET['delete'])) {
  $seat_id = intval($_GET['delete']);
  $conn->query("DELETE FROM table_seats WHERE seat_id = $seat_id");
}

// Fetch All Seats with Booking Info
$result = $conn->query("
  SELECT s.*, b.date, b.till_date, st.name AS student_name 
  FROM table_seats s
  LEFT JOIN table_bookings b ON s.seat_id = b.seat_id AND CURDATE() BETWEEN b.date AND b.till_date
  LEFT JOIN table_students st ON b.student_id = st.id
  ORDER BY s.seat_id ASC
");


$seats = $result->fetch_all(MYSQLI_ASSOC);

// Payment Summary
$paymentResult = $conn->query("SELECT SUM(amount) as total_amount, COUNT(*) as total_transactions FROM table_payments WHERE status = 'pending'");
$paymentSummary = $paymentResult->fetch_assoc() ?? ['total_amount' => 0, 'total_transactions' => 0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Seats</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="max-w-6xl mx-auto px-6 py-10">
  <div class="flex justify-between items-center mb-8">
    <h2 class="text-2xl font-bold">🪑 Manage Seats with Manual Shift & Time</h2>
    <div class="flex items-center gap-3">
      <img src="/digital-library-portal/uploads/profiles/<?= htmlspecialchars($admin['profile_photo']) ?>" class="w-10 h-10 rounded-full border shadow" />
      <span class="text-gray-700 font-medium">👤 <?= htmlspecialchars($admin['name']) ?></span>
    </div>
  </div>

  <!-- 💰 Payment Summary -->
  <div class="mb-6 bg-blue-100 p-4 rounded shadow text-blue-900">
    💰 Total Pending Payments: ₹<?= number_format($paymentSummary['total_amount'] ?? 0, 2) ?> |
    Transactions: <?= $paymentSummary['total_transactions'] ?? 0 ?>
  </div>

  <!-- ✅ Add/Update Form -->
  <form method="POST" class="mb-8 grid grid-cols-1 md:grid-cols-6 gap-4">
    <input type="hidden" name="seat_id" id="seat_id">
    <div>
      <label>Seat Number</label>
      <input type="text" name="seat_number" id="seat_number" class="w-full px-3 py-2 border rounded" required>
    </div>
    <div>
      <label>Shift</label>
      <input type="text" name="shift" id="shift" placeholder="Morning, Evening" class="w-full px-3 py-2 border rounded" required>
    </div>
    <div>
      <label>Start Time</label>
      <input type="time" name="start_time" id="start_time" class="w-full px-3 py-2 border rounded" required>
    </div>
    <div>
      <label>End Time</label>
      <input type="time" name="end_time" id="end_time" class="w-full px-3 py-2 border rounded" required>
    </div>
    <div>
      <label>Price (₹)</label>
      <input type="number" step="0.01" name="price" id="price" class="w-full px-3 py-2 border rounded" required>
    </div>
    <div class="flex items-end">
      <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Save</button>
    </div>
  </form>

  <!-- 📋 Seat List Table -->
  <div class="bg-white shadow rounded overflow-x-auto">
    <table class="min-w-full text-sm text-left">
      <thead class="bg-gray-200">
        <tr>
          <th class="px-4 py-2">#</th>
          <th class="px-4 py-2">Seat</th>
          <th class="px-4 py-2">Shift</th>
          <th class="px-4 py-2">Time</th>
          <th class="px-4 py-2">Price</th>
          <th class="px-4 py-2">Status</th>
          <th class="px-4 py-2 text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($seats as $i => $row): ?>
        <tr class="border-t">
          <td class="px-4 py-2"><?= $i + 1 ?></td>
          <td class="px-4 py-2 font-semibold text-blue-800">🪑 <?= htmlspecialchars($row['seat_number']) ?></td>
          <td class="px-4 py-2 text-gray-700"><?= htmlspecialchars($row['shift']) ?></td>
          <td class="px-4 py-2 text-gray-600">
            Start: <?= htmlspecialchars($row['start_time']) ?><br>
            End: <?= htmlspecialchars($row['end_time']) ?>
          </td>
          <td class="px-4 py-2 text-gray-800 font-medium">₹<?= number_format($row['price'], 2) ?></td>
          <td class="px-4 py-2">
            <?php if (!empty($row['student_name'])): ?>
              <div class="text-sm text-red-700">
                <span class="px-2 py-1 bg-red-100 rounded text-xs">Booked</span><br>
                👤 <?= htmlspecialchars($row['student_name']) ?><br>
📅 <?= !empty($row['date']) ? date('d M Y', strtotime($row['date'])) : 'N/A' ?> 
to 
<?= !empty($row['till_date']) ? date('d M Y', strtotime($row['till_date'])) : 'N/A' ?>
              </div>
            <?php else: ?>
              <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded">Available</span>
            <?php endif; ?>
          </td>
          <td class="px-4 py-2 text-right space-x-2">
            <button onclick="editSeat(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)" class="text-blue-600 hover:underline">Edit</button>
            <a href="?delete=<?= $row['seat_id'] ?>" onclick="return confirm('Delete this seat?')" class="text-red-600 hover:underline">Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ✏️ Script to prefill on edit -->
<script>
function editSeat(data) {
  document.getElementById('seat_id').value = data.seat_id;
  document.getElementById('seat_number').value = data.seat_number;
  document.getElementById('shift').value = data.shift;
  document.getElementById('start_time').value = data.start_time;
  document.getElementById('end_time').value = data.end_time;
  document.getElementById('price').value = data.price;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

</body>
</html>

<?php include '../includes/footer.php'; ?>
