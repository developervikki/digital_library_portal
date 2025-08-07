<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) { 
  $booking_id = intval($_POST['booking_id']);
  $stmt = $conn->prepare("DELETE FROM table_bookings WHERE id = ?"); 
  $stmt->bind_param("i", $booking_id);

  if ($stmt->execute()) {
    header("Location: bookings.php?msg=" . urlencode("Booking deleted successfully."));
  } else {
    header("Location: bookings.php?msg=" . urlencode("Failed to delete booking."));
  }
  exit;
} else {
  header("Location: bookings.php?msg=" . urlencode("Invalid delete request."));
  exit;
}

