<?php
include_once('../includes/db.php');
include_once('../includes/header.php');

// Simulated login – replace with session ID
if (!isset($_SESSION['student_id'])) { 
    header("Location: ../login.php"); 
    exit;
}  


$student_id = 3;
 // Replace this with $_SESSION['user_id'] if auth is added

$sql = "SELECT b.id, s.seat_number, b.date, b.start_time, b.end_time
        FROM table_bookings b
        JOIN table_seats s ON b.seat_id = s.seat_id
        WHERE b.student_id = ?
        ORDER BY b.date DESC, b.start_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>


<div class="max-w-4xl mx-auto mt-10 p-6 bg-white shadow-lg rounded-2xl">
    <h2 class="text-2xl font-bold text-gray-800 mb-4 text-center">📚 My Bookings</h2>

    <?php if ($result->num_rows > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-700">
                <thead class="bg-blue-100 text-gray-700 font-semibold">
                    <tr>
                        <th class="px-4 py-2">Seat No.</th>
                        <th class="px-4 py-2">Date</th>
                        <th class="px-4 py-2">Start Time</th>
                        <th class="px-4 py-2">End Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium"><?= htmlspecialchars($row['seat_number']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($row['date']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($row['start_time']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($row['end_time']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-center text-gray-600">No bookings found. Go to <a href="book-seat.php" class="text-blue-600 underline">Book a Seat</a>.</p>
    <?php endif; ?>
</div>

<?php include_once('../includes/footer.php'); ?>
