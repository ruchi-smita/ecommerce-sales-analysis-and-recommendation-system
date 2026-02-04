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
    <title>FASHIONLY | Home</title>
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/style.css">
</head>

<body>

<?php include "includes/navbar.php"; ?>

<!-- HERO / WELCOME SECTION -->
<?php include "includes/header.php"; ?>

<?php include "php/products/trending.php"; ?>


<!-- CATEGORIES (NOW CONNECTED TO search.php LIKE YOU WANTED) -->
<?php include "php/products/categories.php"; ?>


<?php include "includes/footer.php"; ?>

</body>
</html>
