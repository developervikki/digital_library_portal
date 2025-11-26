<?php
require_once('../includes/db.php');

// Simulated login   
$student_id = 1;   
 
header('Content-Type: text/csv');  
header('Content-Disposition: attachment;filename="time-logs.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Start Time', 'End Time', 'Duration (seconds)']);

$stmt = $conn->prepare("SELECT start_time, end_time, duration FROM table_time_logs WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [$row['start_time'], $row['end_time'], $row['duration']]);
}

fclose($output);
exit;






