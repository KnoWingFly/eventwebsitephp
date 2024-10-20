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
		$hashed_password = password_hash($password, PASSWORD_DEFAULT);
		$stmt_update = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
		$stmt_update->execute([$name, $email, $hashed_password, $user_id]);
		$success = "Profile and password updated successfully!";
	} elseif (!$password) {
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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="../css/output.css" rel="stylesheet">
</head>
<body class="flex justify-center items-center min-h-screen bg-gray-900">
    <div class="bg-gray-800 rounded-lg shadow-lg p-8 max-w-md w-full">
        <h1 class="text-white text-center font-semibold text-2xl mb-6">Edit Profile</h1>

        <!-- Display error or success messages -->
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-500 text-white rounded-lg">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php elseif ($success): ?>
            <div class="mb-4 p-3 bg-green-500 text-white rounded-lg">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-4">
                <label for="name" class="block text-gray-300 mb-1">Name</label>
                <input type="text" name="name" id="name" required value="<?= htmlspecialchars($user["name"]) ?>" 
                    class="block w-full rounded-lg p-3 bg-gray-700 text-white focus:ring-purple-500 focus:ring-2 focus:outline-none">
            </div>
            
            <div class="mb-4">
                <label for="email" class="block text-gray-300 mb-1">Email</label>
                <input type="email" name="email" id="email" required value="<?= htmlspecialchars($user["email"]) ?>" 
                    class="block w-full rounded-lg p-3 bg-gray-700 text-white focus:ring-purple-500 focus:ring-2 focus:outline-none">
            </div>

            <div class="mb-4">
                <label for="password" class="block text-gray-300 mb-1">New Password</label>
                <input type="password" name="password" id="password" 
                    class="block w-full rounded-lg p-3 bg-gray-700 text-white focus:ring-purple-500 focus:ring-2 focus:outline-none">
            </div>

            <div class="mb-4">
                <label for="confirm_password" class="block text-gray-300 mb-1">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" 
                    class="block w-full rounded-lg p-3 bg-gray-700 text-white focus:ring-purple-500 focus:ring-2 focus:outline-none">
            </div>

            <button type="submit" class="w-full p-3 bg-purple-600 hover:bg-purple-700 rounded-lg text-white font-semibold">
                Update Profile
            </button>
        </form>
    </div>
</body>
</html>
