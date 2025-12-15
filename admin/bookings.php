  <?php       
require_once '../includes/db.php';  
require_once '../includes/auth.php';    
requireAdmin();       
include '../includes/header-admin.php';      
      
function formatDateTime($datetime) { 
  return date('d M Y, h:i A', strtotime($datetime)); 
}
?>

<div class="max-w-7xl mx-auto p-6">
  <h1 class="text-3xl font-bold text-gray-50 mb-6">📋 Manage Seat Bookings</h1>

  <?php if (isset($_GET['msg'])): ?>
    <div class="mb-4 p-3 rounded bg-green-100 text-green-800 font-medium shadow">
      <?= htmlspecialchars($_GET['msg']) ?>
    </div>
  <?php endif; ?>

  <div class="mb-4 flex flex-col md:flex-row gap-4 items-start md:items-center">
    <input type="text" id="searchInput" placeholder="Search student name..." class="border border-gray-300 rounded px-3 py-2 w-full md:w-1/3" onkeyup="filterCards()">
    <input type="month" id="monthFilter" class="border border-gray-300 rounded px-3 py-2 w-full md:w-1/4" onchange="filterCards()">
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="cardContainer">
    <?php
    $sql = "SELECT b.*, 
              MONTHNAME(b.date) AS month_name,
              s.name AS student_name, 
              t.seat_number, 
              p.status AS payment_status
            FROM table_bookings b
            JOIN table_students s ON b.student_id = s.id
            JOIN table_seats t ON b.seat_id = t.seat_id
            LEFT JOIN table_payments p ON b.id = p.booking_id
            ORDER BY b.created_at DESC";

    $result = $conn->query($sql);
    $i = 1;

    if ($result && $result->num_rows > 0):
      while ($row = $result->fetch_assoc()):
        $status = strtolower($row['approval_status'] ?? 'pending');

        if ($status === 'rejected') continue;

        $statusClasses = match ($status) {
          'approved' => 'bg-green-100 text-green-700',
          'rejected' => 'bg-red-100 text-red-700',
          default    => 'bg-yellow-100 text-yellow-700'
        };
    ?>
  <style>
  #particles-js {
    position: fixed;
    width: 100%;
    height: 100%;
    z-index: -1;
    top: 0; left: 0;
    background: linear-gradient(135deg, #1e293b, #312e81); /* Tailwind slate-800 to indigo-800 */
  }
