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
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        h1, h2 {
            font-family: 'Poppins', sans-serif;
        }

        .bg-purple-800 {
            background-color: #374151;
        }
        
        .bg-gray-800 {
            background-color: #282c34;
        }

        .table-container {
            overflow-x: auto;
        }
        
        .table-container table {
            min-width: 700px;
            width: 100%;
            background-color: #282c34;
            border-radius: 10px;
            overflow: hidden;
            border: none;
        }
        
        .table-container th, 
        .table-container td {
            padding: 1rem;
            text-align: left;
            color: white;
        }
        
        .table-container th {
            background-color: #1a202c;
            font-weight: bold;
        }
        
        .table-container tr:nth-child(even) {
            background-color: #313540;
        }
        
        .table-container tr:hover {
            background-color: #374151;
        }
        
        .sidebar-link .link-text {
            display: none;
        }
        
        .sidebar:hover {
            width: 16rem;
        }
        
        .sidebar:hover .link-text {
            display: block;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                bottom: 0;
                width: 100%;
                height: auto;
                flex-direction: row;
                justify-content: space-around;
                padding: 0.5rem;
            }

            .sidebar:hover {
                width: 100%;
            }

            .sidebar .mb-8 {
                display: none;
            }

            .sidebar nav {
                flex-direction: row;
                gap: 1rem;
            }

            .link-text {
                display: none !important;
            }

            main {
                margin-left: 0 !important;
                margin-bottom: 4rem !important;
                padding: 1rem !important;
            }

            .card-grid {
                grid-template-columns: 1fr;
            }

            .table-container {
                margin: 0 -1rem;
                border-radius: 0;
            }

            .table-container table {
                min-width: 100%;
            }

            .action-buttons {
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            header h1 {
                font-size: 1.5rem;
            }
        }

        /* Card-based layout for mobile */
        @media (max-width: 640px) {
            .table-container table {
                display: block;
                background: transparent;
            }

            .table-container thead {
                display: none;
            }

            .table-container tbody {
                display: block;
            }

            .table-container tr {
                display: block;
                background: #282c34;
                margin-bottom: 1rem;
                border-radius: 0.5rem;
                padding: 1rem;
            }

            .table-container td {
                display: block;
                padding: 0.5rem 0;
                border: none;
            }

            .table-container td:before {
                content: attr(data-label);
                font-weight: bold;
                display: inline-block;
                width: 100%;
                margin-bottom: 0.25rem; 
            }

            .flex.gap-3 {
                justify-content: flex-start;
                margin-top: 0.5rem;
            }
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="sidebar fixed h-full w-16 transition-all duration-300 ease-in-out bg-gray-800 text-white py-4 px-2 flex flex-col z-50">
            <!-- Logo (kalo butuh, kalo ga butuh apus aja) -->
            <div class="mb-8 flex justify-center">
                <div class="w-8 h-8 bg-white rounded-lg"></div>
            </div>
            
            <!-- Navigation Links -->
            <nav class="flex flex-col gap-4">
                <a href="create_event.php" class="sidebar-link flex items-center gap-3 text-gray-300 hover:text-white p-2 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                    <i class="ph ph-plus-circle text-xl"></i>
                    <span class="link-text font-medium">Add Events</span>
                </a>

                <a href="views_user.php" class="sidebar-link flex items-center gap-3 text-gray-300 hover:text-white p-2 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                    <i class="ph ph-users text-xl"></i>
                    <span class="link-text font-medium">User Views</span>
                </a>

                <a href="manage_user.php" class="sidebar-link flex items-center gap-3 text-gray-300 hover:text-white p-2 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                    <i class="ph ph-gear text-xl"></i>
                    <span class="link-text font-medium">User Management</span>
                </a>
            </nav>

            <!-- Logout Link (at bottom) -->
            <a href="../index.php?page=logout" class="sidebar-link mt-auto flex items-center gap-3 text-gray-300 hover:text-white p-2 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                <i class="ph ph-sign-out text-xl"></i>
                <span class="link-text font-medium">Logout</span>
            </a>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-16 p-8">
            <!-- Header -->
            <header class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-white">Event Manager - Admin Dashboard</h1>
            </header>

            <!-- Events Table Card -->
            <div class="table-container bg-gray-800 rounded-xl p-6 shadow-lg">
                <div class="mb-6 flex items-center gap-2">
                    <i class="ph ph-calendar text-xl text-white"></i>
                    <h2 class="text-xl font-semibold text-white">Events Overview</h2>
                </div>
                <table>
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
                            <td data-label="Event Name"><?= htmlspecialchars($event["name"]) ?></td>
                            <td data-label="Date"><?= htmlspecialchars($event["event_date"]) ?></td>
                            <td data-label="Registrants"><?= htmlspecialchars($event["registrants"]) ?> / <?= htmlspecialchars($event["max_participants"]) ?></td>
                            <td data-label="Actions">
                                <div class="flex gap-3">
                                    <button onclick="location.href='registrants.php?event_id=<?= $event["id"] ?>'" 
                                            class="text-blue-400 hover:text-blue-300 transition-colors duration-200 flex items-center gap-1">
                                        <i class="ph ph-users text-lg"></i>
                                        View
                                    </button>
                                    <button onclick="location.href='edit_event.php?id=<?= $event["id"] ?>'"
                                            class="text-green-400 hover:text-green-300 transition-colors duration-200 flex items-center gap-1">
                                        <i class="ph ph-pencil text-lg"></i>
                                        Edit
                                    </button>
                                    <button onclick="if(confirm('Are you sure you want to delete this event?')) location.href='delete_event.php?id=<?= $event["id"] ?>'"
                                            class="text-red-400 hover:text-red-300 transition-colors duration-200 flex items-center gap-1">
                                        <i class="ph ph-trash text-lg"></i>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            </div>
        </main>
    </div>
</body>
</html>

