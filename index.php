<?php
require_once __DIR__ . "/includes/functions.php";
requireLogin();

$apiUrl = "http://localhost/ecommerce_sales_analysis/php/api/get-recommendations.php";
$response = file_get_contents($apiUrl);
$trendingProducts = json_decode($response, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>VINTA | Home</title>
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/style.css">
</head>

<body>

<?php include "includes/navbar.php"; ?>

<!-- HERO / WELCOME SECTION -->
<header class="hero">
    <div class="container">
        <h1>Welcome to <span>VANTA</span></h1>
        <p>
            We analyze real sales data to recommend what’s actually trending —  
            not just what the store wants to push.
        </p>
        <a href="php/products/products.php" class="btn-primary">
            Browse All Products
        </a>
    </div>
</header>

<!-- TRENDING PRODUCTS -->
<section class="section trending">
    <div class="container">
        <h2>🔥 Trending Products</h2>

        <div class="products-grid">

        <?php if (!empty($trendingProducts)) : ?>

            <?php foreach ($trendingProducts as $p): ?>
            <div class="product-card">

                <img 
                src="/ecommerce_sales_analysis/<?php echo htmlspecialchars($p['image_url']); ?>" 
                alt="<?php echo htmlspecialchars($p['name']); ?>"
                >

                <h3><?php echo htmlspecialchars($p['name']); ?></h3>

                <strong>₹<?php echo htmlspecialchars($p['price']); ?></strong>

                <form method="POST" action="/ecommerce_sales_analysis/php/cart/add_to_cart.php">
                    <input 
                    type="hidden" 
                    name="product_id" 
                    value="<?php echo (int)$p['product_id']; ?>"
                    >
                    <button type="submit">Add to Cart</button>
                </form>

            </div>

            <?php endforeach; ?>

        <?php else : ?>
            <p class="no-data">No trending products yet.</p>
        <?php endif; ?>

        </div>
    </div>
</section>

<hr>

<!-- CATEGORIES (NOW CONNECTED TO search.php LIKE YOU WANTED) -->
<section class="section categories">
    <div class="container">
        <h2>Shop by Category</h2>

        <div class="categories-grid">

            <div class="category-box">
                <a href="php/products/search.php?category=1">
                    <img src="assets/images/clothes.png" alt="Clothes">
                    <div class="overlay"><span>Clothes</span></div>                
                </a>
            </div>

            <div class="category-box">
                <a href="php/products/search.php?category=2">
                    <img src="assets/images/footwear.png" alt="Footwear">
                    <div class="overlay"><span>Footwear</span></div>                
                </a>
            </div>

            <div class="category-box">
                <a href="php/products/search.php?category=3">
                    <img src="assets/images/accessories.png" alt="Accessories">
                    <div class="overlay"><span>Accessories</span></div>                
                </a>
            </div>

        </div>
    </div>
</section>

<?php include "includes/footer.php"; ?>

</body>
</html>
