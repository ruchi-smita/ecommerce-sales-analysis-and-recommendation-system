<?php
session_start();
require_once "../../config/database.php";

/*
|--------------------------------------------------------------------------
| (Optional) Admin check
|--------------------------------------------------------------------------
| Uncomment later when login is implemented
*/
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied");
}

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
<html lang="en">
<head> 
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../../assets/css/manage-user.css">
</head>
<body>

<div class="admin-wrapper">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <h1>Manage Users</h1>
        <p>View, update roles, and manage registered users.</p>
    </div>

    <!-- USERS TABLE -->
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Email</th>
                <th>Role</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>

                <td>#<?php echo $user['user_id']; ?></td>

                <td>
                    <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                    <?php if ($user['user_id'] == 1): ?>
                        <span class="badge">Super Admin</span>
                    <?php endif; ?>
                </td>

                <td><?php echo htmlspecialchars($user['email']); ?></td>

                <td>
                    <?php if ($user['user_id'] != 1): ?>
                        <form method="POST" class="inline-form">
                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                            <select name="role">
                                <option value="customer" <?= $user['role']==='customer'?'selected':''; ?>>
                                    Customer
                                </option>
                                <option value="admin" <?= $user['role']==='admin'?'selected':''; ?>>
                                    Admin
                                </option>
                            </select>
                            <button type="submit" name="change_role">
                                Update
                            </button>
                        </form>
                    <?php else: ?>
                        <em>Admin</em>
                    <?php endif; ?>
                </td>

                <td>
                    <?php echo date("d M Y", strtotime($user['created_at'])); ?>
                </td>

                <td>
                    <?php if ($user['user_id'] != 1): ?>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                            <button type="submit" name="delete_user" class="danger">
                                Delete
                            </button>
                        </form>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>

            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="back-link">
        <a href="dashboards.php">← Back to Dashboard</a>
    </div>

</div>

</body>
</html>
