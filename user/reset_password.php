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
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-base-300 min-h-screen flex items-center justify-center">

<div class="card w-96 bg-base-100 shadow-xl">
    <div class="card-body">
        <h2 class="card-title justify-center mb-4 text-white">Reset Password</h2>
        <form action="" method="post">
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
            <div class="form-control">
                <label class="label">
                    <span class="label-text text-white">New Password</span>
                </label>
                <input type="password" name="password" required autocomplete="off" class="input input-bordered">
            </div>
            <div class="form-control mt-4">
                <label class="label">
                    <span class="label-text text-white">Confirm Password</span>
                </label>
                <input type="password" name="confirm_password" required autocomplete="off" class="input input-bordered">
            </div>
            <div class="form-control mt-6">
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </div>
        </form>

        <?php if ($error): ?>
            <div class="alert alert-error shadow-lg mt-4">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span><?= $error ?></span>
                </div>
            </div>
        <?php elseif ($success): ?>
            <div class="alert alert-success shadow-lg mt-4">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span><?= $success ?></span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
