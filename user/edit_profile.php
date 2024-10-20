<?php
session_start();
require "../config.php";

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
	header("Location: ../index.php?page=login");
	exit();
}

$user_id = $_SESSION["user_id"];
$error = "";
$success = "";

// Fetch current user info
$stmt_user = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$name = $_POST["name"];
	$email = $_POST["email"];
	$password = $_POST["password"];
	$confirm_password = $_POST["confirm_password"];

	// Update profile
	if ($password && $password === $confirm_password) {
		// Update name, email, and password
		$hashed_password = password_hash($password, PASSWORD_DEFAULT);
		$stmt_update = $pdo->prepare(
			"UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?",
		);
		$stmt_update->execute([$name, $email, $hashed_password, $user_id]);
		$success = "Profile and password updated successfully!";
	} elseif (!$password) {
		// Update name and email only
		$stmt_update = $pdo->prepare(
			"UPDATE users SET name = ?, email = ? WHERE id = ?",
		);
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
<body class="min-h-screen bg-gray-900 flex items-center justify-center">
    <div class="w-full max-w-md bg-gray-800 p-8 rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold text-white mb-6 text-center">Edit Profile</h1>

        <!-- Success or Error Messages -->
        <?php if ($error): ?>
            <p class="text-red-500 mb-4"><?= htmlspecialchars($error) ?></p>
        <?php elseif ($success): ?>
            <p class="text-green-500 mb-4"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form action="" method="post">
            <div class="mb-4">
                <label class="block text-gray-300 mb-2">Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($user["name"]) ?>" 
                    required class="w-full p-3 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            <div class="mb-4">
                <label class="block text-gray-300 mb-2">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user["email"]) ?>" 
                    required class="w-full p-3 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            <div class="mb-4">
                <label class="block text-gray-300 mb-2">New Password</label>
                <input type="password" name="password" 
                    class="w-full p-3 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            <div class="mb-4">
                <label class="block text-gray-300 mb-2">Confirm New Password</label>
                <input type="password" name="confirm_password" 
                    class="w-full p-3 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
            </div>

            <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white py-3 px-6 rounded-lg">
                Update Profile
            </button>
        </form>
    </div>
</body>
</html>
