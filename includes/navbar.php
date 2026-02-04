<?php
require_once __DIR__ . "/functions.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Navbar</title>
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/navbar.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-container">

        <!-- LEFT: BRAND -->
        <div class="nav-left">
            <a href="/ecommerce_sales_analysis/index.php" class="logo">
                FASHIONLY
            </a>
        </div>

        <!-- CENTER: NAV LINKS -->
        <div class="nav-center">
            <a href="/ecommerce_sales_analysis/index.php">Home</a>
            <a href="/ecommerce_sales_analysis/php/products/products.php?category=&gender=">Collections</a>
            <a href="/ecommerce_sales_analysis/php/products/categories.php">categories</a>
            <a href="#">About</a>
        </div>

        <!-- RIGHT: ACTIONS -->
        <div class="nav-right">
            <a href="/ecommerce_sales_analysis/php/products/search.php" class="icon-link" aria-label="Search">
                🔍︎
            </a>
            <a href="/ecommerce_sales_analysis/php/cart/cart.php" class="icon-link" aria-label="Cart">
                &#128722;
            </a>
            <a href="/ecommerce_sales_analysis/php/profile.php" class="icon-link" aria-label="Account">
                &#128100;
            </a>
        </div>

    </div>
</nav>

</body>
</html>
