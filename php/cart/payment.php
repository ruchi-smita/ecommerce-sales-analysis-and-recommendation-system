<?php
session_start();

// Flow protection
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

if (!isset($_SESSION['address_id'])) {
    header("Location: address.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Payment</title>
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/payment.css">
</head>
<body>

<div class="page-wrapper">

    <div class="payment-container">

        <!-- LEFT: PAYMENT OPTIONS -->
        <div class="payment-card">

            <h2>Select Payment Method</h2>
            <p class="subtext">All transactions are secure</p>

            <form action="../orders/checkout.php" method="POST" id="payment-form">

                <!-- COD -->
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="COD" checked>
                    <div>
                        <strong>Cash on Delivery</strong>
                        <span>Pay when your order arrives</span>
                    </div>
                </label>

                <!-- ONLINE -->
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="ONLINE">
                    <div>
                        <strong>Online Payment</strong>
                        <span>UPI, Card, Net Banking</span>
                    </div>
                </label>

                <!-- ONLINE PAYMENT DETAILS -->
                <div id="online-section" class="online-section">

                    <!-- UPI OPTIONS -->
                    <div class="upi-section">
                        <p class="section-title">Pay using UPI</p>

                        <label class="upi-option">
                            <input type="radio" name="online_mode" value="PHONEPE">
                            PhonePe
                        </label>

                        <label class="upi-option">
                            <input type="radio" name="online_mode" value="GPAY">
                            Google Pay
                        </label>
                    </div>

                    <!-- CARD OPTION -->
                    <div class="card-section">
                        <p class="section-title">Pay using Card</p>

                        <input type="text" name="card_number" placeholder="Card Number">
                        <div class="card-row">
                            <input type="text" name="expiry" placeholder="MM / YY">
                            <input type="text" name="cvv" placeholder="CVV">
                        </div>
                    </div>

                    <p class="demo-note">
                        * This is a demo payment. No real transaction will occur.
                    </p>

                </div>

                <button type="submit" class="primary-btn">
                    Confirm Order
                </button>

            </form>

        </div>

        <!-- RIGHT: ORDER SUMMARY -->
        <div class="summary-card">
            <h3>Order Summary</h3>

            <?php
            require_once "../../config/database.php";

            $grandTotal = 0;
            foreach ($_SESSION['cart'] as $pid => $qty) {
                $stmt = $conn->prepare("SELECT name, price FROM products WHERE product_id = ?");
                $stmt->execute([$pid]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$product) continue;

                $total = $product['price'] * $qty;
                $grandTotal += $total;
                ?>
                <div class="summary-row">
                    <span><?php echo htmlspecialchars($product['name']); ?> × <?php echo $qty; ?></span>
                    <span>₹<?php echo number_format($total, 2); ?></span>
                </div>
            <?php } ?>

            <hr>

            <div class="summary-row total">
                <strong>Total</strong>
                <strong>₹<?php echo number_format($grandTotal, 2); ?></strong>
            </div>
        </div>

    </div>

</div>

<script>
const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
const onlineSection = document.getElementById("online-section");

paymentRadios.forEach(radio => {
    radio.addEventListener("change", () => {
        onlineSection.style.display =
            document.querySelector('input[name="payment_method"]:checked').value === "ONLINE"
            ? "block"
            : "none";
    });
});
</script>


</body>
</html>
