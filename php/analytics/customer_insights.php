<?php
session_start();

// optional admin check
// if ($_SESSION['role'] !== 'admin') {
//     header("Location: ../login.php");
//     exit;
// }

$python = '"C:\\Users\\badat\\AppData\\Local\\Programs\\Python\\Python314\\python.exe"';

$command = 'cd C:\\xampp\\htdocs\\ecommerce_sales_analysis && '
         . $python . ' -m python_services.analytics.customer_insights';

$output = shell_exec($command);

if ($output === null) {
    die("Python execution failed");
}

$output = trim($output);
$customers = json_decode($output, true);

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
    <title>Customer Insights</title>
</head>
<body>

<h2>Top Customers (By Total Spend)</h2>

<?php if (empty($customers)): ?>
    <p>No customer data available.</p>
<?php else: ?>
<table border="1" cellpadding="10">
    <tr>
        <th>#</th>
        <th>Name</th>
        <th>Email</th>
        <th>Total Orders</th>
        <th>Total Spent</th>
        <th>Last Order</th>
    </tr>

    <?php $i = 1; foreach ($customers as $c): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($c['name']) ?></td>
            <td><?= htmlspecialchars($c['email']) ?></td>
            <td><?= $c['total_orders'] ?></td>
            <td>₹<?= number_format($c['total_spent'], 2) ?></td>
            <td><?= htmlspecialchars($c['last_order_date']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

</body>
</html>
