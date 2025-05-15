<?php
/**
 * Authentication middleware
 * 
 * This file checks if a user is logged in with a valid session.
 * If not, it redirects them to the login page.
 */

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    // Not logged in, redirect to login page
    header('Location: index.php');
    exit();
}

// Optional: Session timeout logic (uncomment to enable)
/*
// Check if last activity time is set
if (isset($_SESSION['last_activity'])) {
    // Calculate time since last activity
    $inactive_time = time() - $_SESSION['last_activity'];
    
    // If more than 30 minutes inactive, log out the user
    $timeout = 30 * 60; // 30 minutes
    if ($inactive_time > $timeout) {
        // Destroy session and redirect to login
        session_unset();
        session_destroy();
        header('Location: index.php?timeout=1');
        exit();
    }
}

// Update last activity time
$_SESSION['last_activity'] = time();
*/
?>
