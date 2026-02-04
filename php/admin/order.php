<?php
session_start();
require_once "../../config/database.php";

/* ================= ADMIN CHECK ================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* ================= UPDATE ORDER STATUS ================= */
if (isset($_POST['update_status'])) {
    $orderId = (int) $_POST['order_id'];
    $newStatus = $_POST['order_status'];

    $allowedStatuses = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];

    if (in_array($newStatus, $allowedStatuses)) {
        $stmt = $conn->prepare(
            "UPDATE orders SET order_status = ? WHERE order_id = ?"
        );
        $stmt->execute([$newStatus, $orderId]);
    }
}

/* ================= FILTER ================= */
$statusFilter = $_GET['status'] ?? 'all';

$sql = "
    SELECT 
        o.order_id,
        o.total_amount,
        o.order_status,
        o.payment_method,
        o.order_date,
        u.name AS customer_name,
        u.email AS customer_email
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
";

$params = [];

if ($statusFilter !== 'all') {
    $sql .= " WHERE o.order_status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders</title>
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/dashboard.css">
</head>
<body>

<div class="admin-layout">

    <!-- SIDEBAR -->
    <?php include "sidebar.php"; ?>

    <!-- MAIN -->
    <main class="main-content">

        <!-- HEADER -->
        <header class="dashboard-header">
            <div>
                <h1>Orders</h1>
                <p>Manage and track customer orders</p>
            </div>

            <div class="admin-actions">
                <form method="GET">
                    <select name="status" onchange="this.form.submit()">
                        <option value="all">All</option>
                        <option value="pending" <?= $statusFilter==='pending'?'selected':''; ?>>Pending</option>
                        <option value="paid" <?= $statusFilter==='paid'?'selected':''; ?>>Paid</option>
                        <option value="shipped" <?= $statusFilter==='shipped'?'selected':''; ?>>Shipped</option>
                        <option value="delivered" <?= $statusFilter==='delivered'?'selected':''; ?>>Delivered</option>
                        <option value="cancelled" <?= $statusFilter==='cancelled'?'selected':''; ?>>Cancelled</option>
                    </select>
                </form>
            </div>
        </header>

        <!-- ORDERS TABLE -->
        <section class="panel">

            <?php if (empty($orders)): ?>
                <p>No orders found.</p>
            <?php else: ?>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php foreach ($orders as $order): ?>
                        <tr>

                            <td>#<?= $order['order_id']; ?></td>

                            <td>
                                <strong><?= htmlspecialchars($order['customer_name']); ?></strong><br>
                                <small><?= htmlspecialchars($order['customer_email']); ?></small>
                            </td>

                            <td>₹ <?= number_format($order['total_amount'], 2); ?></td>

                            <td>
                                <span class="status-badge <?= $order['order_status']; ?>">
                                    <?= ucfirst($order['order_status']); ?>
                                </span>
                            </td>


                            <td><?= htmlspecialchars($order['payment_method']); ?></td>

                            <td><?= date("d M Y", strtotime($order['order_date'])); ?></td>

                            <td>
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id']; ?>">
                                    <select name="order_status">
                                        <?php foreach (['pending','paid','shipped','delivered','cancelled'] as $status): ?>
                                            <option value="<?= $status; ?>" <?= $order['order_status']===$status?'selected':''; ?>>
                                                <?= ucfirst($status); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="update_status">
                                        Update
                                    </button>
                                </form>
                            </td>

                        </tr>
                    <?php endforeach; ?>

                    </tbody>
                </table>

            <?php endif; ?>

        </section>

    </main>

</div>

</body>
</html>
