<?php
// start session only if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect user to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /ecommerce_sales_analysis/php/auth/login.php");
        exit;
    }
}
?>
