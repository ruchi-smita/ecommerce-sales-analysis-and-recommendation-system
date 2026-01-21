<?php
$python = "\"C:\\Users\\badat\\AppData\\Local\\Programs\\Python\\Python314\\python.exe\"";
$script = "\"C:\\xampp\\htdocs\\ecommerce_sales_analysis\\python_services\\recommend.py\"";

$command = "$python $script 2>&1";
$output = shell_exec($command);

$data = json_decode(trim($output), true);

if ($data === null) {
    die("Python output error: " . htmlspecialchars($output));
}

require_once "../../config/database.php";

$ids = array_column($data, 'product_id');
if (empty($ids)) {
    die("No trending products found");
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));

$stmt = $conn->prepare(
    "SELECT product_id, name, price, image_url
     FROM products
     WHERE product_id IN ($placeholders)"
);
$stmt->execute($ids);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($products);
