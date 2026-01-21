<?php
session_start();
require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Temporary user (until login is implemented)
|--------------------------------------------------------------------------
*/
$userId = 1;

/*
|--------------------------------------------------------------------------
| Fetch user info
|--------------------------------------------------------------------------
*/
$userStmt = $conn->prepare(
    "SELECT name, email, role, created_at 
     FROM users 
     WHERE user_id = ?"
);
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found");
}

/*
|--------------------------------------------------------------------------
| Fetch order count
|--------------------------------------------------------------------------
*/
$orderStmt = $conn->prepare(
    "SELECT COUNT(*) FROM orders WHERE user_id = ?"
);
$orderStmt->execute([$userId]);
$orderCount = $orderStmt->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <link rel="stylesheet" href="../assets/css/profile.css">
</head>
<body>

<h2>🏠︎ My Profile</h2>

<div class="profile-card">
    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <p><strong>Role:</strong> <?php echo ucfirst($user['role']); ?></p>
    <p><strong>Joined:</strong> <?php echo date("d M Y", strtotime($user['created_at'])); ?></p>
    <p><strong>Total Orders:</strong> <?php echo $orderCount; ?></p>
</div>

<br>

<a href="products/products.php">🛍 Browse Products</a> |
<a href="cart/cart.php">🛒 View Cart</a>

</body>
</html>
