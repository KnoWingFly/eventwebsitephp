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
<html lang="en" data-theme="dark"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Admin Dashboard - Event Manager</title> 
    <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet" type="text/css" /> 
    <link href="../css/output.css" rel="stylesheet"> 
</head> 
<body class="bg-base-900 text-white"> 
<div class="container mx-auto p-4"> 

    <!-- Header Section --> 
    <div class="navbar bg-[#6C63FF] text-primary-content rounded-box mb-4"> 
        <div class="flex-1"> 
            <h1 class="text-2xl font-bold">Event Manager - Admin Dashboard</h1> 
        </div> 
        <div class="flex-none"> 
            <a href="../index.php?page=logout" class="btn btn-ghost">Logout</a> 
        </div> 
    </div>

    <!-- Admin Menu Section --> 
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4"> 
        <a href="create_event.php" class="btn bg-[#FF007A] text-white btn-block"> 
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-6 h-6 mr-2 stroke-current"> 
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path> 
            </svg> 
            Add Events 
        </a> 
        <a href="views_user.php" class="btn bg-[#00C9A7] text-white btn-block"> 
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-6 h-6 mr-2 stroke-current"> 
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path> 
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path> 
            </svg> 
            User Views 
        </a> 
        <a href="manage_user.php" class="btn bg-[#6C63FF] text-white btn-block"> 
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-6 h-6 mr-2 stroke-current"> 
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1 .066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path> 
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path> 
            </svg> 
            User Management 
        </a> 
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