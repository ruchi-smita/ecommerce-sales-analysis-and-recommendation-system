<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>sidebar</title>
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/dashboard.css">
</head>
<body>

<aside class="sidebar">

    <div class="sidebar-brand">
        <h2>FASHIONLY</h2>
        <span>Admin Panel</span>
    </div>

    <nav class="sidebar-nav">

        <span class="nav-section">Overview</span>
        <a href="/ecommerce_sales_analysis/php/admin/dashboards.php"
           class="<?= basename($_SERVER['PHP_SELF']) === 'dashboards.php' ? 'active' : '' ?>">
            Dashboard
        </a>

        <span class="nav-section">Store</span>
        <a href="/ecommerce_sales_analysis/php/admin/order.php"
           class="<?= basename($_SERVER['PHP_SELF']) === 'order.php' ? 'active' : '' ?>">
            Orders
        </a>
        <a href="/ecommerce_sales_analysis/php/products/products.php">Products</a>
        <a href="/ecommerce_sales_analysis/php/admin/add-product.php">Add Product</a>

        <span class="nav-section">Users</span>
        <a href="/ecommerce_sales_analysis/php/admin/manage-users.php">Manage Users</a>

        <span class="nav-section">System</span>
        <a href="/ecommerce_sales_analysis/php/admin/setting.php">Settings</a>

        <a href="/ecommerce_sales_analysis/php/auth/logout.php" class="logout">
            Logout
        </a>

    </nav>

</aside>

    
</body>
</html>