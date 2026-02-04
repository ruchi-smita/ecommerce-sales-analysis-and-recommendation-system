<?php
session_start();
require_once "../../config/database.php";

/* ================= ADMIN CHECK ================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

/* ================= PAGE META ================= */
$pageTitle = "Settings";
$pageSubtitle = "Manage store and admin preferences";

/* ================= ADMIN INFO ================= */
$adminName  = $_SESSION['user_name'] ?? 'Admin';
$adminEmail = $_SESSION['user_email'] ?? 'admin@example.com';

/* ================= BASIC STATS (OPTIONAL) ================= */
$totalUsers  = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalOrders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Settings</title>
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/dashboard.css">
</head>
<body>

<div class="admin-layout">

    <!-- SIDEBAR (OUTSIDE) -->
    <?php include __DIR__ . "\sidebar.php"; ?>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <!-- HEADER -->
        <?php include __DIR__ . "\ad-header.php"; ?>

        <!-- ================= ACCOUNT SETTINGS ================= -->
        <section class="panel">
            <h3>Admin Account</h3>

            <table class="data-table">
                <tbody>
                    <tr>
                        <td><strong>Name</strong></td>
                        <td><?= htmlspecialchars($adminName); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Email</strong></td>
                        <td><?= htmlspecialchars($adminEmail); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Role</strong></td>
                        <td>Administrator</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- ================= STORE INFO ================= -->
        <section class="panel">
            <h3>Store Information</h3>

            <table class="data-table">
                <tbody>
                    <tr>
                        <td><strong>Store Name</strong></td>
                        <td>FASHIONLY</td>
                    </tr>
                    <tr>
                        <td><strong>Total Users</strong></td>
                        <td><?= $totalUsers; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Total Orders</strong></td>
                        <td><?= $totalOrders; ?></td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- ================= SECURITY ================= -->
        <section class="panel">
            <h3>Security</h3>

            <div class="settings-actions">
                <a href="/ecommerce_sales_analysis/php/logout.php" class="danger-link">
                    Logout from Admin
                </a>

                <p class="hint">
                    Password change and 2FA can be added later.
                </p>
            </div>
        </section>

        <!-- ================= SYSTEM INFO ================= -->
        <section class="panel">
            <h3>System Information</h3>

            <table class="data-table">
                <tbody>
                    <tr>
                        <td><strong>PHP Version</strong></td>
                        <td><?= phpversion(); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Server</strong></td>
                        <td><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Environment</strong></td>
                        <td>Local Development</td>
                    </tr>
                </tbody>
            </table>
        </section>

    </main>

</div>

</body>
</html>
