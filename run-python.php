<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$python = "\"C:\\Users\\badat\\AppData\\Local\\Programs\\Python\\Python314\\python.exe\"";
$script = "\"C:\\xampp\\htdocs\\ecommerce_sales_analysis\\python\\test_db.py\"";

$command = "$python $script 2>&1";

echo "<pre>";
$output = shell_exec($command);
var_dump($output);
echo "</pre>";
