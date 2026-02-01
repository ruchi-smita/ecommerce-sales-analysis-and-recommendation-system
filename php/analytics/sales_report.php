<?php
session_start();

// optional admin check
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$python = '"C:\\Users\\badat\\AppData\\Local\\Programs\\Python\\Python314\\python.exe"';

$command = 'cd C:\\xampp\\htdocs\\ecommerce_sales_analysis && '
         . $python . ' -m python_services.analytics.sales_report';

$output = shell_exec($command);

if ($output === null) {
    die("Python execution failed");
}

$salesData = json_decode($output, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("JSON decode error: " . json_last_error_msg());
}
?>

<h2>Daily Sales Report</h2>

<?php if (empty($salesData)): ?>
    <p>No sales data available.</p>
<?php else: ?>
<table border="1" cellpadding="8">
    <tr>
        <th>Date</th>
        <th>Total Revenue</th>
    </tr>
    <?php foreach ($salesData as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['date']) ?></td>
            <td>₹<?= number_format($row['revenue'], 2) ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
