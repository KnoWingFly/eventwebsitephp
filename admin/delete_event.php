<?php
// admin/delete_event.php
session_start();
require '../config.php';

if ($_SESSION['role'] != 'admin') {
    header('Location: ../index.php?page=login');
    exit;
}

$event_id = $_GET['id'];

// Fetch event details to confirm deletion
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Confirm deletion
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    header('Location: dashboard.php'); 
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Event</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-10">
        <h1 class="text-2xl font-bold mb-6">Delete Event</h1>
        <p>Are you sure you want to delete the event: <strong><?= htmlspecialchars($event['name']) ?></strong>?</p>
        <form action="" method="post">
            <button type="submit" class="bg-red-500 text-white py-2 px-4 rounded">Yes, Delete</button>
            <a href="dashboard.php" class="bg-gray-500 text-white py-2 px-4 rounded">Cancel</a>
        </form>
    </div>
</body>
</html>
