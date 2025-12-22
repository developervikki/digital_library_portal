<?php
session_start();

// Destroy all session variables
$_SESSION = [];

// Destroy the session
session_destroy(); 

// Redirect to login page
header("Location: ../login.php");
exit();


