<?php
require __DIR__ . '/../config.php';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password === $confirm_password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
            if ($stmt->execute([$hashed_password, $token])) {
                $success = "Password successfully updated! You can now log in.";
            } else {
                $error = "Failed to update password!";
            }
        } else {
            $error = "Invalid or expired token!";
        }
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
    <title>Reset Password</title>
    <link href="../css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-300 font-sans">
<div class="max-w-lg mx-auto mt-10 bg-gray-800 rounded-lg shadow-lg">
    <div class="p-6">
        <h1 class="text-3xl text-center text-white mb-6">Reset Password</h1>
        <form action="" method="post">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
            <div class="mt-6">
                <label class="text-gray-400">New Password<span class="text-red-500">*</span></label>
                <input type="password" name="password" required autocomplete="off" class="w-full p-3 mt-2 bg-gray-700 text-white border border-gray-600 rounded focus:outline-none focus:border-teal-500">
            </div>
            <div class="mt-6">
                <label class="text-gray-400">Confirm Password<span class="text-red-500">*</span></label>
                <input type="password" name="confirm_password" required autocomplete="off" class="w-full p-3 mt-2 bg-gray-700 text-white border border-gray-600 rounded focus:outline-none focus:border-teal-500">
            </div>
            <button type="submit" class="mt-6 w-full py-3 bg-teal-500 text-white rounded-lg hover:bg-teal-600">Reset Password</button>
        </form>

        <?php if ($error): ?>
            <p class="mt-4 text-red-500 text-center"><?= $error; ?></p>
        <?php elseif ($success): ?>
            <p class="mt-4 text-green-500 text-center"><?= $success; ?></p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
