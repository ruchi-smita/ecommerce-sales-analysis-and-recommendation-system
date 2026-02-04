<?php
session_start();
require_once "../../config/database.php";

/* ---------------- CART COUNT ---------------- */
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

/* ---------------- READ SEARCH INPUTS ---------------- */
$keyword  = isset($_GET['q']) ? trim($_GET['q']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$gender   = isset($_GET['gender']) ? $_GET['gender'] : '';

/* ---------------- BASE QUERY ---------------- */
$sql = "
SELECT 
    p.product_id,
    p.name,
    p.price,
    p.image_url,
    c.category_name,
    g.gender_name
FROM products p
JOIN categories c ON p.category_id = c.category_id
JOIN genders g ON p.gender_id = g.gender_id
WHERE 1
";

$params = [];

/* ---------------- KEYWORD SEARCH ---------------- */
if ($keyword !== '') {
    $sql .= " AND p.name LIKE :keyword";
    $params[':keyword'] = '%' . $keyword . '%';
}

/* ---------------- CATEGORY FILTER ---------------- */
if (!empty($category)) {
    $sql .= " AND c.category_id = :category";
    $params[':category'] = $category;
}

/* ---------------- GENDER FILTER ---------------- */
if (!empty($gender)) {
    $sql .= " AND g.gender_id = :gender";
    $params[':gender'] = $gender;
}

$sql .= " ORDER BY p.created_at DESC";

/* ---------------- EXECUTE QUERY ---------------- */
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Products</title>
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/search.css">
</head>
<body>

<!-- ================= NAVBAR ================= -->
<div class="top-nav">
    <div class="nav-left">
        <a href="/ecommerce_sales_analysis/index.php" class="logo">
            FASHIONLY
        </a>
    </div>

    <div class="nav-right">
        <a href="/ecommerce_sales_analysis/index.php">Home</a>
        <a href="/ecommerce_sales_analysis/php/products/products.php">Products</a>
        <a href="/ecommerce_sales_analysis/php/cart/cart.php">
            Cart (<?php echo $cartCount; ?>)
        </a>
    </div>
</div>

<!-- ================= PAGE TITLE ================= -->
<h2>Search Products</h2>

<!-- ================= SEARCH FORM ================= -->
<form method="GET" action="search.php" class="search-form">
    <input
        type="text"
        name="q"
        placeholder="Search products..."
        value="<?php echo htmlspecialchars($keyword); ?>"
    >
    <button type="submit">Search</button>
</form>

<!-- ================= RESULTS ================= -->
<div class="products-grid">

<?php if (!empty($results)): ?>
    <?php foreach ($results as $row): ?>
        <div class="product-card">
            <img
                src="/ecommerce_sales_analysis/<?php echo htmlspecialchars($row['image_url']); ?>"
                alt="<?php echo htmlspecialchars($row['name']); ?>"
            >

            <h3><?php echo htmlspecialchars($row['name']); ?></h3>

            <strong>₹<?php echo htmlspecialchars($row['price']); ?></strong>

            <p>
                <?php echo htmlspecialchars($row['category_name']); ?> |
                <?php echo htmlspecialchars($row['gender_name']); ?>
            </p>

            <form method="POST" action="/ecommerce_sales_analysis/php/cart/add_to_cart.php">
                <input type="hidden" name="product_id" value="<?php echo (int)$row['product_id']; ?>">
                <button type="submit">Add to Cart</button>
            </form>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No products found.</p>
<?php endif; ?>

</div>
<script src="/ecommerce_sales_analysis/assets/js/search.js"></script>


</body>
</html>
