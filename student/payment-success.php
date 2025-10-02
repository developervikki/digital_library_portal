<?php

require_once '../includes/db.php';
require_once '../includes/auth.php';
include '../includes/header.php';

// ✅ Define the function to avoid fatal error
function formatDateTime($datetime) { 
  return date('d M Y, h:i A', strtotime($datetime));
}

if (!isset($_GET['booking_id'])) {
  echo "<div class='p-6 text-red-600'>Invalid request. Booking ID is missing.</div>";
  include '../includes/footer.php';
  exit;
}

// ... rest of the code


$booking_id = intval($_GET['booking_id']);

// Fetch booking & payment info
$stmt = $conn->prepare("
  SELECT b.date, b.till_date, b.start_time, b.end_time, b.shift, b.purpose,
         s.seat_number, p.amount, p.status, p.method, p.payment_date
  FROM table_bookings b
  JOIN table_seats s ON b.seat_id = s.seat_id
  JOIN table_payments p ON b.id = p.booking_id
  WHERE b.id = ?
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo "<div class='p-6 text-red-600'>Booking not found.</div>";
  include '../includes/footer.php';
  exit;
}

$data = $result->fetch_assoc();
?>

<div class="min-h-screen bg-gray-100 py-10 px-4">
  <div class="max-w-xl mx-auto bg-white p-6 rounded-xl shadow-lg">
    <h2 class="text-2xl font-bold text-center text-indigo-700 mb-4">✅ Booking Confirmation</h2>

    <div class="bg-gray-100 p-4 rounded border">
      <p><strong>📅 From:</strong> <?= htmlspecialchars($data['date']) ?></p>
      <p><strong>📅 Till:</strong> <?= htmlspecialchars($data['till_date']) ?></p>
      <p><strong>🕒 Time:</strong> <?= htmlspecialchars($data['start_time']) ?> to <?= htmlspecialchars($data['end_time']) ?> (<?= ucfirst($data['shift']) ?>)</p>
      <p><strong>💺 Seat Number:</strong> <?= htmlspecialchars($data['seat_number']) ?></p>
      <p><strong>📝 Purpose:</strong> <?= nl2br(htmlspecialchars($data['purpose'])) ?></p>
    </div>

    <div class="bg-white p-4 mt-4 border rounded shadow">
      <p><strong>💰 Amount:</strong> ₹<?= number_format($data['amount'], 2) ?></p>
      <p><strong>💳 Payment Method:</strong> <?= htmlspecialchars($data['method']) ?></p>
      <p><strong>📆 Payment Date:</strong> <?= formatDateTime($data['payment_date']) ?></p>
      <p><strong>📌 Status:</strong>
        <?php if ($data['status'] === 'pending'): ?>
          <span class="text-yellow-600 font-semibold">⏳ Pending Approval</span>
        <?php else: ?>
          <span class="text-green-600 font-semibold">✅ Confirmed</span>
        <?php endif; ?>
      </p>
    </div>

    <div class="mt-6 text-center">
      <a href="/digital-library-portal/student/dashboard.php" class="inline-block bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
        Go to Dashboard
      </a>
    </div>
  </div>
</div>

<?php include '../includes/footer.php'; ?>

