
<?php
include_once('../includes/db.php');
include_once('../includes/header.php');

// Simulated login
$student_id = 1;
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $duration = $_POST['duration']; // in seconds

    $stmt = $conn->prepare("INSERT INTO table_time_logs (student_id, start_time, end_time, duration) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $student_id, $start_time, $end_time, $duration);
    $stmt->execute();
    exit;
}
?>

<div class="max-w-3xl mx-auto mt-10 p-6 bg-white shadow-lg rounded-2xl text-center">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">⏱️ Study Timer</h2>

    <div class="text-5xl font-mono text-gray-800 mb-4" id="timerDisplay">00:00:00</div>

    <div class="space-x-4">
        <button id="startBtn" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">Start</button>
        <button id="stopBtn" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 hidden">Stop</button>
    </div>

    <p id="sessionSaved" class="text-green-600 mt-4 hidden">✅ Session saved successfully.</p>
</div>

<script>
let startTime, timerInterval;

const startBtn = document.getElementById('startBtn');
const stopBtn = document.getElementById('stopBtn');
const timerDisplay = document.getElementById('timerDisplay');
const sessionSaved = document.getElementById('sessionSaved');

function formatTime(seconds) {
    const h = String(Math.floor(seconds / 3600)).padStart(2, '0');
    const m = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
    const s = String(seconds % 60).padStart(2, '0');
    return `${h}:${m}:${s}`;
}

startBtn.addEventListener('click', () => {
    startTime = new Date();
    let elapsed = 0;
    timerDisplay.textContent = formatTime(elapsed);

    timerInterval = setInterval(() => {
        elapsed++;
        timerDisplay.textContent = formatTime(elapsed);
    }, 1000);

    startBtn.classList.add('hidden');
    stopBtn.classList.remove('hidden');
    sessionSaved.classList.add('hidden');
});

stopBtn.addEventListener('click', () => {
    clearInterval(timerInterval);
    const endTime = new Date();
    const duration = Math.floor((endTime - startTime) / 1000);

    // Save session
    fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `start_time=${startTime.toISOString()}&end_time=${endTime.toISOString()}&duration=${duration}`
    }).then(() => {
        sessionSaved.classList.remove('hidden');
    });

    stopBtn.classList.add('hidden');
    startBtn.classList.remove('hidden');
});
</script>

<?php include_once('../includes/footer.php'); ?>
