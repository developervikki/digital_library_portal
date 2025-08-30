<?php
include_once('../includes/db.php');
include_once('../includes/header.php');
 
// You can add auth check here if needed

$message = ""; 
if ($_SERVER["REQUEST_METHOD"] === "POST") { 
    $student_id = 1; // Replace with actual logged-in user ID from session
    $feedback = trim($_POST['feedback']); 

    if (!empty($feedback)) {
        $stmt = $conn->prepare("INSERT INTO table_feedback (student_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $student_id, $feedback);
        if ($stmt->execute()) {
            $message = "✅ Feedback submitted successfully!";
        } else {
            $message = "❌ Something went wrong.";
        }
        $stmt->close();
    } else {
        $message = "⚠️ Please enter your feedback.";
    }
}
?>

<div class="max-w-3xl mx-auto mt-12 p-6 bg-white shadow-xl rounded-2xl">
    <h2 class="text-2xl font-bold mb-4 text-center text-gray-800">📝 Give Your Feedback</h2>

    <?php if ($message): ?>
        <div class="mb-4 p-3 text-sm rounded-lg <?php echo (str_starts_with($message, '✅') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'); ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
        <label class="block text-gray-700 font-medium">
            Your Feedback
            <textarea name="feedback" rows="5" class="w-full mt-2 p-3 border border-gray-300 rounded-lg focus:ring focus:outline-none" required></textarea>
        </label>
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
            Submit Feedback
        </button>
    </form>
</div>

<?php include_once('../includes/footer.php'); ?>