</style>

    <div class="bg-white shadow rounded-lg p-5 border border-gray-200 hover:shadow-md transition card" data-name="<?= strtolower($row['student_name']) ?>" data-month="<?= date('Y-m', strtotime($row['date'])) ?>">
      <div class="flex justify-between items-start mb-2">
        <h2 class="text-lg font-semibold text-gray-800">Booking #<?= $i++ ?></h2>
        <span class="text-xs text-gray-500"><?= formatDateTime($row['created_at']) ?></span>
      </div>

      <div class="space-y-1 text-sm text-gray-700">
        <p><strong>Student:</strong> <?= htmlspecialchars($row['student_name']) ?></p>
        <p><strong>Seat No:</strong> <?= htmlspecialchars($row['seat_number']) ?></p>
        <p><strong>Date:</strong> <?= htmlspecialchars($row['date']) ?> (<?= htmlspecialchars($row['month_name']) ?>)</p>
        <p><strong>Time:</strong> <?= htmlspecialchars($row['start_time']) ?> - <?= htmlspecialchars($row['end_time']) ?></p>
        <p><strong>Shift:</strong> <?= htmlspecialchars($row['shift']) ?></p>
        <p><strong>Fees Type:</strong> <?= htmlspecialchars($row['fees_type']) ?></p>
        <p><strong>Purpose:</strong> <?= htmlspecialchars($row['purpose'] ?: 'N/A') ?></p>
        <p><strong>ID Proof:</strong>
          <?php if (!empty($row['id_proof'])): ?>
            <a href="../uploads/id_proofs/<?= $row['id_proof'] ?>" target="_blank" class="text-indigo-600 underline">View</a>
          <?php else: ?>N/A<?php endif; ?>
        </p>
      </div>

      <div class="mt-3 flex flex-wrap items-center gap-2">
        <span class="px-2 py-1 rounded-full text-xs font-semibold <?= $statusClasses ?>">
          <?= htmlspecialchars($status) ?>
        </span>
        <span class="text-sm font-medium">Payment:</span>
        <span class="px-2 py-1 rounded-full text-xs font-semibold <?= ($row['payment_status'] === 'paid') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
          <?= htmlspecialchars($row['payment_status'] ? 'Paid' : 'Pending') ?>
        </span>
      </div>

      <!-- Action Buttons (Approve/Reject/Delete) -->
      <div class="mt-4 flex justify-end gap-3">
        <?php if ($status === 'pending'): ?>
          <form method="post" action="update-booking-status.php" class="flex gap-2">
            <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
            <button name="action" value="approve" class="text-green-600 text-sm hover:underline">Approve</button>
            <button name="action" value="reject" class="text-red-600 text-sm hover:underline">Reject</button>
          </form>
        <?php elseif ($status === 'approved'): ?>
          <span class="text-green-600 text-sm font-medium">Already Approved</span>
        <?php endif; ?>

        <!-- Delete Button -->
        <form method="post" action="delete.php" onsubmit="return confirm('Are you sure you want to delete this booking?');">
          <input type="hidden" name="booking_id" value="<?= $row['id'] ?>">
          <button type="submit" class="text-red-500 text-sm hover:underline">🗑 Delete</button>
        </form>
      </div>
    </div>
    <?php endwhile; else: ?>
      <p class="text-gray-500 col-span-full text-center py-6">No bookings found.</p>
    <?php endif; ?>
  </div>
</div>

<script>
function filterCards() {
  const search = document.getElementById('searchInput').value.toLowerCase();
  const month = document.getElementById('monthFilter').value;
  const cards = document.querySelectorAll('.card');

  cards.forEach(card => {
    const name = card.getAttribute('data-name');
    const cardMonth = card.getAttribute('data-month');
    const matchesName = name.includes(search);
    const matchesMonth = !month || month === cardMonth;
    card.style.display = (matchesName && matchesMonth) ? 'block' : 'none';
  });
}
</script>
<style>
  #particles-js {
    position: fixed;
    width: 100%;
    height: 100%;
    z-index: -1; 
    top: 0; left: 0;
    background: linear-gradient(135deg, #1e293b, #312e81); /* Tailwind slate-800 to indigo-800 */
  }
</style>

<div id="particles-js"></div>

<script>
  particlesJS("particles-js", {
    particles: {
      number: { value: 80, density: { enable: true, value_area: 900 } },
      color: { value: ["#a5b4fc", "#c7d2fe", "#e0e7ff", "#f0f9ff"] }, // soft blues, purples, whites
      shape: {
        type: ["circle", "star"],
        stroke: { width: 0, color: "#fff" },
        polygon: { nb_sides: 5 }
      },
      opacity: {
        value: 0.4,
        random: true,
        anim: { enable: true, speed: 0.5, opacity_min: 0.1, sync: false }
      },
      size: {
        value: 3,
        random: true,
        anim: { enable: true, speed: 2, size_min: 0.5, sync: false }
      },
      move: {
        enable: true,
        speed: 1,
        direction: "none",
        random: true,
        straight: false,
        bounce: false,
        attract: { enable: false }
      },
      line_linked: {
        enable: true,
        distance: 130,
        color: "#a5b4fc",
        opacity: 0.15,
        width: 1
      }
    },
    interactivity: {
      detect_on: "canvas",
      events: {
        onhover: { enable: true, mode: "grab" },
        onclick: { enable: true, mode: "push" },
        resize: true
      },
      modes: {
        grab: { distance: 140, line_linked: { opacity: 0.4 } },
        push: { particles_nb: 4 }
      }
    },
    retina_detect: true
  });
</script>

<?php include '../includes/footer.php'; ?>
