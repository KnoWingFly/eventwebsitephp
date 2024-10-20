<?php
session_start();
require '../config.php';

// Ensure the user is an admin
if ($_SESSION['role'] != 'admin') {
    header('Location: ../index.php?page=login');
    exit;
}

// Fetch user data based on the ID passed via GET
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no user found, redirect to user management page
    if (!$user) {
        header('Location: manage_user.php');
        exit;
    }
} else {
    header('Location: manage_user.php');
    exit;
}

// Handle form submission for updating user data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Update the user's information in the database
    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
    $success = $stmt->execute([$name, $email, $role, $user_id]);

    // Redirect back to management page after update
    if ($success) {
        header('Location: manage_user.php?message=success');
        exit;
    } else {
        $error_message = "Failed to update user. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="../css/output.css" rel="stylesheet">
    <!-- <link href="https://cdn.jsdelivr.net/npm/daisyui@3.7.3/dist/full.css" rel="stylesheet" type="text/css" /> -->
</head>
<body class="min-h-screen bg-base-300 p-4 sm:p-6 md:p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl sm:text-3xl font-bold mb-6 text-white">Edit User</h1>

        <!-- Display error message if any -->
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error mb-4">
                <div>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <div class="card bg-base-100 shadow-xl">
            <div class="card-body space-y-6">
                <form method="POST" class="space-y-4">
                    <div class="form-control">
                        <label class="label text-white" for="name">Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="input input-bordered w-full" required>
                    </div>

                    <div class="form-control">
                        <label class="label text-white" for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="input input-bordered w-full" required>
                    </div>

                    <div class="form-control">
                        <label class="label text-white" for="role">Role</label>
                        <select id="role" name="role" class="select select-bordered w-full" required>
                            <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                        </select>
                    </div>

                    <div class="form-control flex justify-end space-x-4">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="manage_user.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
