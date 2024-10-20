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

=======
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
>>>>>>> b43fc485fc4f889b2afea78291fc9b5639bf80fe
