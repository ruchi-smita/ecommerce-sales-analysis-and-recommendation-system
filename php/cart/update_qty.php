<?php
session_start();

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if ($id && isset($_SESSION['cart'][$id])) {
    if ($action === 'inc') {
        $_SESSION['cart'][$id]++;
    } elseif ($action === 'dec' && $_SESSION['cart'][$id] > 1) {
        $_SESSION['cart'][$id]--;
    }
}

header("Location: cart.php");
exit;
?>