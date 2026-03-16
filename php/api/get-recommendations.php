<?php
require_once __DIR__ . "/../../includes/python-runtime.php";

$scriptPath = realpath(__DIR__ . "/../../python_services/recommend.py");
$command = $scriptPath !== false ? python_script_command($scriptPath) : null;

if ($command === null) {
    die("Python executable not found.");
}

$command .= " 2>&1";
$output = shell_exec($command);

$data = json_decode(trim($output), true);

if ($data === null) {
    die("Python output error: " . htmlspecialchars($output));
}

require_once __DIR__ . "/../../config/database.php";

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
