<?php
error_reporting(E_ALL);
ini_set('display_errors', 1); 
require_once __DIR__ . "/includes/python-runtime.php";

$scriptPath = realpath(__DIR__ . "/python_services/recommend.py");
$command = $scriptPath !== false ? python_script_command($scriptPath) : null;

if ($command === null) {
    die("Python executable not found");
}

$command .= " 2>&1";

echo "<pre>";
$output = shell_exec($command);
var_dump($output);
echo "</pre>";
