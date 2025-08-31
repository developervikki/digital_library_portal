<?php
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fees_type = $_POST['fees_type'] ?? '';
  $from = $_POST['from'] ?? '';
  $to = $_POST['to'] ?? '';
  $seat_id = $_POST['seat_id'] ?? '';

  $response = ['amount' => 0];

  if ($fees_type && $from && $to && $seat_id) { 
    $seatStmt = $conn->prepare("SELECT daily_fee, half_month_fee, monthly_fee FROM table_seats WHERE seat_id = ?");
    $seatStmt->bind_param("s", $seat_id);
    $seatStmt->execute();
    $seat = $seatStmt->get_result()->fetch_assoc();
    
    $start = new DateTime($from);
    $end = new DateTime($to);
    $diff = $start->diff($end);
    $days = max(1, $diff->days + 1); // Minimum 1 day

    if ($fees_type === 'daily') {
      $response['amount'] = $days * $seat['daily_fee'];
    } elseif ($fees_type === 'half_month') {
      $half_months = ceil($days / 15);
      $response['amount'] = $half_months * $seat['half_month_fee'];
    } elseif ($fees_type === 'monthly') {
      $months = ceil($days / 30);
      $response['amount'] = $months * $seat['monthly_fee'];
    }
  }

  echo json_encode($response);
}
?>

