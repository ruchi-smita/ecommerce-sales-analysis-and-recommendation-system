<?php
session_start();
require_once "../../config/database.php";

$userId = $_SESSION['user_id'] ?? 1; // demo fallback

// cart protection
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $conn->prepare(
        "INSERT INTO user_addresses
        (user_id, full_name, phone, address_line, city, state, pincode)
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->execute([
        $userId,
        $_POST['full_name'],
        $_POST['phone'],
        $_POST['address'],
        $_POST['city'],
        $_POST['state'],
        $_POST['pincode']
    ]);

    // 🔴 THIS IS THE KEY LINE YOU ARE MISSING
    $_SESSION['address_id'] = $conn->lastInsertId();

    header("Location: payment.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Delivery Address</title>
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/address.css">
</head>
<body>

<div class="page-wrapper">

    <div class="address-card">

        <header class="card-header">
            <h2>Delivery Address</h2>
            <p>Please enter your shipping details</p>
        </header>

        <form method="POST" class="address-form">

            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" placeholder="John Doe" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" placeholder="10-digit mobile number" required>
            </div>

            <div class="form-group full-width">
                <label>Full Address</label>
                <textarea name="address" placeholder="House no, street, area" required></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" required>
                </div>

                <div class="form-group">
                    <label>State</label>
                    <input type="text" name="state" required>
                </div>

                <div class="form-group">
                    <label>Pincode</label>
                    <input type="text" name="pincode" required>
                </div>
            </div>

            <button type="submit" class="primary-btn">
                Continue to Payment
            </button>

        </form>

    </div>

</div>

</body>
</html>
