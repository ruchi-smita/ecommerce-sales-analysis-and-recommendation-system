<?php
require_once __DIR__ . "/functions.php";
?>

<nav class="navbar">

    <!-- LEFT : WEBSITE NAME -->
    <div class="navbar-left">
        <a href="/ecommerce_sales_analysis/base.php">WebsiteName</a>
    </div>

    <!-- CENTER : SEARCH BAR -->
    <div class="navbar-center">
        <form action="/ecommerce_sales_analysis/php/products/search.php" method="GET">
            <input type="text" name="q" placeholder="Search">
            <button type="submit">Search</button>
        </form>
    </div>

    <!-- RIGHT : LINKS -->
    <div class="navbar-right">
        <a href="/ecommerce_sales_analysis/trending.php">Trending</a>
        <a href="/ecommerce_sales_analysis/php/cart/view-cart.php">Cart</a>

        <?php if (isLoggedIn()): ?>
            <a href="/ecommerce_sales_analysis/php/auth/logout.php">Logout</a>
        <?php else: ?>
            <a href="/ecommerce_sales_analysis/php/auth/login.php">Login</a>
            <a href="/ecommerce_sales_analysis/php/auth/register.php">Register</a>
        <?php endif; ?>
    </div>

</nav>
