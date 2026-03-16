<?php
session_start();
require_once __DIR__ . "/../../includes/python-runtime.php";

/* ---------- ADMIN CHECK (recommended) ---------- */
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     header("Location: ../login.php");
//     exit;
// }

/* ---------- PYTHON EXECUTION ---------- */
$command = python_module_command('python_services.analytics.top_products');

if ($command === null) {
    die("Python executable not found");
}

$output = shell_exec($command);

if ($output === null) {
    die("Python execution failed");
}

/* ---------- CLEAN & DECODE JSON ---------- */
$output = trim($output);
$topProducts = json_decode($output, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "<pre>RAW OUTPUT:\n";
    echo htmlspecialchars($output);
    echo "</pre>";
    die("JSON decode error: " . json_last_error_msg());
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Top Selling Products</title>
</head>
<body>

<h2>Top Selling Products</h2>

<?php if (empty($topProducts)): ?>
    <p>No sales data available.</p>
<?php else: ?>

<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>#</th>
        <th>Product Name</th>
        <th>Units Sold</th>
        <th>Total Revenue</th>
    </tr>

    <?php $i = 1; foreach ($topProducts as $product): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($product['name']) ?></td>
            <td><?= (int)$product['total_sold'] ?></td>
            <td>₹<?= number_format($product['revenue'], 2) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<?php endif; ?>

</body>
</html>
