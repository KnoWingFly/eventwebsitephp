<?php
session_start();
require "../config.php";

$user_id = $_SESSION["user_id"];

$stmt_all_events = $pdo->query("SELECT * FROM events");
$all_events = $stmt_all_events->fetchAll();

$closestatus = $pdo->prepare("
    UPDATE events 
    SET status = 'closed' 
    WHERE status = 'open' 
    AND TIMESTAMPDIFF(MINUTE, CONCAT(event_date, ' ', event_time), NOW()) >= 1
");
$stmt_registered_events = $pdo->prepare(
    "SELECT event_id FROM registrations WHERE user_id = ?",
);
$stmt_registered_events->execute([$user_id]);
$registered_events = $stmt_registered_events->fetchAll(PDO::FETCH_COLUMN, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link href="../css/output.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
            overflow-x: hidden;
        }
        
        .container {
            max-width: 1200px; 
            width: 100%;
            margin-left: auto;
            margin-right: auto;
            padding-left: 15px;
            padding-right: 15px;
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

        .card {
            background-color: #282c34;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            position: relative;
            padding: 20px;
            background-color: #2d2d2d; 
        }
        
        .event-banner {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-size: cover;
            background-position: center;
            filter: blur(2px); 
            opacity: 0.6; 
        }

        .card-body {
            padding: 1rem;
        }

        .btn {
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .status-open {
            color: #10B981;
        }

        .status-closed {
            color: #6B7280;
        }

        .status-other {
            color: #EF4444;
        }

        .card {
            color: white;
        }

        .card-header h3 {
            position: relative;
            z-index: 10; 
            color: #fff; 
        }

        .card p {
            color: #e2e8f0; 
        }

        /* Status colors */
        .status-open {
            color: #10B981;
        }

        .status-closed {
            color: #6B7280; 
        }

        .status-other {
            color: #EF4444; 
        }

        /* Button text colors */
        .register-btn {
            color: white; 
        }

        .cancel-btn {
            color: #EF4444;
        }

        .view-details-btn {
            color: #3B82F6; 
        }

        .registered-text {
            color: #10B981;
        }

        .registration-closed-text {
            color: #6B7280;
        }

        .header-container {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            background-color: #1a202c;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            margin-right: 1rem;
        }

        .search-container {
            flex-grow: 1;
            max-width: 600px;
            margin: 0.5rem 1rem;
        }

        .search-input {
            width: 100%;
            padding: 0.5rem;
            border-radius: 0.25rem;
            border: none;
            background-color: #2d3748;
            color: white;
        }

        .nav-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                align-items: stretch;
            }

            .container {
                padding-left: 10px;
                padding-right: 10px;
            }
            .search-container {
                order: 3;
                max-width: none;
                margin: 0.5rem 0;
            }

            .nav-buttons {
                order: 2;
                justify-content: flex-end;
                margin-top: 0.5rem;
            }
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Header -->
    <nav class="w-full bg-gray-800 text-white shadow-lg">
        <div class="container mx-auto flex justify-between items-center px-6 py-4">
            <a href="dashboard.php" class="text-3xl font-bold">Event System</a>
            
            <div class="w-full md:w-1/3">
                <input id="search-bar" type="text" placeholder="Search events..." class="w-full p-2 bg-gray-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 placeholder-gray-400">
            </div>
            <div class="flex space-x-4">
                <a href="profile.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition duration-300">
                    <i class="ph ph-user mr-2"></i>Profile
                </a>
                <a href="../index.php?page=logout" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition duration-300">
                    <i class="ph ph-sign-out mr-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-8">
        <h2 class="text-2xl font-bold text-white mb-8 mt-8">Available Events</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($all_events as $event): ?>
                <div class="card">
                    <div class="card-header relative">
                        <?php if (!empty($event['banner'])): ?>
                            <div class="event-banner" style="background-image: url('../uploads/<?= htmlspecialchars($event['banner']) ?>');"></div>
                        <?php endif; ?>
                        <h3 class="text-xl font-bold relative z-10"><?= htmlspecialchars($event["name"]) ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="flex items-center mb-2">
                            <i class="ph ph-calendar text-xl text-white mr-2"></i>
                            <p class="text-sm"><?= htmlspecialchars($event["event_date"]) ?></p>
                        </div>
                        <div class="flex items-center mb-2">
                            <i class="ph ph-map-pin text-xl text-white mr-2"></i>
                            <p class="text-sm"><?= htmlspecialchars($event["location"]) ?></p>
                        </div>
                        <p class="text-sm font-semibold status-<?= $event["status"] ?> mb-4">
                            Status: <?= ucfirst(htmlspecialchars($event["status"])) ?>
                        </p>
                        <div class="flex justify-between items-center">
                            <button class="view-details-btn btn" data-event-id="<?= $event['id'] ?>">View Details</button>
                            <?php if (in_array($event["id"], $registered_events)): ?>
                                <div class="flex items-center">
                                    <span class="registered-text font-bold mr-2">Registered</span>
                                    <button class="cancel-btn btn" data-event-id="<?= $event["id"] ?>">Cancel</button>
                                </div>
                            <?php elseif ($event["status"] === "open"): ?>
                                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg register-btn btn" data-event-id="<?= $event["id"] ?>">Register</button>
                            <?php else: ?>
                                <span class="registration-closed-text">Registration Closed</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- View Details Modal Structure -->
    <div id="eventDetailsModal" class="fixed inset-0 bg-base-100 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-base-100 p-6 rounded-lg shadow-xl max-w-lg w-full">
            <!-- Banner container with display:none instead of hidden class -->
            <div id="eventBannerContainer" class="mb-4" style="display: none;">
                <img id="eventBanner" src="" alt="Event Banner" class="w-full h-48 object-cover rounded-t-lg">
            </div>
            <h2 class="text-2xl font-bold mb-4">Event Details</h2>
            <p class="mb-2"><strong>Name:</strong> <span id="eventName"></span></p>
            <p class="mb-2"><strong>Date:</strong> <span id="eventDate"></span></p>
            <p class="mb-2"><strong>Time:</strong> <span id="eventTime"></span></p>
            <p class="mb-2"><strong>Location:</strong> <span id="eventLocation"></span></p>
            <p class="mb-2"><strong>Description:</strong> <span id="eventDescription"></span></p>
            <p class="mb-2"><strong>Max Participants:</strong> <span id="maxParticipants"></span></p>
            <p class="mb-4"><strong>Status:</strong> <span id="eventStatus"></span></p>
            <button id="closeEventDetailsModal" class="bg-blue-500 hover:bg-blue-700 text-white py-2 px-4 rounded">Close</button>
        </div>
    </div>

    <!-- Register Modal Structure -->
    <div id="registerModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-gray-800 p-6 rounded-lg shadow-xl max-w-lg w-full">
            <h2 class="text-xl text-white font-bold mb-4">Confirm Registration</h2>
            <p>Are you sure you want to register for this event?</p>
            <div class="mt-4 flex justify-end space-x-4">
                <button id="confirmRegister" class="bg-blue-500 hover:bg-blue-700 text-white py-2 px-4 rounded">Confirm</button>
                <button id="cancelRegister" class="bg-gray-500 hover:bg-gray-700 text-white py-2 px-4 rounded">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Cancel Modal Structure -->
    <div id="cancelModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-gray-800 p-6 rounded-lg shadow-xl max-w-lg w-full">
            <h2 class="text-xl text-white font-bold mb-4">Confirm Cancellation</h2>
            <p>Are you sure you want to cancel your registration for this event?</p>
            <div class="mt-4 flex justify-end space-x-4">
                <button id="confirmCancel" class="bg-red-500 hover:bg-red-700 text-white py-2 px-4 rounded">Confirm</button>
                <button id="cancelCancel" class="bg-gray-500 hover:bg-gray-700 text-white py-2 px-4 rounded">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Success Message Modal -->
    <div id="successModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-gray-800 p-6 rounded-lg shadow-xl max-w-lg w-full">
            <h2 class="text-xl text-white font-bold mb-4">Action Complete</h2>
            <p id="successMessage">Success!</p>
            <button id="closeSuccessModal" class="bg-blue-500 hover:bg-blue-700 text-white py-2 px-4 rounded mt-4">Close</button>
        </div>
    </div>

    <!-- Script for Handling Modals and AJAX -->
    <script>
        $(document).ready(function() {
            let selectedEventId = null;
            let registeredEvents = <?php echo json_encode($registered_events); ?>;

            // Search bar functionality
            $('#search-bar').on('input', function() {
                let query = $(this).val();

                $.ajax({
                    url: 'search_events.php',
                    method: 'GET',
                    data: { query: query },
                    success: function(response) {
                        let events;
                        
                        // Safely parse JSON
                        try {
                            events = JSON.parse(response);
                        } catch (e) {
                            console.error('Failed to parse JSON response:', e);
                            events = [];
                        }

                        let eventRows = '';

                        if (Array.isArray(events) && events.length > 0) {  // Verify events is an array
                            events.forEach(function(event) {
                                // Verify event object has required properties
                                if (!event || typeof event !== 'object') return;
                                
                                // Use optional chaining and nullish coalescing for safer property access
                                const eventId = event?.id || '';
                                const eventName = event?.name || '';
                                const eventDate = event?.event_date || '';
                                const eventLocation = event?.location || '';
                                const eventStatus = event?.status || '';
                                const eventBanner = event?.banner || '';
                                
                                let isRegistered = registeredEvents.includes(parseInt(eventId));
                                
                                eventRows += `
                                    <div class="card">
                                        <div class="card-header relative">
                                            ${eventBanner ? `<div class="event-banner" style="background-image: url('../uploads/${eventBanner}');"></div>` : ''}
                                            <h3 class="text-xl font-bold relative z-10">${eventName}</h3>
                                        </div>
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="flex items-center mb-2">
                                                    <i class="ph ph-calendar text-xl text-white mr-2"></i>
                                                    <p class="text-sm">${eventDate}</p>
                                                </div>
                                                <div class="flex items-center mb-2">
                                                    <i class="ph ph-map-pin text-xl text-white mr-2"></i>
                                                    <p class="text-sm">${eventLocation}</p>
                                                </div>
                                                <p class="text-sm font-semibold status-${eventStatus} mb-4">
                                                    Status: ${eventStatus.charAt(0).toUpperCase() + eventStatus.slice(1)}
                                                </p>
                                                <div class="flex justify-between items-center">
                                                    <button class="view-details-btn btn" data-event-id="${eventId}">View Details</button>
                                                    ${isRegistered ? 
                                                        `<div class="flex items-center">
                                                            <span class="registered-text font-bold mr-2">Registered</span>
                                                            <button class="cancel-btn btn" data-event-id="${eventId}">Cancel</button>
                                                        </div>` :
                                                        (eventStatus === 'open' ? 
                                                            `<button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg register-btn btn" data-event-id="${eventId}">Register</button>` : 
                                                            '<span class="registration-closed-text">Registration Closed</span>'
                                                        )
                                                    }
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                        } else {
                            eventRows = '<div class="text-center py-6">No events found</div>';
                        }

                        $('.grid').html(eventRows);
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        $('.grid').html('<div class="text-center py-6">Error loading events</div>');
                    }
                });
            });

            // Handle View Details button click
            $(document).on('click', '.view-details-btn', function() {
                selectedEventId = $(this).data('event-id');
                $.ajax({
                    url: 'event_details.php',
                    method: 'GET',
                    data: { event_id: selectedEventId },
                    success: function(response) {
                        let event = JSON.parse(response);
                        $('#eventName').text(event.name);
                        $('#eventDate').text(event.event_date);
                        $('#eventTime').text(event.event_time);
                        $('#eventLocation').text(event.location);
                        $('#eventDescription').text(event.description);
                        $('#maxParticipants').text(event.max_participants);
                        $('#eventStatus').text(event.status.charAt(0).toUpperCase() + event.status.slice(1));

                        // Enhanced banner visibility handling
                        const bannerContainer = $('#eventBannerContainer');
                        if (event.banner) {
                            // Set image source and handle load/error events
                            const bannerImg = $('#eventBanner');
                            bannerImg
                                .on('load', function() {
                                    // Image loaded successfully
                                    bannerContainer.show();
                                })
                                .on('error', function() {
                                    // Image failed to load
                                    bannerContainer.hide();
                                    console.log('Failed to load image:', event.banner);
                                })
                                .attr('src', '../uploads/' + event.banner);
                        } else {
                            bannerContainer.hide();
                        }

                        $('#eventDetailsModal').removeClass('hidden');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching event details:', error);
                    }
                });
            });

            // Close Event Details Modal
            $('#closeEventDetailsModal').on('click', function() {
                $('#eventDetailsModal').addClass('hidden');
                // Reset banner container and image when closing
                $('#eventBannerContainer').hide();
                $('#eventBanner').attr('src', '').off('load error');
            });

            // Handle register button click
            $(document).on('click', '.register-btn', function() {
                selectedEventId = $(this).data('event-id');
                $('#registerModal').removeClass('hidden');
            });

            // Confirm registration
            $('#confirmRegister').on('click', function() {
                $.ajax({
                    url: 'register_event.php',
                    method: 'GET',
                    data: { event_id: selectedEventId },
                    success: function(response) {
                        let res = JSON.parse(response);
                        if (res.status === 'success') {
                            $('#registerModal').addClass('hidden');
                            $('#successMessage').text(res.message);
                            $('#successModal').removeClass('hidden');
                            registeredEvents.push(selectedEventId);
                        } else {
                            alert(res.message);
                        }
                    }
                });
            });

            // Handle cancel button click
            $(document).on('click', '.cancel-btn', function() {
                selectedEventId = $(this).data('event-id');
                $('#cancelModal').removeClass('hidden');
            });

            // Confirm cancellation
            $('#confirmCancel').on('click', function() {
                $.ajax({
                    url: 'cancel_registration.php',
                    method: 'GET',
                    data: { event_id: selectedEventId },
                    success: function(response) {
                        let res = JSON.parse(response);
                        if (res.status === 'success') {
                            $('#cancelModal').addClass('hidden');
                            $('#successMessage').text(res.message);
                            $('#successModal').removeClass('hidden');
                            registeredEvents = registeredEvents.filter(id => id !== selectedEventId);
                        } else {
                            alert(res.message);
                        }
                    },
                    error: function() {
                        alert('Error: Unable to cancel registration.');
                    }
                });
            });

            // Close register and cancel modals
            $('#cancelRegister, #cancelCancel').on('click', function() {
                $('#registerModal, #cancelModal').addClass('hidden');
            });

            $('#closeSuccessModal').on('click', function() {
                $('#successModal').addClass('hidden');
                location.reload();
            });
        });
    </script>
</body>
</html>