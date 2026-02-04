<?php
session_start();

// optional admin check
// if ($_SESSION['role'] !== 'admin') {
//     header("Location: ../auth/login.php");
//     exit;
// }

$python = '"C:\\Users\\badat\\AppData\\Local\\Programs\\Python\\Python314\\python.exe"';

$command = 'cd C:\\xampp\\htdocs\\ecommerce_sales_analysis && '
         . $python . ' -m python_services.analytics.category_sales';

$output = shell_exec($command);

if ($output === null) {
    die("Python execution failed");
}

$salesData = json_decode($output, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("JSON decode error: " . json_last_error_msg());
}
?>