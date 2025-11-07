<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); 
require_once __DIR__ . '/db.php';  // Only once  
   
/**
 * ✅ Check if a user is logged in 
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * ✅ Get the current user ID
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * ✅ Get the current user role
 */
function getUserRole(): ?string {
    return $_SESSION['user_role'] ?? null;
}
 
/**
 * ✅ Redirect to login page if user is not logged in
 */
function requireLogin(string $redirect = '/digital-library-portal/login.php'): void {
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit;
    }
}

/**
 * ✅ Allow only admin users
 */
function requireAdmin(string $redirect = '/digital-library-portal/index.php'): void {
    if (!isLoggedIn() || getUserRole() !== 'admin') {
        header("Location: $redirect");
        exit;
    }
}

/**
 * ✅ Allow only student users
 */
function requireStudent(string $redirect = '/digital-library-portal/login.php'): void {
    if (!isLoggedIn() || getUserRole() !== 'student') {
        header("Location: $redirect");
        exit;
    }
}

/**
 * ✅ Logout and redirect to login
 */
function logout(): void {
    session_unset();
    session_destroy();
    header("Location: /digital-library-portal/login.php");
    exit;
}
