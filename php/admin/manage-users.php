<?php
session_start();
require_once "../../config/database.php";

/*
|--------------------------------------------------------------------------
| (Optional) Admin check
|--------------------------------------------------------------------------
| Uncomment later when login is implemented
*/
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     die("Access denied");
// }

/*
|--------------------------------------------------------------------------
| Handle role update
|--------------------------------------------------------------------------
*/
if (isset($_POST['change_role'])) {
    $userId = (int) $_POST['user_id'];
    $newRole = $_POST['role'];

    if (in_array($newRole, ['admin', 'customer'])) {
        $stmt = $conn->prepare(
            "UPDATE users SET role = ? WHERE user_id = ?"
        );
        $stmt->execute([$newRole, $userId]);
    }
}

/*
|--------------------------------------------------------------------------
| Handle delete user
|--------------------------------------------------------------------------
*/
if (isset($_POST['delete_user'])) {
    $userId = (int) $_POST['user_id'];

    // Prevent deleting self (basic safety)
    if ($userId !== 1) {
        $stmt = $conn->prepare(
            "DELETE FROM users WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
    }
}

/*
|--------------------------------------------------------------------------
| Fetch users
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare(
    "SELECT user_id, name, email, role, created_at 
     FROM users 
     ORDER BY created_at DESC"
);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>

<h2>👤 Manage Users</h2>

<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Created</th>
        <th>Actions</th>
    </tr>

    <?php foreach ($users as $user) { ?>
        <tr>
            <td><?php echo $user['user_id']; ?></td>
            <td><?php echo htmlspecialchars($user['name']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                    <select name="role">
                        <option value="customer" <?php if ($user['role'] === 'customer') echo 'selected'; ?>>
                            Customer
                        </option>
                        <option value="admin" <?php if ($user['role'] === 'admin') echo 'selected'; ?>>
                            Admin
                        </option>
                    </select>
                    <button type="submit" name="change_role">Update</button>
                </form>
            </td>
            <td><?php echo $user['created_at']; ?></td>
            <td>
                <?php if ($user['user_id'] != 1) { ?>
                    <form method="POST" onsubmit="return confirm('Delete this user?');">
                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                        <button type="submit" name="delete_user">Delete</button>
                    </form>
                <?php } else { ?>
                    Super Admin
                <?php } ?>
            </td>
        </tr>
    <?php } ?>
</table>

<br>
<a href="dashboard.php">⬅ Back to Admin Dashboard</a>

</body>
</html>
