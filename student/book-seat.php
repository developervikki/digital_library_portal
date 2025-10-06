<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';  
  
if (session_status() === PHP_SESSION_NONE) { 
    session_start();  
}    
 
if (!isset($_SESSION['student_id'])) { 
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$success = '';
$error = '';

// Booking logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? '';
    $seat_id = intval($_POST['seat_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $paid_amount = floatval($_POST['paid_amount'] ?? 0);
    $remaining_amount = max($price - $paid_amount, 0);
    $status = ($paid_amount >= $price) ? 'approved' : 'pending';
    $payment_status = ($paid_amount >= $price) ? 'paid' : 'pending';
    $id_proof_path = '';

    // Prevent booking if student already has a seat
    $stmt = $conn->prepare("SELECT COUNT(*) FROM table_bookings WHERE student_id = ? AND CURDATE() BETWEEN date AND till_date");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->bind_result($already_booked);
    $stmt->fetch();
    $stmt->close();

    if ($already_booked > 0) {
        $error = "❌ You have already booked a seat. You cannot book more than one seat.";
    }

    // Generate till_date
    if (empty($error) && $date) {
        $fromDateObj = new DateTime($date);
        $tillDateObj = clone $fromDateObj;
        $tillDateObj->modify('+1 month')->modify('-1 day');
        $till_date = $tillDateObj->format('Y-m-d');
    }

    // Check if seat is already booked for selected period
    if (empty($error)) {
        $check = $conn->prepare("
            SELECT COUNT(*) FROM table_bookings
            WHERE seat_id = ? AND (
                (date <= ? AND till_date >= ?) OR
                (date <= ? AND till_date >= ?) OR
                (date >= ? AND till_date <= ?)
            )
        ");
        $check->bind_param("issssss", $seat_id, $date, $date, $till_date, $till_date, $date, $till_date);
        $check->execute();
        $check->bind_result($conflict_count);
        $check->fetch();
        $check->close();

        if ($conflict_count > 0) {
            $error = "❌ This seat is already booked for the selected period.";
        }
    }

    // ID proof upload
    if (empty($error) && isset($_FILES['id_proof']) && $_FILES['id_proof']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $file_type = $_FILES['id_proof']['type'];
        $file_size = $_FILES['id_proof']['size'];

        if (!in_array($file_type, $allowed_types)) {
            $error = "❌ Only JPG, PNG or PDF files are allowed.";
        } elseif ($file_size > 2 * 1024 * 1024) {
            $error = "❌ File must be less than 2MB.";
        } else {
            $upload_dir = '../uploads/id_proofs/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $filename = time() . '_' . basename($_FILES['id_proof']['name']);
            $target_path = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['id_proof']['tmp_name'], $target_path)) {
                $id_proof_path = 'id_proofs/' . $filename;
            } else {
                $error = "❌ Failed to upload ID proof.";
            }
        }
    }

    // Final booking insert
    if (empty($error) && $date && $seat_id > 0) {
        $stmt = $conn->prepare("SELECT shift, start_time, end_time FROM table_seats WHERE seat_id = ?");
        $stmt->bind_param("i", $seat_id);
        $stmt->execute();
        $stmt->bind_result($shift, $start_time, $end_time);

        if ($stmt->fetch()) {
            $stmt->close();

            $insertBooking = $conn->prepare("INSERT INTO table_bookings (student_id, seat_id, shift, start_time, end_time, date, till_date, id_proof, price, paid_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insertBooking->bind_param("iissssssdds", $student_id, $seat_id, $shift, $start_time, $end_time, $date, $till_date, $id_proof_path, $price, $paid_amount, $status);

            if ($insertBooking->execute()) {
                $booking_id = $insertBooking->insert_id;
                $insertBooking->close();

                $insertPayment = $conn->prepare("INSERT INTO table_payments (student_id, amount, remaining_amount, status, paid_on, booking_id, payment_type, method, payment_date) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, NOW())");
                $payment_type = 'manual';
                $method = 'cash';
                $insertPayment->bind_param("iddsiss", $student_id, $paid_amount, $remaining_amount, $payment_status, $booking_id, $payment_type, $method);

                if ($insertPayment->execute()) {
                    $success = "✅ Seat booked successfully! Status: <strong>" . strtoupper($status) . "</strong>";
                } else {
                    $error = "❌ Payment insertion failed.";
                }
            } else {
                $error = "❌ Booking failed. Please try again.";
            }
        } else {
            $error = "❌ Invalid seat selected.";
        }
    } elseif (empty($error)) {
        $error = "⚠️ All fields are required.";
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="max-w-xl mx-auto mt-10 p-6 bg-white shadow rounded">
    <h1 class="text-2xl font-bold mb-4">🎫 Book a Seat</h1>

    <?php if ($success): ?>
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label class="block mb-1">From Date</label>
            <input type="date" name="date" class="w-full px-3 py-2 border rounded" required>
        </div>

        <div>
            <label class="block mb-1">Select Seat</label>
            <select name="seat_id" id="seat_id" class="w-full px-3 py-2 border rounded" required>
                <option value="">-- Select Seat --</option>
                <?php
                $seats = $conn->query("
                    SELECT s.seat_id, s.seat_number 
                    FROM table_seats s
                    WHERE s.is_active = 1
                      AND NOT EXISTS (
                          SELECT 1 FROM table_bookings b
                          WHERE b.seat_id = s.seat_id
                            AND CURDATE() BETWEEN b.date AND b.till_date
                      )
                    ORDER BY s.seat_number ASC
                ");
                while ($seat = $seats->fetch_assoc()):
                ?>
                    <option value="<?= $seat['seat_id'] ?>"><?= htmlspecialchars($seat['seat_number']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label class="block mb-1">Shift</label>
            <input type="text" id="shift" class="w-full px-3 py-2 border rounded bg-gray-100" readonly>
        </div>

        <div>
            <label class="block mb-1">Start Time</label>
            <input type="time" id="start_time" class="w-full px-3 py-2 border rounded bg-gray-100" readonly>
        </div>

        <div>
            <label class="block mb-1">End Time</label>
            <input type="time" id="end_time" class="w-full px-3 py-2 border rounded bg-gray-100" readonly>
        </div>

        <div>
            <label class="block mb-1">Price (₹)</label>
            <input type="text" id="price" name="price" class="w-full px-3 py-2 border rounded bg-gray-100" readonly>
        </div>

       

        

        <div>
            <label class="block mb-1">Upload ID Proof</label>
            <input type="file" name="id_proof" accept=".jpg,.jpeg,.png,.pdf" class="w-full px-3 py-2 border rounded bg-white" required>
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Book Now
        </button>
    </form>
</div>

<script>
let seatPrice = 0;
document.getElementById('seat_id').addEventListener('change', function () {
    const seatId = this.value;
    if (!seatId) return;

    fetch('get-seat-details.php?seat_id=' + seatId)
        .then(res => res.json())
        .then(data => {
            document.getElementById('shift').value = data.shift || '';
            document.getElementById('start_time').value = data.start_time || '';
            document.getElementById('end_time').value = data.end_time || '';
            document.getElementById('price').value = data.price || '';
            seatPrice = parseFloat(data.price || 0);
            updateRemaining();
        });
});

document.getElementById('paid_amount').addEventListener('input', updateRemaining);

function updateRemaining() {
    const paid = parseFloat(document.getElementById('paid_amount').value || 0);
    const remaining = Math.max(seatPrice - paid, 0);
    document.getElementById('remaining_amount').value = `₹ ${remaining.toFixed(2)}`;
}
</script>

<?php include '../includes/footer.php'; ?>
