<?php
session_start();
require_once "../../config/database.php";

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    die("Cart is empty");
}

if (!isset($_POST['payment_method'])) {
    die("Payment method not selected");
}

$paymentMethod = $_POST['payment_method'];
$userId = $_SESSION['user_id'] ?? 1; // replace when auth exists

$conn->beginTransaction();

try {
    $totalAmount = 0;

    // Fetch products
    $productIds = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));

    $stmt = $conn->prepare(
        "SELECT product_id, price FROM products WHERE product_id IN ($placeholders)"
    );
    $stmt->execute($productIds);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $p) {
        $totalAmount += $p['price'] * $cart[$p['product_id']];
    }

    // Decide payment status
    $paymentStatus = ($paymentMethod === 'COD') ? 'PENDING' : 'SUCCESS';

    // Create order
    $orderStmt = $conn->prepare(
        "INSERT INTO orders 
        (user_id, total_amount, payment_method, payment_status, order_status) 
        VALUES (?, ?, ?, ?, 'PLACED')"
    );

    $orderStmt->execute([
        $userId,
        $totalAmount,
        $paymentMethod,
        $paymentStatus
    ]);

    $orderId = $conn->lastInsertId();

    // Insert order items + behavior log
    foreach ($products as $p) {
        $qty = $cart[$p['product_id']];

        $itemStmt = $conn->prepare(
            "INSERT INTO order_items (order_id, product_id, quantity, price)
             VALUES (?, ?, ?, ?)"
        );
        $itemStmt->execute([
            $orderId,
            $p['product_id'],
            $qty,
            $p['price']
        ]);

        $behaviorStmt = $conn->prepare(
            "INSERT INTO user_behavior (user_id, product_id, action)
             VALUES (?, ?, 'purchase')"
        );
        $behaviorStmt->execute([$userId, $p['product_id']]);
    }

    $conn->commit();
    unset($_SESSION['cart']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmed</title>
    <!-- <link rel="stylesheet" href="../../assets/css/checkout.css"> -->
</head>
<body>

<div class="checkout-wrapper">
    <div class="checkout-card">
        <div class="success-icon">✓</div>

        <h2>Order Placed Successfully</h2>

        <p class="subtitle">
            Payment Method:
            <strong><?php echo htmlspecialchars($paymentMethod); ?></strong>
        </p>

        <div class="order-summary">
            <div>
                <span>Total Amount</span>
                <strong>₹<?php echo number_format($totalAmount, 2); ?></strong>
            </div>
            <div>
                <span>Order ID</span>
                <strong>#<?php echo $orderId; ?></strong>
            </div>
            <div>
                <span>Payment Status</span>
                <strong><?php echo $paymentStatus; ?></strong>
            </div>
        </div>

        <a href="../products/products.php" class="primary-btn">
            Continue Shopping
        </a>
    </div>
</div>

</body>
</html>
<?php

} catch (Exception $e) {
    $conn->rollBack();
    die("Checkout failed: " . $e->getMessage());
}
?>