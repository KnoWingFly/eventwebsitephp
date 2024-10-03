<?php
// admin/dashboard.php
session_start();
require '../config.php';

// Check if the admin is logged in
if ($_SESSION['role'] != 'admin') {
    header('Location: ../index.php?page=login');
    exit;
}

// Fetch all events with registrants count
$stmt = $pdo->query("SELECT events.*, COUNT(registrations.id) as registrants 
                     FROM events 
                     LEFT JOIN registrations ON events.id = registrations.event_id 
                     GROUP BY events.id");
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
            <a href="../index.php?page=logout" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                Logout
            </a>
        </div>
        
        <!-- Button to Create New Event -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Events Overview</h2>
            <a href="create_event.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                + Add New Event
            </a>
        </div>
        
        <!-- Events Table -->
        <div class="bg-white shadow-lg rounded-lg p-6">
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
                        <td class="py-2 border-b"><?= $event['registrants'] ?></td>
                        <td class="py-2 border-b">
                            <!-- Edit Link -->
                            <a href="edit_event.php?id=<?= $event['id'] ?>" class="text-blue-500">Edit</a> |
                            
                            <!-- Delete Link -->
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
