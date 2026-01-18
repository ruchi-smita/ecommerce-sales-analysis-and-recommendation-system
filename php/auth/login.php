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
<html>
<head>
    <title>User Login</title>
    <link rel="stylesheet" href="/ecommerce_sales_analysis/assets/css/login.css">
</head>
<body>



<?php if ($message): ?>
    <p><?php echo $message; ?></p>
<?php endif; ?>

<form method="POST">
    <h2>User Login</h2>
    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>
    <p>
    Don’t have an account?
    <a href="register.php">Register here</a>
</p>

    <button type="submit">Login</button>
</form>

</body>
</html>
