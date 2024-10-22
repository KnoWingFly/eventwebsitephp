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
    <link href="../css/output.css" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        .sidebar {
            width: 3rem;
            transition: width 0.3s ease;
            overflow: hidden;
            background-color: #1e1e1e;
        }
        .sidebar:hover {
            width: 16rem;
        }
        .sidebar-menu {
            display: flex;
            flex-direction: column;
            height: 100vh;
            padding: 1rem 0;
        }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #a0a0a0;
            transition: color 0.3s ease;
        }
        .sidebar-menu a:hover {
            color: #ffffff;
        }
        .sidebar-menu i {
            font-size: 1.25rem;
            min-width: 1rem;
            display: flex;
            justify-content: center;
        }
        .link-text {
            margin-left: 1rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .sidebar:hover .link-text {
            opacity: 1;
        }
        .logout-link {
            margin-top: auto;
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-open {
            background-color: green;
        }

        .status-closed {
            background-color: orange;
        }

        .status-canceled {
            background-color: red;
        }

        /* Add margin between action buttons */
        .action-buttons a {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body class="bg-gray-900 text-white">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="sidebar">
            <nav class="sidebar-menu">
                <a href="create_event.php" class="tooltip tooltip-right" data-tip="Create Event">
                    <i class="ph ph-plus-circle"></i>
                    <span class="link-text">Create Event</span>
                </a>
                <a href="views_user.php" class="tooltip tooltip-right" data-tip="User Views">
                    <i class="ph ph-eye"></i>
                    <span class="link-text">User Views</span>
                </a>
                <a href="manage_user.php" class="tooltip tooltip-right" data-tip="User Management">
                    <i class="ph ph-user-gear"></i>
                    <span class="link-text">User Management</span>
                </a>
                <a href="../index.php?page=logout" class="logout-link tooltip tooltip-right" data-tip="Logout">
                    <i class="ph ph-sign-out"></i>
                    <span class="link-text">Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-4">
            <header class="mb-8">
                <h1 class="text-2xl font-bold">Event Manager - Admin Dashboard</h1>
            </header>

            <div class="overflow-x-auto bg-base-100 rounded-box shadow-xl">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Registrants</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= htmlspecialchars($event["name"]) ?></td>
                            <td><?= htmlspecialchars($event["event_date"]) ?></td>
                            <td><?= htmlspecialchars($event["registrants"]) ?> / <?= htmlspecialchars($event["max_participants"]) ?></td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <!-- Status Indicator with custom CSS -->
                                    <span class="status-indicator 
                                        <?= ($event['status'] == 'open') ? 'status-open' : (($event['status'] == 'closed') ? 'status-closed' : 'status-canceled') ?>">
                                    </span>
                                    <span><?= htmlspecialchars(ucfirst($event["status"])) ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="flex gap-2 action-buttons">
                                    <a href="registrants.php?event_id=<?= $event["id"] ?>" class="btn btn-sm btn-info">
                                        <i class="ph ph-users"></i> View
                                    </a>
                                    <a href="edit_event.php?id=<?= $event["id"] ?>" class="btn btn-sm btn-warning">
                                        <i class="ph ph-pencil"></i> Edit
                                    </a>
                                    <button onclick="openDeleteModal(<?= $event['id'] ?>)" class="btn btn-sm btn-error">
                                        <i class="ph ph-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <dialog id="deleteModal" class="modal">
        <form method="dialog" class="modal-box">
            <h3 class="font-bold text-lg">Confirm Deletion</h3>
            <p class="py-4">Are you sure you want to delete this event?</p>
            <div class="modal-action">
                <button class="btn btn-ghost" onclick="closeDeleteModal()">Cancel</button>
                <button id="confirmDelete" class="btn btn-error">Delete</button>
            </div>
        </form>
    </dialog>

    <script>
        let deleteEventId = null;

        function openDeleteModal(eventId) {
            deleteEventId = eventId;
            document.getElementById('deleteModal').showModal();
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').close();
        }

        document.getElementById('confirmDelete').addEventListener('click', function() {
            if (deleteEventId) {
                window.location.href = 'delete_event.php?id=' + deleteEventId;
            }
        });
    </script>
</body>
</html>
