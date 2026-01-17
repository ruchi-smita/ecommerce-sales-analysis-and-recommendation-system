<?php
require_once "../../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($name) && !empty($email) && !empty($password)) {

        // check if email already exists
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            $message = "Email already registered!";
        } else {
            // hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password, created_at)
                 VALUES (?, ?, ?, NOW())"
            );

            if ($stmt->execute([$name, $email, $hashedPassword])) {
                $message = "Registration successful!";
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
<html>
<head>
    <title>User Registration</title>
    <link rel="stylesheet" href="/ecommerce_sales_analysis/css/register.css">
</head>
<body>



<?php if ($message): ?>
    <p><?php echo $message; ?></p>
<?php endif; ?>

<form method="POST">
    <h2>Registration Form</h2>
    <label>Name:</label><br>
    <input type="text" name="name" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>
    <p>
    Already have an account?
    <a href="login.php">Login</a>
</p>

    <button type="submit">Register</button>
    
</form>

</body>
</html>
