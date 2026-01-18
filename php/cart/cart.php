<?php
session_start();
require_once "../../config/database.php";

//Read cart from session
$cart = $_SESSION['cart'] ?? [];

//If cart is empty, stop early

if (empty($cart)) {
    echo "<h2>Your Cart</h2>";
    echo "<p>Cart is empty.</p>";
    echo '<a href="../products/products.php">Go back to products</a>';
    exit;
}

/*
Sanitize product IDs (IMPORTANT)
We only allow numeric IDs to avoid corrupted carts
*/
$productIds = array_keys($cart);
$productIds = array_filter($productIds, fn($id) => is_numeric($id));

if (empty($productIds)) {
    echo "<p>Cart data is invalid. Please clear cart.</p>";
    exit;
}

//Fetch products from database


$placeholders = implode(',', array_fill(0, count($productIds), '?'));

$sql = "
    SELECT product_id, name, price, image_url
    FROM products
    WHERE product_id IN ($placeholders)
";

$stmt = $conn->prepare($sql);
$stmt->execute($productIds);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Cart</title>
    <link rel="stylesheet" href="../../assets/css/cart.css">
</head>
<body>

<h2>Your Cart</h2>

<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>Product</th>
        <th>Price</th>
        <th>Quantity</th>
        <th>Total</th>
    </tr>

    <?php
    $grandTotal = 0;
    foreach ($products as $product):
        $pid = $product['product_id'];
        $qty = $cart[$pid];
        $total = $product['price'] * $qty;
        $grandTotal += $total;
    ?>
        <tr>
            <td><?php echo htmlspecialchars($product['name']); ?></td>
            <td>₹<?php echo htmlspecialchars($product['price']); ?></td>
            <td><?php echo $qty; ?></td>
            <td>₹<?php echo $total; ?></td>
        </tr>
    <?php endforeach; ?>

    <tr>
        <td colspan="3"><strong>Grand Total</strong></td>
        <td><strong>₹<?php echo $grandTotal; ?></strong></td>
    </tr>
</table>

<br>

<a href="../orders/checkout.php">Proceed to Checkout</a> |
<a href="../products/products.php">Continue Shopping</a> |
<a href="clear_cart.php">Clear Cart</a>

</body>
</html>
