<?php
session_start();

require_once "../../config/database.php";

// Optional admin check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$python = '"C:\\Users\\badat\\AppData\\Local\\Programs\\Python\\Python314\\python.exe"';
$projectRoot = 'C:\\xampp\\htdocs\\ecommerce_sales_analysis';

/* ================= SUMMARY CARDS ================= */
$totalSales = $conn->query("SELECT SUM(total_amount) FROM orders")->fetchColumn() ?? 0;
$totalOrders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?? 0;
$totalCustomers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn() ?? 0;

/* ================= RECENT ORDERS ================= */
$stmt = $conn->query("
    SELECT o.order_id, u.name AS user_name,
           o.total_amount, o.order_status, o.order_date
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
    LIMIT 5
");
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================= CATEGORY SALES (Python) ================= */
$command = 'cd ' . $projectRoot . ' && '
         . $python . ' -m python_services.analytics.category_sales';

$output = trim(shell_exec($command));
$categorySales = json_decode($output, true) ?? [];

/* ================= CUSTOMER INSIGHTS (Python) ================= */
$command = 'cd ' . $projectRoot . ' && '
         . $python . ' -m python_services.analytics.customer_insights';

$output = trim(shell_exec($command));
$topCustomers = json_decode($output, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    $topCustomers = [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="admin-layout">

    <?php include __DIR__ . "\sidebar.php"; ?>


    <!-- ================= MAIN ================= -->
    <main class="main-content">

        <?php include __DIR__ . "\ad-header.php"; ?>

        <!-- KPI SUMMARY -->
        <section class="kpi-section">
            <div class="kpi-card">
                <span>Total Sales</span>
                <strong>₹ <?= number_format($totalSales, 2) ?></strong>
            </div>

            <div class="kpi-card">
                <span>Total Orders</span>
                <strong><?= $totalOrders ?></strong>
            </div>

            <div class="kpi-card">
                <span>Total Customers</span>
                <strong><?= $totalCustomers ?></strong>
            </div>
        </section>

        <!-- ANALYTICS -->
        <section class="analytics-section">

            <div class="panel">
                <h3>Revenue Analytics</h3>
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <div class="panel">
                <h3>Customer Distribution</h3>
                <div class="chart-container">
                    <canvas id="customerChart"></canvas>
                </div>
            </div>

        </section>

        <!-- CATEGORY INSIGHTS -->
        <section class="panel">
            <h3>Top Categories</h3>

            <?php if (!empty($categorySales)): ?>
                <div class="split">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorySales as $cat): ?>
                                <tr>
                                    <td><?= htmlspecialchars($cat['category']) ?></td>
                                    <td>₹ <?= number_format($cat['revenue'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="chart-container small">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            <?php else: ?>
                <p>No category data available.</p>
            <?php endif; ?>
        </section>

        <!-- RECENT ORDERS -->
        <section class="panel">
            <h3>Recent Orders</h3>

            <?php if (!empty($recentOrders)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>#<?= $order['order_id'] ?></td>
                                <td><?= htmlspecialchars($order['user_name']) ?></td>
                                <td>₹ <?= number_format($order['total_amount'], 2) ?></td>
                                <td><?= htmlspecialchars($order['order_status']) ?></td>
                                <td><?= date("d M Y", strtotime($order['order_date'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No recent orders.</p>
            <?php endif; ?>
        </section>

    </main>

</div>

<script>
    const categorySalesData = <?= json_encode($categorySales) ?>;
    const customerInsightsData = <?= json_encode($topCustomers) ?>;
</script>

<script src="/ecommerce_sales_analysis/assets/js/dashboard.js"></script>

</body>
</html>
