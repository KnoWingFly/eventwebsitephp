<?php
session_start();
require '../config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?page=login');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user profile information
$stmt_user = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Fetch user event registration history
$stmt_history = $pdo->prepare("
    SELECT events.name, events.event_date, events.location 
    FROM registrations 
    JOIN events ON registrations.event_id = events.id 
    WHERE registrations.user_id = ?
");
$stmt_history->execute([$user_id]);
$registered_events = $stmt_history->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-10">
        <h1 class="text-2xl font-bold mb-6">User Profile</h1>

        <!-- User Info -->
        <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Profile Information</h2>
            <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <a href="edit_profile.php" class="text-blue-500 hover:underline mt-4 inline-block">Edit Profile</a>
        </div>

        <!-- Event Registration History -->
        <div class="bg-white shadow-lg rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Event Registration History</h2>
            <table class="min-w-full bg-white border border-gray-300 rounded-lg">
                <thead>
                    <tr>
                        <th class="py-2 border-b text-left">Event Name</th>
                        <th class="py-2 border-b text-left">Date</th>
                        <th class="py-2 border-b text-left">Location</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registered_events as $event): ?>
                    <tr>
                        <td class="py-2 border-b"><?= htmlspecialchars($event['name']) ?></td>
                        <td class="py-2 border-b"><?= htmlspecialchars($event['event_date']) ?></td>
                        <td class="py-2 border-b"><?= htmlspecialchars($event['location']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
