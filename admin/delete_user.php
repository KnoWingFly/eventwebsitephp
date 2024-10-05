<?php
session_start();
require "../config.php";

if ($_SESSION["role"] != "admin") {
	header("Location: ../index.php?page=login");
	exit();
}

$user_id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

if ($user_id > 0) {
	$stmt_registrations = $pdo->prepare(
		"DELETE FROM registrations WHERE user_id = ?",
	);
	$stmt_registrations->execute([$user_id]);

	$stmt_user = $pdo->prepare("DELETE FROM users WHERE id = ?");
	$stmt_user->execute([$user_id]);

	header("Location: dashboard.php?message=User deleted successfully");
	exit();
} else {
	header("Location: views_user.php?error=Invalid user ID");
	exit();
}
