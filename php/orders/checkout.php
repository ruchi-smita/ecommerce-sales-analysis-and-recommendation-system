<?php
session_start();
require_once "../../config/database.php";

//Read cart

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    die("Cart is empty");
}

//Start transaction

$conn->beginTransaction();

try {
    $totalAmount = 0;

    //Calculate total
    
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

    //Create order
    
    $orderStmt = $conn->prepare(
        "INSERT INTO orders (user_id, total_amount, status)
         VALUES (1, ?, 'completed')"
    );
    $orderStmt->execute([$totalAmount]);

    $orderId = $conn->lastInsertId();

    //Insert order items + log purchase behavior
    
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

        $behavior = $conn->prepare(
            "INSERT INTO user_behavior (user_id, product_id, action)
             VALUES (1, ?, 'purchase')"
        );
        $behavior->execute([$p['product_id']]);
    }

    //Commit + clear cart
    
    $conn->commit();
    unset($_SESSION['cart']);

    echo "<h2>Order placed successfully</h2>";
    echo "<a href='../products/products.php'>Continue Shopping</a>";

} catch (Exception $e) {
    $conn->rollBack();
    die("Checkout failed: " . $e->getMessage());
}
