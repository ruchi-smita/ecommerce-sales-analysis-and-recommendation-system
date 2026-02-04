<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/trending.css">
</head>
<body>
    

<!-- TRENDING PRODUCTS -->
<section class="trending-products">

    <div class="trending-header">
        <h2>Trending Products</h2>
        <a href="/ecommerce_sales_analysis/php/products/products.php" class="view-all">
            View all →
        </a>
    </div>

    <div class="trending-grid">

        <?php if (!empty($trendingProducts)) : ?>

            <?php foreach ($trendingProducts as $p): ?>
                <a 
                    href="/ecommerce_sales_analysis/php/products/product-details.php?id=<?php echo (int)$p['product_id']; ?>" 
                    class="trend-product"
                >

                    <div class="image-wrap">
                        <img 
                            src="/ecommerce_sales_analysis/<?php echo htmlspecialchars($p['image_url']); ?>" 
                            alt="<?php echo htmlspecialchars($p['name']); ?>"
                        >
                    </div>

                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                        <span class="price">₹<?php echo htmlspecialchars($p['price']); ?></span>
                    </div>

                </a>
            <?php endforeach; ?>

        <?php else : ?>
            <p class="no-data">No trending products yet.</p>
        <?php endif; ?>

    </div>

</section>
</body>
</html>