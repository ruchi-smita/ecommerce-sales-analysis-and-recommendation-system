<?php
session_start();
require_once "../../config/database.php";

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    echo "<h2>Your Cart</h2>";
    echo "<p>Your cart is empty.</p>";
    echo '<a href="../products/products.php">Continue Shopping</a>';
    exit;
}

$productIds = array_keys($cart);
$productIds = array_filter($productIds, 'is_numeric');

if (empty($productIds)) {
    echo "<p>Invalid cart data.</p>";
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

<div class="cart-container">

    <div class="cart-table"> 

        <div class="cart-header">
            <span>Product</span>
            <span>Price</span>
            <span>Quantity</span>
            <span>Total</span>
        </div>

        <?php
        $grandTotal = 0;
        foreach ($products as $product):
            $pid   = $product['product_id'];
            $qty   = $cart[$pid];
            $total = $product['price'] * $qty;
            $grandTotal += $total;
        ?>

        <div class="cart-row"
             data-product-id="<?php echo $pid; ?>"
             data-price="<?php echo $product['price']; ?>">

            <div class="product-info">
                <img src="/ecommerce_sales_analysis/<?php echo htmlspecialchars($product['image_url']); ?>" alt="product">
                <span class="product-name">
                    <?php echo htmlspecialchars($product['name']); ?>
                </span>
            </div>

            <span class="price">₹<?php echo $product['price']; ?></span>

            <div class="qty-control">
                <button type="button" class="qty-btn dec">−</button>
                <span class="qty"><?php echo $qty; ?></span>
                <button type="button" class="qty-btn inc">+</button>
            </div>

            <span class="item-total">₹<?php echo $total; ?></span>

            <button type="button" class="remove-btn">✖</button>
        </div> 

        <?php endforeach; ?> 

    </div> 
 
    <!-- SUMMARY -->
    <div class="cart-summary"> 
        <h3>Order Summary</h3>

        <div class="summary-row"> 
            <span>Total Amount</span>
            <strong id="grand-total">₹<?php echo $grandTotal; ?></strong>
        </div>

        <button id="go-to-address" class="primary-btn">
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

<script src="/ecommerce_sales_analysis/assets/js/cart.js"></script>
<script>
document.getElementById("go-to-address").addEventListener("click", () => {
    window.location.href = "address.php";
});
</script>

</body>
</html>
