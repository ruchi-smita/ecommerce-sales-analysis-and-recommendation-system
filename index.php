<?php
require_once __DIR__ . "/includes/functions.php";
requireLogin();
?>


<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/style.css">

</head>
<body>
    <?php include "includes/navbar.php"; ?>


   <!-- WELCOME SECTION -->
<h2>Welcome to WebsiteName</h2>

<!-- TOP TRENDS -->
<h3>Top Trends</h3>

<hr>

<!-- CATEGORY SECTION -->
<h3>Categories</h3>

<div>

    <!-- CLOTHES -->
    <div>
        <a href="product.php?category=clothes">
        <img src="assets/images/clothes.png" alt="clothes" >            
        <strong>Clothes</strong>
        </a>
    </div>

    <br>

    <!-- FOOTWEAR -->
    <div>
        <a href="product.php?category=footwear">
            <img src="assets/images/footwear.png" alt="footwear" >
            <strong>Footwear</strong>
        </a>
    </div>

    <br>

    <!-- ACCESSORIES -->
    <div>
        <a href="product.php?category=accessories">
        <img src="assets/images/accessories.png" alt="accessories" >           
        <strong>Accessories</strong>
        </a>
    </div>

</div>

</body>
</html>