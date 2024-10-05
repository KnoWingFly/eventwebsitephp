<?php
session_start();
require '../config.php';

// Check if the admin is logged in
if ($_SESSION['role'] != 'admin') {
    header('Location: ../index.php?page=login');
    exit;
}

// Get the user ID from the URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if the user ID is valid
if ($user_id > 0) {
    // Delete all registrations associated with the user
    $stmt_registrations = $pdo->prepare("DELETE FROM registrations WHERE user_id = ?");
    $stmt_registrations->execute([$user_id]);

    // Delete the user from the database
    $stmt_user = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt_user->execute([$user_id]);

    // Redirect to the user management page with a success message
    header('Location: dashboard.php?message=User deleted successfully');
    exit;
} else {
    // Redirect back if no valid user ID is provided
    header('Location: views_user.php?error=Invalid user ID');
    exit;
}
