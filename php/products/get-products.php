<?php
require_once "../../config/database.php";

try {
    $stmt = $conn->prepare("
        SELECT 
            product_id,
            name,
            main_category,
            sub_category,
            gender,
            price,
            stock,
            description,
            image_url
        FROM products
        WHERE stock > 0
    ");
    $stmt->execute();

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($products);

} catch (Exception $e) {
    echo json_encode([
        "error" => "Failed to fetch products"
    ]);
}
?>
