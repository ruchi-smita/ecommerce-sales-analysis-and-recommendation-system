<?php
session_start();

require_once "../../config/database.php";

/* ---------------- CART COUNT ---------------- */
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

/* ---------------- FETCH FILTER DATA ---------------- */
$catStmt = $conn->prepare("SELECT * FROM categories");
$catStmt->execute();
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

$genStmt = $conn->prepare("SELECT * FROM genders");
$genStmt->execute();
$genders = $genStmt->fetchAll(PDO::FETCH_ASSOC);

/* ---------------- FILTER INPUT ---------------- */
$categoryFilter = $_GET['category'] ?? '';
$genderFilter   = $_GET['gender'] ?? '';

/* ---------------- PRODUCT QUERY ---------------- */
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

if (!empty($categoryFilter)) {
    $sql .= " AND c.category_id = :category_id";
    $params[':category_id'] = $categoryFilter;
}

if (!empty($genderFilter)) {
    $sql .= " AND g.gender_id = :gender_id";
    $params[':gender_id'] = $genderFilter;
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Products</title>
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/products.css">
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
    <h2>All Products</h2>

<!-- ================= FILTER FORM ================= -->
<form method="GET" class="filter-form">
    <select name="category">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat['category_id']; ?>"
                <?php if ($categoryFilter == $cat['category_id']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($cat['category_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="gender">
        <option value="">All Genders</option>
        <?php foreach ($genders as $gen): ?>
            <option value="<?php echo $gen['gender_id']; ?>"
                <?php if ($genderFilter == $gen['gender_id']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($gen['gender_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Filter</button>
</form>

<!-- ================= PRODUCT GRID ================= -->
<div class="products-grid">

<?php if (empty($products)): ?>
    <p>No products found.</p>
<?php endif; ?>

<?php foreach ($products as $row): ?>
    <div class="product-card">
        <img src="../../<?php echo htmlspecialchars($row['image_url']); ?>" alt="Product Image">

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

</div>

</body>
</html>
