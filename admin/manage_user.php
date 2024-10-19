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
<!-- Error Modal -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-900 text-gray-100 p-4 sm:p-6 md:p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 sm:mb-8">
            <h1 class="text-2xl sm:text-3xl font-bold mb-4 sm:mb-0">User Management</h1>
            <a href="../index.php?page=logout" class="w-full sm:w-auto bg-red-500 hover:bg-red-700 text-white py-2 px-4 rounded text-center">
                Logout
            </a>
        </div>
        <div class="bg-gray-800 rounded-lg shadow-lg overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="py-3 px-4 sm:px-6 text-left">Name</th>
                        <th class="py-3 px-4 sm:px-6 text-left">Email</th>
                        <th class="py-3 px-4 sm:px-6 text-left">Role</th>
                        <th class="py-3 px-4 sm:px-6 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr class="border-b border-gray-700">
                            <td class="py-3 px-4 sm:px-6"><?php echo htmlspecialchars($user['name']); ?></td>
                            <td class="py-3 px-4 sm:px-6"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="py-3 px-4 sm:px-6"><?php echo htmlspecialchars($user['role']); ?></td>
                            <td class="py-3 px-4 sm:px-6">
                                <div class="flex flex-col sm:flex-row sm:space-x-3 space-y-2 sm:space-y-0">
                                    <a href="edit_user.php?id=<?= $user['id'] ?>" class="text-blue-500 hover:text-blue-300 text-center sm:text-left">Edit</a>
                                    <a href="delete_user.php?id=<?= $user['id'] ?>" class="text-red-500 hover:text-red-300 text-center sm:text-left" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>