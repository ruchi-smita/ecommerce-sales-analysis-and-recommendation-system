<?php
session_start();
require_once "../../config/database.php";

/* ==============================
   FLOW VALIDATION
================================ */
if (empty($_SESSION['cart'])) {
    die("Cart is empty"); 
} 

if (!isset($_SESSION['address_id'])) {
    die("Address not selected");
}

if (!isset($_POST['payment_method'])) {
    die("Payment method not selected");
}
 
/* ==============================
   DATA
================================ */
$cart          = $_SESSION['cart'];
$addressId     = $_SESSION['address_id'];
$paymentMethod = $_POST['payment_method'];
$onlineMode    = $_POST['online_mode'] ?? null;
$userId        = $_SESSION['user_id'] ?? 1; // demo fallback

/* ==============================
   FETCH PRODUCTS
================================ */
$productIds = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($productIds), '?'));

$stmt = $conn->prepare(
    "SELECT product_id, name, price
     FROM products
     WHERE product_id IN ($placeholders)"
);
$stmt->execute($productIds);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($products)) {
    die("Invalid cart items");
}

/* ==============================
   CALCULATE TOTAL
================================ */
$totalAmount = 0;
foreach ($products as $p) {
    $totalAmount += $p['price'] * $cart[$p['product_id']];
}

/* ==============================
   PAYMENT LOGIC (DEMO-SAFE)
================================ */
if ($paymentMethod === 'COD') {
    $paymentStatus = 'PENDING';
    $paymentLabel  = 'Cash on Delivery';
} else {
    // Demo online payment
    $paymentStatus = 'INITIATED';
    $paymentLabel  = $onlineMode
        ? "Online ($onlineMode)"
        : "Online Payment";
}

/* ==============================
   TRANSACTION
================================ */
$conn->beginTransaction();

try {

    /* -------- CREATE ORDER -------- */
    $orderStmt = $conn->prepare(
        "INSERT INTO orders
        (user_id, address_id, total_amount, payment_method, payment_status, order_status)
        VALUES (?, ?, ?, ?, ?, 'PLACED')"
    );

    $orderStmt->execute([
        $userId, 
        $addressId,
        $totalAmount,
        $paymentLabel,
        $paymentStatus
    ]);

    $orderId = $conn->lastInsertId();

    /* -------- ORDER ITEMS + USER BEHAVIOR -------- */
    foreach ($products as $p) {
        $qty = $cart[$p['product_id']];
 
        // order items
        $itemStmt = $conn->prepare(
            "INSERT INTO order_items
            (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)"
        );
        $itemStmt->execute([
            $orderId,
            $p['product_id'],
            $qty,
            $p['price']
        ]); 
 
        // behavior log
        $behaviorStmt = $conn->prepare(
            "INSERT INTO user_behavior (user_id, product_id, action)
            VALUES (?, ?, 'purchase')"
        );
        $behaviorStmt->execute([$userId, $p['product_id']]);
    }

    $conn->commit();

    // cleanup session
    unset($_SESSION['cart'], $_SESSION['address_id']);

} catch (Exception $e) {
    $conn->rollBack();
    die("Checkout failed: " . $e->getMessage());
}
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Order Confirmed</title>
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/checkout.css">
</head>
<body>

<div class="checkout-wrapper">
    <div class="checkout-card"> 

        <div class="success-icon">✓</div>

        <h2>Order Placed Successfully</h2>

        <p class="subtitle">
            Payment Method:
            <strong><?php echo htmlspecialchars($paymentLabel); ?></strong>
        </p>

        <div class="order-summary"> 
            <div>
                <span>Order ID</span>
                <strong>#<?php echo $orderId; ?></strong>
            </div>

            <div>
                <span>Total Amount</span>
                <strong>₹<?php echo number_format($totalAmount, 2); ?></strong>
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
