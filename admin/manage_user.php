<?php
session_start();
require '../config.php';

if ($_SESSION['role'] != 'admin') {
    header('Location: ../index.php?page=login');
    exit;
}

$stmt = $pdo->query("SELECT id, name, email, role FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@3.7.3/dist/full.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-base-300 p-4 sm:p-6 md:p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 sm:mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold mb-4 sm:mb-0">User Management</h1>
            <a href="../index.php?page=logout" class="btn btn-error btn-sm sm:btn-md w-full sm:w-auto hover:brightness-75 transition-all duration-300">
                Logout
            </a>
        </div>
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th class="hidden sm:table-cell">Email</th>
                                <th class="hidden md:table-cell">Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td class="hidden sm:table-cell"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="hidden md:table-cell"><?php echo htmlspecialchars($user['role']); ?></td>
                                    <td>
                                    <div class="flex flex-col sm:flex-row sm:space-x-2 space-y-2 sm:space-y-0">
                                    <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-info btn-xs sm:btn-sm text-white hover:brightness-75 transition-all duration-300">Edit</a>
                                    <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-error btn-xs sm:btn-sm text-white hover:brightness-75 transition-all duration-300" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                    </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>