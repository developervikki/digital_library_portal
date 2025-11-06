<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin(); // Ensure only admin accesses

include '../includes/header-admin.php';
 
function formatDateTime($datetime) { 
  return $datetime ? date('d M Y, h:i A', strtotime($datetime)) : '—';
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

<div class="max-w-7xl mx-auto p-6">
  <h1 class="text-2xl font-semibold text-gray-50 mb-6">💬 All Feedback Messages</h1>

  <div class="overflow-x-auto bg-white shadow rounded-lg">
    <table class="min-w-full text-sm text-left border border-gray-200">
      <thead class="bg-gray-100 text-gray-700">
        <tr>
          <th class="px-4 py-3 border-b">#</th>
          <th class="px-4 py-3 border-b">Student Name</th>
          <th class="px-4 py-3 border-b">Email</th>
          <th class="px-4 py-3 border-b">Message</th>
          
        </tr>
      </thead>
      <tbody class="text-gray-700 divide-y divide-gray-200">

        <?php
       $sql = "SELECT f.*, s.name AS student_name, s.email 
        FROM table_feedback f
        JOIN table_students s ON f.student_id = s.id
        ORDER BY f.submitted_at DESC";




        $result = $conn->query($sql);
        $i = 1;

        if ($result && $result->num_rows > 0):
          while ($row = $result->fetch_assoc()):
        ?>
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3"><?= $i++ ?></td>
            <td class="px-4 py-3"><?= htmlspecialchars($row['student_name']) ?></td>
            <td class="px-4 py-3"><?= htmlspecialchars($row['email']) ?></td>
            <td class="px-4 py-3"><?= htmlspecialchars($row['message']) ?></td>
            
          </tr>
        <?php endwhile; else: ?>
          <tr>
            <td colspan="6" class="text-center py-6 text-gray-400">No messages found.</td>
          </tr>
        <?php endif; ?>

      </tbody>
    </table>
  </div>
</div>

<script>
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
