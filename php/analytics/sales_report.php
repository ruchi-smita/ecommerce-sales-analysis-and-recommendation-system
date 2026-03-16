<?php
session_start();
require_once __DIR__ . "/../../includes/python-runtime.php";

// optional admin check
// if ($_SESSION['role'] !== 'admin') {
//     header("Location: ../auth/login.php");
//     exit;
// }

$command = python_module_command('python_services.analytics.sales_report');

if ($command === null) {
    echo json_encode([]);
    exit;
}

$output = shell_exec($command);

if ($output === null) {
    echo json_encode([]);
    exit;}

$salesData = json_decode($output, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("JSON decode error: " . json_last_error_msg());
}

$output = trim($output);

// IMPORTANT: echo ONLY JSON
echo $output;
exit;
?>

