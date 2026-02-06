<?php
session_start();
require_once "../../config/database.php";

/*
|--------------------------------------------------------------------------
| Auth check
|--------------------------------------------------------------------------
*/
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    header("Location: /ecommerce_sales_analysis/php/auth/login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Fetch user orders
|--------------------------------------------------------------------------
*/
$orderStmt = $conn->prepare(
    "SELECT 
        o.order_id,
        o.total_amount,
        o.payment_status,
        o.order_status,
        o.order_date,
        p.name AS product_name,
        p.image_url,
        oi.quantity
     FROM orders o
     JOIN order_items oi ON o.order_id = oi.order_id
     JOIN products p ON oi.product_id = p.product_id
     WHERE o.user_id = ?
     ORDER BY o.order_date DESC"
);

$orderStmt->execute([$userId]);
$rows = $orderStmt->fetchAll(PDO::FETCH_ASSOC);


$orderStmt->execute([$userId]);
$orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);

$orders = [];

foreach ($rows as $row) {
    $orders[$row['order_id']]['meta'] = [
        'order_id'       => $row['order_id'],
        'order_date'     => $row['order_date'],
        'total_amount'   => $row['total_amount'],
        'payment_status' => $row['payment_status'],
        'order_status'   => $row['order_status'],
    ];

    $orders[$row['order_id']]['items'][] = [
        'name'     => $row['product_name'],
        'image'    => $row['image_url'],
        'quantity' => $row['quantity'],
    ];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/orders.css">
</head>
<body>

<main class="orders-page">

    <!-- ===================== -->
    <!-- Header -->
    <!-- ===================== -->
    <header class="orders-header">
        <h1>My Orders</h1>
        <p>Track and manage your purchases</p>
    </header>

    <!-- ===================== -->
    <!-- Orders Table -->
    <!-- ===================== -->
    <section class="orders-section">

        <?php if (empty($orders)): ?>
            <p>You haven’t placed any orders yet.</p>
            <a href="../../products/products.php">Start Shopping</a>
        <?php else: ?>

            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>

                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">

                            <div class="order-header">
                                <div>
                                    <strong>Order #<?php echo $order['meta']['order_id']; ?></strong>
                                    <span><?php echo date("d M Y", strtotime($order['meta']['order_date'])); ?></span>
                                </div>
                                <div>
                                    <span class="status"><?php echo ucfirst($order['meta']['order_status']); ?></span>
                                </div>
                            </div>

                            <div class="order-items">
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="order-item">
                                        <img src="/ecommerce_sales_analysis/<?php echo htmlspecialchars($item['image']); ?>" alt="">
                                        <div>
                                            <p><?php echo htmlspecialchars($item['name']); ?></p>
                                            <span>Qty: <?php echo $item['quantity']; ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="order-footer">
                                <span>Total: ₹<?php echo number_format($order['meta']['total_amount'], 2); ?></span>
                                <span><?php echo ucfirst($order['meta']['payment_status']); ?></span>
                            </div>

                        </div>
                    <?php endforeach; ?>


                </tbody>
            </table>

        <?php endif; ?>

    </section>

    <!-- ===================== -->
    <!-- Back Action -->
    <!-- ===================== -->
    <section class="orders-actions">
        <a href="../profile.php">← Back to Profile</a>
    </section>

</main>

</body>
</html>
