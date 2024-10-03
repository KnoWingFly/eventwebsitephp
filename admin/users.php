<?php
// user/dashboard.php
session_start();
require '../config.php';

$user_id = $_SESSION['user_id'];

// Fetch registered events for the user
$stmt = $pdo->prepare("SELECT events.* FROM registrations 
                       JOIN events ON registrations.event_id = events.id 
                       WHERE registrations.user_id = ?");
$stmt->execute([$user_id]);
$registered_events = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-10">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Your Registered Events</h1>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                Logout
            </a>
        </div>
        <div class="bg-white shadow-lg rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Events You Have Registered</h2>
            <table class="min-w-full bg-white border border-gray-300 rounded-lg">
                <thead>
                    <tr>
                        <th class="py-2 border-b text-left">Event Name</th>
                        <th class="py-2 border-b text-left">Date</th>
                        <th class="py-2 border-b text-left">Location</th>
                        <th class="py-2 border-b text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registered_events as $event): ?>
                    <tr>
                        <td class="py-2 border-b"><?= htmlspecialchars($event['name']) ?></td>
                        <td class="py-2 border-b"><?= htmlspecialchars($event['event_date']) ?></td>
                        <td class="py-2 border-b"><?= htmlspecialchars($event['location']) ?></td>
                        <td class="py-2 border-b">
                            <a href="cancel_registration.php?event_id=<?= $event['id'] ?>" class="text-red-500">Cancel Registration</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>