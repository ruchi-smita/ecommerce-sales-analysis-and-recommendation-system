<?php
require_once __DIR__ . "/functions.php";
?>
<!DOCTYPE html>
<html>
    <head>
        <title>navbar</title>
        <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/navbar.css">
    </head>
    <body>
        <nav class="navbar">
            <div class="nav-container">
                <!-- Logo -->
                <div class="nav-left">
                <a href="index.php" class="logo">
                    <span class="logo-icon">𓆩ꨄ︎𓆪</span>
                    <span class="logo-text">VANTA</span>
                </a>
                </div>

                <!-- Links -->
                <div class="nav-center">
                <a href="php/products/products.php?category=&gender=1">Men</a>
                <a href="php/products/products.php?category=&gender=2">Women</a>
                <a href="php/products/products.php?category=2&gender=">Footwear</a>
                <a href="php/products/products.php?category=3&gender=">Accessories</a>
                </div>

                <!-- Actions -->
                <div class="nav-right">
                <a href="/ecommerce_sales_analysis/php/products/search.php" title="Search">🔍︎</a>
                <a href="/ecommerce_sales_analysis/php/cart/cart.php" title="Cart">🛒</a>
                <a href="/ecommerce_sales_analysis/php/profile.php" title="Account">🏠︎</a>
                </div>

            </div>
        </nav>
        <script src="navbar.js"></script>
    </body>
</html> 
