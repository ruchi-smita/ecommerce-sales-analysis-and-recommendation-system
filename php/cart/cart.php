<?php
session_start();
require_once "../../config/database.php";

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    echo "<h2>Your Cart</h2>";
    echo "<p>Cart is empty.</p>";
    echo '<a href="../products/products.php">Go back to products</a>';
    exit;
}

$productIds = array_keys($cart);
$productIds = array_filter($productIds, fn($id) => is_numeric($id));

if (empty($productIds)) {
    echo "<p>Cart data is invalid. Please clear cart.</p>";
    exit;
}

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
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/cart.css">
</head>
<body>

<h2>Your Cart</h2>

<form action="../orders/checkout.php" method="POST" id="checkout-form">
<div class="cart-container">

    <div class="cart-table">
        <div class="cart-header">
            <span>Product</span>
            <span>Price</span>
            <span>Qty</span>
            <span>Total</span>
        </div>

        <?php
        $grandTotal = 0;
        foreach ($products as $product):
            $pid = $product['product_id'];
            $qty = $cart[$pid];
            $total = $product['price'] * $qty;
            $grandTotal += $total;
        ?>
        <div class="cart-row" data-product-id="<?php echo $pid; ?>" data-price="<?php echo $product['price']; ?>">
            <div class="product-info">
                <img src="/ecommerce_sales_analysis/<?php echo htmlspecialchars($product['image_url']); ?>" alt="product">
                <span><?php echo htmlspecialchars($product['name']); ?></span>
            </div>

            <span>₹<?php echo $product['price']; ?></span>

            <div class="qty-control">
                <button type="button" class="qty-btn dec">−</button>
                <span class="qty"><?php echo $qty; ?></span>
                <button type="button" class="qty-btn inc">+</button>
            </div>


            <span>₹<?php echo $total; ?></span>

        <button type="button" class="remove-btn">✖</button>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="cart-summary">
        <div class="summary-row">
            <strong id="grand-total">₹<?php echo $grandTotal; ?></strong>
            <strong>₹<?php echo $grandTotal; ?></strong>
        </div>

        <!-- PAYMENT METHOD -->
        <div class="payment-section">
            <h4>Select Payment Method</h4>

            <label>
                <input type="radio" name="payment_method" value="COD" checked>
                Cash on Delivery
            </label><br>

            <label>
                <input type="radio" name="payment_method" value="ONLINE">
                Online Payment (Demo)
            </label>
        </div>

        <div class="address-section">
            <h4>Delivery Address</h4>

            <input type="text" name="full_name" id="full_name" placeholder="Full Name" required>
            <input type="text" name="phone" id="phone" placeholder="Phone Number" required>
            <textarea name="address" id="address" placeholder="Full Address" required></textarea>
            <input type="text" name="city" id="city" placeholder="City" required>
            <input type="text" name="state" id="state" placeholder="State" required>
            <input type="text" name="pincode" id="pincode" placeholder="Pincode" required>

        </div>

    <button type="submit" class="primary-btn">
            Place Order
    </button>

        <a href="../products/products.php" class="secondary-link">
            Continue Shopping
        </a>

        <a href="clear_cart.php" class="danger-link">
            Clear Cart
        </a>
    </div>

</div>
</form>
<script src="/ecommerce_sales_analysis/assets/js/cart.js"></script>
<script src="/ecommerce_sales_analysis/assets/js/checkout.js"></script>
</body>
</html>
