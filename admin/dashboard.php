<?php
session_start();
require "../config.php";

if ($_SESSION["role"] != "admin") {
	header("Location: ../index.php?page=login");
	exit();
}

$stmt = $pdo->query("
    SELECT events.*, COUNT(registrations.id) as registrants 
    FROM events 
    LEFT JOIN registrations ON events.id = registrations.event_id 
    GROUP BY events.id
");

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
    <title>Admin Dashboard - Event Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }
        h1, h2 {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Container -->
    <div class="container mx-auto p-8">
        
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-10">
            <h1 class="text-4xl font-bold text-white">Event Manager - Admin Dashboard</h1>
            <a href="../index.php?page=logout" class="bg-gradient-to-r from-pink-500 to-red-500 hover:from-pink-600 hover:to-red-600 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition-all ease-in-out duration-200">
                Logout
            </a>
        </div>

        <!-- Admin Menu Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white shadow-lg rounded-lg p-6 text-center hover:shadow-xl transition-all duration-200">
                <a href="create_event.php" class="block text-blue-600 font-semibold text-lg hover:text-blue-700 transition duration-200">
                    ➕ Add Events
                </a>
            </div>
            <div class="bg-white shadow-lg rounded-lg p-6 text-center hover:shadow-xl transition-all duration-200">
                <a href="views_user.php" class="block text-blue-600 font-semibold text-lg hover:text-blue-700 transition duration-200">
                    👥 User Views
                </a>
            </div>
            <div class="bg-white shadow-lg rounded-lg p-6 text-center hover:shadow-xl transition-all duration-200">
                <a href="manage_user.php" class="block text-blue-600 font-semibold text-lg hover:text-blue-700 transition duration-200">
                    ⚙️ User Management
                </a>
            </div>
        </div>

        <!-- Events Overview Section -->
        <div class="bg-white shadow-lg rounded-lg p-8">
            <h2 class="text-3xl font-semibold text-gray-800 mb-6">📅 Events Overview</h2>
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead class="bg-blue-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left py-4 px-6 text-sm font-semibold text-gray-600">Event Name</th>
                        <th class="text-left py-4 px-6 text-sm font-semibold text-gray-600">Date</th>
                        <th class="text-left py-4 px-6 text-sm font-semibold text-gray-600">Registrants</th>
                        <th class="text-left py-4 px-6 text-sm font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                    <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-200">
                        <td class="py-4 px-6 text-sm text-gray-700"><?= htmlspecialchars($event["name"]) ?></td>
                        <td class="py-4 px-6 text-sm text-gray-700"><?= htmlspecialchars($event["event_date"]) ?></td>
                        <td class="py-4 px-6 text-sm text-gray-700">
                            <?= htmlspecialchars($event["registrants"]) ?> / <?= htmlspecialchars($event["max_participants"]) ?>
                        </td>
                        <td class="py-4 px-6 text-sm">
                            <a href="registrants.php?event_id=<?= $event["id"] ?>" class="text-blue-500 hover:underline hover:text-blue-600 transition duration-200">View Registrants</a> |
                            <a href="edit_event.php?id=<?= $event["id"] ?>" class="text-blue-500 hover:underline hover:text-blue-600 transition duration-200">Edit</a> |
                            <a href="delete_event.php?id=<?= $event["id"] ?>" class="text-red-500 hover:underline hover:text-red-600 transition duration-200" onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
