<?php
session_start(); 
require_once "../../config/database.php";

// Fetch categories
$catStmt = $conn->prepare("SELECT category_id, category_name FROM categories");
$catStmt->execute();
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch genders
$genStmt = $conn->prepare("SELECT gender_id, gender_name FROM genders");
$genStmt->execute();
$genders = $genStmt->fetchAll(PDO::FETCH_ASSOC);

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name        = trim($_POST['name']);
    $price       = trim($_POST['price']);
    $category_id = $_POST['category_id'];
    $gender_id   = $_POST['gender_id'];

    // Image handling
    $image       = $_FILES['image'];

    if (
        empty($name) || empty($price) ||
        empty($category_id) || empty($gender_id)
    ) {
        $message = "All fields are required.";
    }
    elseif (!is_numeric($price)) {
        $message = "Price must be numeric.";
    }
    elseif ($image['error'] !== 0) {
        $message = "Image upload failed.";
    }
    else {

        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($image['type'], $allowedTypes)) {
            $message = "Only JPG and PNG images allowed.";
        }
        elseif ($image['size'] > 2 * 1024 * 1024) { // 2MB
            $message = "Image size must be under 2MB.";
        }
        else {

            // Create unique file name
            $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
            $newName = uniqid("product_", true) . "." . $ext;

            $basePath = $_SERVER['DOCUMENT_ROOT'] . "/ecommerce_sales_analysis/assets/images/products/";

            $uploadPath = $basePath . $newName;
            $dbPath     = "assets/images/products/" . $newName;

            if (move_uploaded_file($image['tmp_name'], $uploadPath)) {

                $sql = "INSERT INTO products
                        (name, price, category_id, gender_id, image_url, created_at)
                        VALUES (?, ?, ?, ?, ?, NOW())";

                $stmt = $conn->prepare($sql);

                if ($stmt->execute([
                    $name,
                    $price,
                    $category_id,
                    $gender_id,
                    $dbPath
                ])) {
                    $message = "Product added successfully!";
                } else {
                    $message = "Database error.";
                }

            } else {
                $message = "Failed to move uploaded file.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/add-products.css">
</head>
<body>

<div class="admin-wrapper">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <h1>Add New Product</h1>
        <p>Fill in the details below to add a product to your store</p>
    </div>

    <?php if ($message): ?>
        <div class="alert">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- FORM -->
    <form method="POST" enctype="multipart/form-data" class="product-form">

        <!-- LEFT: DETAILS -->
        <div class="form-left">

            <div class="form-section">
                <h3>Product Information</h3>

                <label>Product Name</label>
                <input type="text" name="name" placeholder="e.g. Classic Linen Shirt" required>
            </div>

            <div class="form-section">
                <h3>Pricing</h3>

                <label>Price</label>
                <input type="text" name="price" placeholder="e.g. 1999" required>
            </div>

            <div class="form-section">
                <h3>Classification</h3>

                <div class="row">
                    <div>
                        <label>Category</label>
                        <select name="category_id" required>
                            <option value="">Select category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['category_id']; ?>">
                                    <?= htmlspecialchars($cat['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label>Gender</label>
                        <select name="gender_id" required>
                            <option value="">Select gender</option>
                            <?php foreach ($genders as $gen): ?>
                                <option value="<?= $gen['gender_id']; ?>">
                                    <?= htmlspecialchars($gen['gender_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT: IMAGE -->
        <div class="form-right">

            <div class="image-box">
                <span>Product Image</span>
                <p>Upload JPG or PNG (max 2MB)</p>
                <input type="file" name="image" accept="image/*" required>
            </div>

            <button type="submit" class="submit-btn">
                Add Product
            </button>

        </div>

    </form>

</div>

</body>
</html>
