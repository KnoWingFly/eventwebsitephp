<?php 
session_start(); 
require "../config.php"; 

if ($_SESSION["role"] != "admin") { 
    header("Location: ../index.php?page=login"); 
    exit(); 
} 

$stmt = $pdo->query(" 
    SELECT events.*, COUNT(registrations.id) AS registrants 
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
    <div class="card bg-base-900 shadow-xl"> 
        <div class="card-body"> 
            <h2 class="card-title text-2xl mb-4">📅 Events Overview</h2> 
            <div class="overflow-x-auto"> 
                <table class="table w-full table-zebra"> 
                    <thead> 
                        <tr> 
                            <th>Event Name</th> 
                            <th>Date</th> 
                            <th>Registrants</th> 
                            <th>Actions</th> 
                        </tr> 
                    </thead> 
                    <tbody> 
                        <?php foreach ($events as $event): ?> 
                        <tr> 
                            <td><?= htmlspecialchars($event["name"]) ?></td> 
                            <td><?= htmlspecialchars($event["event_date"]) ?></td> 
                            <td> 
                                <div class="badge bg-[#6C63FF] text-white"><?= htmlspecialchars($event["registrants"]) ?> / <?= htmlspecialchars($event["max_participants"]) ?></div> 
                            </td> 
                            <td> 
                                <div class="btn-group"> 
                                    <a href="registrants.php?event_id=<?= $event["id"] ?>" class="btn btn-sm bg-[#00C9A7] text-white">View</a> 
                                    <a href="edit_event.php?id=<?= $event["id"] ?>" class="btn btn-sm bg-[#FFD700] text-black">Edit</a> 
                                    <a href="delete_event.php?id=<?= $event["id"] ?>" class="btn btn-sm bg-[#FF007A] text-white" onclick="return confirm('Are you sure you want to delete this event?')">Delete</a> 
                                </div> 
                            </td> 
                        </tr> 
                        <?php endforeach; ?> 
                    </tbody> 
                </table> 
            </div> 
        </div> 
    </div> 
</div> 
</body> 
</html>
