<?php

function catalog_table_exists(PDO $conn, string $tableName): bool
{
    $stmt = $conn->prepare(
        "SELECT COUNT(*)
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = ?"
    );
    $stmt->execute([$tableName]);

    return (int) $stmt->fetchColumn() > 0;
}

function catalog_column_exists(PDO $conn, string $tableName, string $columnName): bool
{
    $stmt = $conn->prepare(
        "SELECT COUNT(*)
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME = ?
           AND COLUMN_NAME = ?"
    );
    $stmt->execute([$tableName, $columnName]);

    return (int) $stmt->fetchColumn() > 0;
}

function ensure_product_catalog_schema(PDO $conn): void
{
    static $schemaReady = false;

    if ($schemaReady) {
        return;
    }

    $schemaReady = true;

    if (!catalog_column_exists($conn, 'products', 'description')) {
        $conn->exec(
            "ALTER TABLE products
             ADD COLUMN description TEXT NULL AFTER image_url"
        );
    }

    if (!catalog_table_exists($conn, 'product_reviews')) {
        $conn->exec(
            "CREATE TABLE product_reviews (
                review_id INT NOT NULL AUTO_INCREMENT,
                product_id INT NOT NULL,
                user_id INT NOT NULL,
                rating TINYINT UNSIGNED NOT NULL,
                title VARCHAR(120) DEFAULT NULL,
                review_text TEXT NOT NULL,
                review_image_url VARCHAR(255) DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (review_id),
                UNIQUE KEY uq_product_reviews_user_product (product_id, user_id),
                KEY idx_product_reviews_product_created (product_id, created_at),
                KEY idx_product_reviews_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        return;
    }

    if (!catalog_column_exists($conn, 'product_reviews', 'title')) {
        $conn->exec(
            "ALTER TABLE product_reviews
             ADD COLUMN title VARCHAR(120) DEFAULT NULL AFTER rating"
        );
    }

    if (!catalog_column_exists($conn, 'product_reviews', 'review_image_url')) {
        $conn->exec(
            "ALTER TABLE product_reviews
             ADD COLUMN review_image_url VARCHAR(255) DEFAULT NULL AFTER review_text"
        );
    }

    if (!catalog_column_exists($conn, 'product_reviews', 'updated_at')) {
        $conn->exec(
            "ALTER TABLE product_reviews
             ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
             ON UPDATE CURRENT_TIMESTAMP AFTER created_at"
        );
    }
}
