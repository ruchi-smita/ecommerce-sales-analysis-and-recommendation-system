<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>header</title>
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/dashboard.css">
</head>
<body>

<header class="dashboard-header">
    <div>
        <h1><?= $pageTitle ?? 'Dashboard'; ?></h1>
        <p><?= $pageSubtitle ?? ''; ?></p>
    </div>

    <div class="admin-actions">
        <input type="search" placeholder="Search admin...">
        <span class="admin-name">Admin</span>
    </div>
</header>

    
</body>
</html>
