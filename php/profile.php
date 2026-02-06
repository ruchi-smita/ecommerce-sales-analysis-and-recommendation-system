<?php
session_start();
require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Auth check
|--------------------------------------------------------------------------
*/
$userId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? 'user';

if (!$userId) {
    header("Location: /ecommerce_sales_analysis/php/auth/login.php");
    exit;
}

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
    session_destroy();
    header("Location: /ecommerce_sales_analysis/php/auth/login.php");
    exit;
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

/*
|--------------------------------------------------------------------------
| Generate initials
|--------------------------------------------------------------------------
*/
$initials = strtoupper(substr(trim($user['name']), 0, 1));
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Account</title>
    <link rel="stylesheet" href="../assets/css/profile.css">
</head>
<body>

<main class="account-page">

    <!-- ===================== -->
    <!-- Header -->
    <!-- ===================== -->
    <header class="account-header">
        <h1>My Account</h1>
        <p>Manage your profile and activity</p>
    </header>

    <!-- ===================== -->
    <!-- Profile Card -->
    <!-- ===================== -->
    <section class="profile-section">
        <article class="profile-card">

            <div class="profile-avatar">
                <span><?php echo $initials; ?></span>
            </div>

            <div class="profile-info">
                <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
                <small>
                    Joined on <?php echo date("d M Y", strtotime($user['created_at'])); ?>
                </small>
                <br>
                <small>
                    Role: <strong><?php echo ucfirst($user['role']); ?></strong>
                </small>
            </div>

        </article>
    </section>

    <!-- ===================== -->
    <!-- Stats -->
    <!-- ===================== -->
    <section class="account-stats">
        <div class="stat-card">
            <h3>Total Orders</h3>
            <p><?php echo (int)$orderCount; ?></p>
        </div>
    </section>

    <!-- ===================== -->
    <!-- Actions -->
    <!-- ===================== -->
    <section class="account-actions">

        <?php if ($user['role'] === 'admin'): ?>
            <a href="/ecommerce_sales_analysis/php/admin/dashboards.php"
               class="action-btn admin">
                Admin Dashboard
            </a>
        <?php endif; ?>

        <a href="orders/order.php" class="action-btn">
            View My Orders
        </a>

        <a href="products/products.php" class="action-btn">
            Browse Products
        </a>

        <a href="cart/cart.php" class="action-btn">
            View Cart
        </a>

        <a href="/ecommerce_sales_analysis/php/auth/logout.php"
           class="action-btn logout">
            Logout
        </a>
 
    </section> 

</main>

</body>
</html>
