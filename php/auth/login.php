<?php
session_start();
require_once "../../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {

        $stmt = $conn->prepare("SELECT user_id, name, password FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // verify password
            if (password_verify($password, $user['password'])) {

                // create session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];

                header("Location: ../../index.php");
                exit;
            } else {
                $message = "Invalid password!";
            }
        } else {
            $message = "Email not found!";
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
    <title>User Login</title>
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/logins.css">
</head>
<body>

<div class="auth-wrapper">

    <!-- LEFT: IMAGE / BRAND -->
    <div class="auth-visual animate-left">
        <div class="overlay"></div>

        <div class="brand">
            <h1>FASHIONLY</h1>
            <p>Wear confidence. Own your style.</p>
        </div>
    </div>

    <!-- RIGHT: OFFSET FORM -->
    <div class="auth-form animate-right">
        <div class="form-inner">

            <h2>Welcome Back</h2>
            <p class="subtitle">Login to your account</p>

            <?php if (!empty($message)): ?>
                <div class="error-message">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit">Login</button>
            </form>

                <p class="register-text">
                    Don’t have an account?
                    <a href="register.php">Create one</a>
                </p>

            </div>
        </div>

    </div>

    <script src="/ecommerce_sales_analysis/assets/js/login.js"></script>
    </body>
    </html>
