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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link href="../css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-10">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">User Management</h1>
            <a href="../index.php?page=logout" class="bg-red-500 hover:bg-red-700 text-white py-2 px-4 rounded">
                Logout
            </a>
        </div>

        <div class="bg-white shadow-lg rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">All Users</h2>
            <table class="min-w-full bg-white border border-gray-300 rounded-lg">
                <thead>
                    <tr>
                        <th class="py-2 border-b text-left">name</th>
                        <th class="py-2 border-b text-left">Email</th>
                        <th class="py-2 border-b text-left">Role</th>
                        <th class="py-2 border-b text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="py-2 border-b"><?= htmlspecialchars($user['name']) ?></td>
                        <td class="py-2 border-b"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="py-2 border-b"><?= htmlspecialchars($user['role']) ?></td>
                        <td class="py-2 border-b">
                            <a href="edit_user.php?id=<?= $user['id'] ?>" class="text-blue-500">Edit</a> |
                            <a href="delete_user.php?id=<?= $user['id'] ?>" class="text-red-500" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
