<?php
session_start();
require '../config.php';

if ($_SESSION['role'] != 'admin') {
    header('Location: ../index.php?page=login');
    exit;
}

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
    <!-- Navbar -->
    <nav class="bg-blue-600 text-white px-4 py-3 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <a href="dashboard.php" class="text-2xl font-bold">Event System</a>
            <div class="w-1/3">
                <input id="search-bar" type="text" placeholder="Search events..." class="w-full p-2 rounded-lg text-black focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="space-x-6">
                <a href="dashboard.php" class="hover:underline">Manage Events</a>
                <a href="users.php" class="hover:underline">Users Management</a>
            </div>
            <a href="../index.php?page=logout" class="bg-red-500 hover:bg-red-700 text-white px-4 py-2 rounded-lg">Logout</a>
        </div>
    </nav>

    <!-- Events Table -->
    <div class="container mx-auto p-10 bg-white shadow-lg rounded-lg">
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
                    <td class="py-2 border-b"><?= $event['registrants'] ?></td>
                    <td class="py-2 border-b">
                        <a href="edit_event.php?id=<?= $event['id'] ?>" class="text-blue-500">Edit</a> |
                        <a href="delete_event.php?id=<?= $event['id'] ?>" class="text-red-500" onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
