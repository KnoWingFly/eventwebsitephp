<?php
session_start();
require '../config.php';

// Check if the admin is logged in
if ($_SESSION['role'] != 'admin') {
    header('Location: ../index.php?page=login');
    exit;
}

// Fetch all events with registrants count
$stmt = $pdo->query("
    SELECT events.*, COUNT(registrations.id) as registrants 
    FROM events 
    LEFT JOIN registrations ON events.id = registrations.event_id 
    GROUP BY events.id
");

$query = "
    SELECT 
        events.*,
        events.max_participants,
        (SELECT COUNT(*) FROM registrations WHERE registrations.event_id = events.id) AS registered_people
    FROM events
";

$closestatus = $pdo->prepare("
    UPDATE events 
    SET status = 'closed' 
    WHERE status = 'open' 
    AND TIMESTAMPDIFF(MINUTE, CONCAT(event_date, ' ', event_time), NOW()) >= 1
");
$closestatus->execute();


$events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-10">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Admin Dashboard</h1>
            <a href="../index.php?page=logout" class="bg-red-500 hover:bg-red-700 text-white py-2 px-4 rounded">
                Logout
            </a>
        </div>
        
        <!-- Admin Menu -->
        <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Admin Menu</h2>
            <ul class="space-y-2">
                <li><a href="create_event.php" class="text-blue-500 hover:underline">Event Management</a></li>
                <li><a href="views_user.php" class="text-blue-500 hover:underline">User Management</a></li>
            </ul>
        </div>

        <!-- Events Overview -->
        <div class="bg-white shadow-lg rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Events Overview</h2>
            <table class="min-w-full bg-white border border-gray-300 rounded-lg">
                <thead>
                    <tr>
                        <th class="py-2 border-b text-left">Event Name</th>
                        <th class="py-2 border-b text-left">Date</th>
                        <th class="py-2 border-b text-left">Registrants</th>
                        <th class="py-2 border-b text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                    <tr>
                        <td class="py-2 border-b"><?= htmlspecialchars($event['name']) ?></td>
                        <td class="py-2 border-b"><?= htmlspecialchars($event['event_date']) ?></td>
                        <td class="py-2 border-b"><?= htmlspecialchars($event['registrants']) ?> / <?= htmlspecialchars($event['max_participants']) ?></td>
                        <td class="py-2 border-b">
                            <a href="registrants.php?event_id=<?= $event['id'] ?>" class="text-blue-500">View Registrants</a> |
                            <a href="edit_event.php?id=<?= $event['id'] ?>" class="text-blue-500">Edit</a> |
                            <a href="delete_event.php?id=<?= $event['id'] ?>" class="text-red-500" onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
