<?php

// Sanitize user input
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Format seconds to HH:MM:SS 
function formatDuration($seconds) {
    $h = str_pad(floor($seconds / 3600), 2, '0', STR_PAD_LEFT);
    $m = str_pad(floor(($seconds % 3600) / 60), 2, '0', STR_PAD_LEFT);
    $s = str_pad($seconds % 60, 2, '0', STR_PAD_LEFT);
    return "$h:$m:$s";
}

// Format datetime to readable form
function formatDateTime($datetime) {
    return date("d M Y, h:i A", strtotime($datetime));
}

// Flash message (set in session)
function setFlash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

// Get and clear flash message
function getFlash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

// Render alert box
function showAlert($key, $type = 'info') {
    $msg = getFlash($key);
    if ($msg) {
        echo "<div class='mb-4 p-4 rounded bg-$type-100 text-$type-700 border-l-4 border-$type-500'>$msg</div>";
    }
}

// Check if seat is available (example logic)
function isSeatAvailable($conn, $seat_id, $date, $slot) {
    $stmt = $conn->prepare("SELECT id FROM table_bookings WHERE seat_id = ? AND date = ? AND time_slot = ?");
    $stmt->bind_param("iss", $seat_id, $date, $slot);
    $stmt->execute();
    return $stmt->get_result()->num_rows === 0;
}
