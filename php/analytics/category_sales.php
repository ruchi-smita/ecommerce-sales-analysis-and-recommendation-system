<?php
session_start();
require_once __DIR__ . "/../../includes/python-runtime.php";

// optional admin check
// if ($_SESSION['role'] !== 'admin') {
//     header("Location: ../auth/login.php");
//     exit;
// }

$command = python_module_command('python_services.analytics.category_sales');

if ($command === null) {
    die("Python executable not found");
}

$output = shell_exec($command);

if ($output === null) {
    die("Python execution failed");
}

$salesData = json_decode($output, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("JSON decode error: " . json_last_error_msg());
}
?>
