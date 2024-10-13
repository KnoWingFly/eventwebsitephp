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
</head>
<body class="bg-gray-50">
    <!-- Redesigned Header -->
    <nav class="w-full bg-gradient-to-r from-cyan-500 to-blue-500 text-white shadow-lg">
        <div class="container mx-auto flex justify-between items-center px-6 py-4">
            <a href="dashboard.php" class="text-3xl font-bold">Event System</a>
            <div class="w-full md:w-1/3">
                <input id="search-bar" type="text" placeholder="Search events..." class="w-full p-2 bg-gray-100 text-black rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <a href="../index.php?page=logout" class="bg-red-500 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">Logout</a>
        </div>
    </nav>

    <!-- Card Layout for Events -->
    <div class="container mx-auto px-6 py-10">
    <h2 class="text-2xl font-bold mb-6">Available Events</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($all_events as $event): ?>
        <div class="bg-white shadow-md rounded-lg p-6 transform transition duration-300 hover:scale-105 hover:shadow-lg">
            <div class="flex items-center mb-4">
                <h3 class="text-2xl font-bold"><?= htmlspecialchars($event["name"]) ?></h3>
            </div>
            <div class="flex items-center mb-2">
                <svg class="w-6 h-6 text-indigo-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="text-sm text-gray-600">Date: <?= htmlspecialchars($event["event_date"]) ?></p>
            </div>
            <div class="flex items-center mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-indigo-600 mr-3 flex-shrink-0">
                    <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm text-gray-600">Location: <?= htmlspecialchars($event["location"]) ?></p>
            </div>
            <p class="text-sm font-semibold <?= $event["status"] === 'open' ? 'text-green-600' : ($event["status"] === 'closed' ? 'text-gray-600' : 'text-red-600') ?> mb-4">
                Status: <?= ucfirst(htmlspecialchars($event["status"])) ?>
            </p>
            <div class="flex justify-between items-center">
                <button class="text-indigo-600 hover:text-indigo-800 view-details-btn transition duration-300 ease-in-out transform hover:-translate-y-1" data-event-id="<?= $event['id'] ?>">View Details</button>
                <?php if (in_array($event["id"], $registered_events)): ?>
                    <div class="flex items-center">
                        <span class="text-green-500 font-bold mr-2">Registered</span>
                        <button class="text-red-500 hover:text-red-700 cancel-btn transition duration-300 ease-in-out transform hover:-translate-y-1" data-event-id="<?= $event["id"] ?>">Cancel</button>
                    </div>
                <?php elseif ($event["status"] === "open"): ?>
                    <button class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded-lg register-btn transition duration-300 ease-in-out transform hover:-translate-y-1 hover:shadow-md" data-event-id="<?= $event["id"] ?>">Register</button>
                <?php else: ?>
                    <span class="text-gray-500">Registration Closed</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

    <!-- View Details Modal Structure -->
    <div id="eventDetailsModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-lg w-full">
            <div class="mb-4">
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
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-lg w-full">
            <h2 class="text-xl font-bold mb-4">Confirm Registration</h2>
            <p>Are you sure you want to register for this event?</p>
            <div class="mt-4 flex justify-end space-x-4">
                <button id="confirmRegister" class="bg-blue-500 hover:bg-blue-700 text-white py-2 px-4 rounded">Confirm</button>
                <button id="cancelRegister" class="bg-gray-500 hover:bg-gray-700 text-white py-2 px-4 rounded">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Cancel Modal Structure -->
    <div id="cancelModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-lg w-full">
            <h2 class="text-xl font-bold mb-4">Confirm Cancellation</h2>
            <p>Are you sure you want to cancel your registration for this event?</p>
            <div class="mt-4 flex justify-end space-x-4">
                <button id="confirmCancel" class="bg-red-500 hover:bg-red-700 text-white py-2 px-4 rounded">Confirm</button>
                <button id="cancelCancel" class="bg-gray-500 hover:bg-gray-700 text-white py-2 px-4 rounded">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Success Message Modal -->
    <div id="successModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-lg w-full">
            <h2 class="text-xl font-bold mb-4">Action Complete</h2>
            <p id="successMessage">Success!</p>
            <button id="closeSuccessModal" class="bg-blue-500 hover:bg-blue-700 text-white py-2 px-4 rounded mt-4">Close</button>
        </div>
    </div>

    <!-- Script for Handling Modals and AJAX -->
    <script>
        $(document).ready(function() {
            let selectedEventId = null;

            // Search bar functionality
            $('#search-bar').on('input', function() {
                let query = $(this).val();

                $.ajax({
                    url: 'search_events.php',
                    method: 'GET',
                    data: { query: query },
                    success: function(response) {
                        let events = JSON.parse(response);
                        let eventRows = '';

                        if (events.length > 0) {
                            events.forEach(function(event) {
                                eventRows += `
                                    <div class="bg-white shadow-md rounded-lg p-6">
                                        <h3 class="text-2xl font-bold mb-2">${event.name}</h3>
                                        <p class="text-sm text-gray-600">Date: ${event.event_date}</p>
                                        <p class="text-sm text-gray-600">Location: ${event.location}</p>
                                        <p class="text-sm font-semibold ${event.status === 'open' ? 'text-green-600' : (event.status === 'closed' ? 'text-gray-600' : 'text-red-600')} mb-4">
                                            Status: ${event.status.charAt(0).toUpperCase() + event.status.slice(1)}
                                        </p>
                                        <div class="flex justify-between items-center">
                                            <button class="text-indigo-600 hover:text-indigo-800 view-details-btn" data-event-id="${event.id}">View Details</button>
                                            ${event.status === 'open' ? '<button class="bg-blue-500 hover:bg-blue-700 text-white px-4 py-2 rounded-lg register-btn" data-event-id="'+ event.id +'">Register</button>' : '<span class="text-gray-500">Registration Closed</span>'}
                                        </div>
                                    </div>
                                `;
                            });
                        } else {
                            eventRows = '<div class="text-center py-6">No events found</div>';
                        }

                        $('.grid').html(eventRows);
                    },
                    error: function() {
                        alert('Error fetching events.');
                    }
                });
            });

            // Handle View Details button click
            $('.view-details-btn').on('click', function() {
                selectedEventId = $(this).data('event-id');
                $.ajax({
                    url: 'event_details.php', // Fetch event details via AJAX
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

                        if (event.banner && event.banner !== '') {
                            $('#eventBanner').attr('src', '../uploads/' + event.banner).show();
                        } else {
                            $('#eventBanner').hide();
                        }

                        $('#eventDetailsModal').removeClass('hidden');
                    }
                });
            });

            // Close Event Details Modal
            $('#closeEventDetailsModal').on('click', function() {
                $('#eventDetailsModal').addClass('hidden');
            });

            // Handle register button click
            $('.register-btn').on('click', function() {
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
                        } else {
                            alert(res.message);
                        }
                    }
                });
            });

            // Handle cancel button click
            $('.cancel-btn').on('click', function() {
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

            // Close success modal and reload page
            $('#closeSuccessModal').on('click', function() {
                $('#successModal').addClass('hidden');
                location.reload();
            });
        });
    </script>
</body>
</html>
