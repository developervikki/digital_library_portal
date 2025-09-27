<?php
include_once('../includes/db.php');
include_once('../includes/header.php');

// Simulated login – replace with session logic
$student_id = 1; 

// Handle payment form submission
$payment_msg = '';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $amount = $_POST['amount'];
    $method = $_POST['method'];

    if ($amount > 0 && !empty($method)) {
        $stmt = $conn->prepare("INSERT INTO table_payments (student_id, amount, status, method) VALUES (?, ?, 'pending', ?)");
        $stmt->bind_param("ids", $student_id, $amount, $method);
        if ($stmt->execute()) {
            $payment_msg = "✅ Payment request submitted.";
        } else {
            $payment_msg = "❌ Failed to process payment.";
        }
        $stmt->close();
    } else {
        $payment_msg = "⚠️ Enter a valid amount and method.";
    }
}

// Fetch previous payments
$stmt = $conn->prepare("SELECT amount, status, method, payment_date FROM table_payments WHERE student_id = ? ORDER BY payment_date DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$payments = $stmt->get_result();
?>

<div class="max-w-4xl mx-auto mt-10 p-6 bg-white shadow-xl rounded-2xl">
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">💳 My Payments</h2>

    <?php if ($payment_msg): ?>
        <div class="mb-4 p-3 text-sm rounded-md <?php echo (str_starts_with($payment_msg, '✅') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'); ?>">
            <?= htmlspecialchars($payment_msg) ?>
        </div>
    <?php endif; ?>

    <!-- Payment form -->
    <form method="POST" class="mb-8 space-y-4 bg-gray-50 p-4 rounded-lg">
        <div>
            <label class="block text-sm font-medium text-gray-700">Amount (₹)</label>
            <input type="number" step="0.01" name="amount" class="w-full mt-1 p-2 border border-gray-300 rounded-lg" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Payment Method</label>
            <select name="method" class="w-full mt-1 p-2 border border-gray-300 rounded-lg" required>
                <option value="">-- Select --</option>
                <option value="UPI">UPI</option>
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
                <option value="Netbanking">Netbanking</option>
            </select>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition">Submit Payment</button>
    </form>

    <!-- Payment history table -->
    <h3 class="text-lg font-semibold mb-2 text-gray-800">📜 Payment History</h3>
    <?php if ($payments->num_rows > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-gray-700">
                <thead class="bg-blue-100 font-semibold">
                    <tr>
                        <th class="px-4 py-2">Amount</th>
                        <th class="px-4 py-2">Method</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php while ($row = $payments->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2">₹<?= htmlspecialchars($row['amount']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($row['method']) ?></td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold 
                                    <?= $row['status'] == 'completed' ? 'bg-green-100 text-green-700' :
                                       ($row['status'] == 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td class="px-4 py-2"><?= date("d M Y, h:i A", strtotime($row['payment_date'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-gray-600">No payments found.</p>
    <?php endif; ?>
</div>

<?php include_once('../includes/footer.php'); ?>
