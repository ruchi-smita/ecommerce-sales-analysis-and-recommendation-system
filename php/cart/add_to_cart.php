
<?php
session_start();
require_once "../../config/database.php";

//Validate request

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['product_id']) ||
    !ctype_digit($_POST['product_id'])
) {
    die("Invalid request");
}

$product_id = (int) $_POST['product_id'];
$redirectTo = "/ecommerce_sales_analysis/php/products/products.php";

if (isset($_POST['redirect_to']) && is_string($_POST['redirect_to'])) {
    $candidate = trim($_POST['redirect_to']);

    if (
        strpos($candidate, '/ecommerce_sales_analysis/') === 0 &&
        strpos($candidate, "\n") === false &&
        strpos($candidate, "\r") === false
    ) {
        $redirectTo = $candidate;
    }
}

// Verify product exists

$check = $conn->prepare(
    "SELECT product_id FROM products WHERE product_id = ?"
);
$check->execute([$product_id]);

if (!$check->fetch()) {
    die("Product not found");
}

//Initialize cart

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add / increment product in cart

if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id]++;
} else {
    $_SESSION['cart'][$product_id] = 1;
}

//Log user behavior (cart)

$behaviorUserId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 1;

$behavior = $conn->prepare(
    "INSERT INTO user_behavior (user_id, product_id, action)
     VALUES (?, ?, 'cart')"
);
$behavior->execute([$behaviorUserId, $product_id]);

// Redirect back to the caller page
header("Location: " . $redirectTo);
exit;
?>
