<?php
$host = "localhost";
$dbname = "ecommerce_db";   // change if your DB name is different
$username = "root";        // default for XAMPP
$password = "";            // default for XAMPP

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>
