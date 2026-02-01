<?php
session_start();
require_once "../../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($name && $email && $password) {

        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            $message = "Email already registered!";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password, created_at)
                 VALUES (?, ?, ?, NOW())"
            );

            if ($stmt->execute([$name, $email, $hashedPassword])) {
                header("Location: login.php");
                exit;
            } else {
                $message = "Registration failed!";
            }
        }
    } else {
        $message = "All fields are required!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account</title>
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/register.css">
</head>
<body>

<div class="register-wrapper">

    <!-- LEFT CONTENT -->
    <div class="register-content">
        <h1>OFFERS POWERED<br>BY DESIGNERS<br>AROUND THE WORLD.</h1>

        <p class="subtext">
            Discover fashion curated from global creators.
        </p>

        <p class="login-link">
            Already have an account?
            <a href="login.php">Login →</a>
        </p>
    </div>

    <!-- RIGHT IMAGE + FORM -->
    <div class="register-visual">
        <div class="form-card">

            <h2>Create your account</h2>

            <?php if ($message): ?>
                <div class="error-message"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="name" placeholder="Full name" required>
                <input type="email" name="email" placeholder="Email address" required>
                <input type="password" name="password" placeholder="Password" required>

                <button type="submit">Create account</button>
            </form>

            </div>
        </div>

</div>

</body>
</html>
