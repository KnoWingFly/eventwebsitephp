<?php
session_start();
require '../config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?page=login');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch current user info
$stmt_user = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Update profile
    if ($password && $password === $confirm_password) {
        // Update name, email, and password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt_update = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
        $stmt_update->execute([$name, $email, $hashed_password, $user_id]);
        $success = "Profile and password updated successfully!";
    } elseif (!$password) {
        // Update name and email only
        $stmt_update = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt_update->execute([$name, $email, $user_id]);
        $success = "Profile updated successfully!";
    } else {
        $error = "Passwords do not match!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-10">
        <h1 class="text-2xl font-bold mb-6">Edit Profile</h1>

        <!-- Display success or error message -->
        <?php if ($error): ?>
            <p class="text-red-500"><?= htmlspecialchars($error) ?></p>
        <?php elseif ($success): ?>
            <p class="text-green-500"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <!-- Profile Update Form -->
        <form action="" method="post" class="bg-white shadow-lg rounded-lg p-6">
            <div class="mb-6">
                <label class="text-gray-700">Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="w-full p-3 mt-2 bg-gray-200 rounded-lg">
            </div>
            <div class="mb-6">
                <label class="text-gray-700">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="w-full p-3 mt-2 bg-gray-200 rounded-lg">
            </div>
            <div class="mb-6">
                <label class="text-gray-700">New Password (leave blank if you don't want to change)</label>
                <input type="password" name="password" class="w-full p-3 mt-2 bg-gray-200 rounded-lg">
            </div>
            <div class="mb-6">
                <label class="text-gray-700">Confirm New Password</label>
                <input type="password" name="confirm_password" class="w-full p-3 mt-2 bg-gray-200 rounded-lg">
            </div>
            <button type="submit" class="bg-teal-500 hover:bg-teal-600 text-white py-3 px-6 rounded-lg">Update Profile</button>
        </form>
    </div>
</body>
</html>
