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
<body class="bg-gray-100">
    <!-- Navbar -->
    <nav class="bg-blue-600 text-white px-4 py-3 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <a href="dashboard.php" class="text-2xl font-bold">Event System</a>
            <div class="w-1/3">
                <input id="search-bar" type="text" placeholder="Search events..." class="w-full p-2 rounded-lg text-black focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="space-x-6">
                <a href="dashboard.php" class="hover:underline">Home</a>
                <a href="profile.php" class="hover:underline``">View Profile</a>
            </div>
            <a href="../index.php?page=logout" class="bg-red-500 hover:bg-red-700 text-white px-4 py-2 rounded-lg">Logout</a>
        </div>
    </nav>

    <!-- All Events Table -->
    <div class="container mx-auto p-10 bg-white shadow-lg rounded-lg">
        <h2 class="text-xl font-semibold mb-4">Browse Available Events</h2>
        <table class="min-w-full bg-white border border-gray-300 rounded-lg">
            <thead>
                <tr>
                    <th class="py-2 border-b text-left">Event Name</th>
                    <th class="py-2 border-b text-left">Date</th>
                    <th class="py-2 border-b text-left">Location</th>
                    <th class="py-2 border-b text-left">Status</th>
                    <th class="py-2 border-b text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_events as $event): ?>
                <tr>
                    <td class="py-2 border-b"><?= htmlspecialchars(
                    	$event["name"],
                    ) ?></td>
                    <td class="py-2 border-b"><?= htmlspecialchars(
                    	$event["event_date"],
                    ) ?></td>
                    <td class="py-2 border-b"><?= htmlspecialchars(
                    	$event["location"],
                    ) ?></td>
                    <td class="py-2 border-b">
                        <?php if ($event["status"] === "canceled"): ?>
                            <span class="text-red-500">Canceled</span>
                        <?php elseif ($event["status"] === "closed"): ?>
                            <span class="text-gray-500">Closed</span>
                        <?php else: ?>
                            <span class="text-green-500">Open</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-2 border-b">
                        <!-- View Details Button -->
                        <button class="text-blue-500 view-details-btn" data-event-id="<?= $event[
                        	"id"
                        ] ?>">View Details</button>
                        <?php if (
                        	in_array($event["id"], $registered_events)
                        ): ?>
                            <span class="text-green-500 font-bold">Registered</span> |
                            <button class="text-red-500 cancel-btn" data-event-id="<?= $event[
                            	"id"
                            ] ?>">Cancel Registration</button>
                        <?php elseif ($event["status"] === "open"): ?>
                            <button class="text-blue-500 register-btn" data-event-id="<?= $event[
                            	"id"
                            ] ?>">Register</button>
                        <?php else: ?>
                            <span class="text-gray-500">Registration Closed</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- View Details Modal Structure -->
    <div id="eventDetailsModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-lg w-full">
            <div class="mb-4">
                <img id="eventBanner" src="" alt="Event Banner" class="w-full h-48 object-cover rounded-t-lg">
            </div>

            <!-- Event Details -->
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
    <div id="registerModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden">
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
    <div id="cancelModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden">
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
    <div id="successModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden">
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

            // If query is empty, send an empty request to fetch all events
            $.ajax({
                url: 'search_events.php',
                method: 'GET',
                data: { query: query },
                success: function(response) {
                    let events = JSON.parse(response);
                    let eventRows = '';

                    // Iterate over events and dynamically update the table rows
                    if (events.length > 0) {
                        events.forEach(function(event) {
                            eventRows += `
                                <tr>
                                    <td class="py-2 border-b">${event.name}</td>
                                    <td class="py-2 border-b">${event.event_date}</td>
                                    <td class="py-2 border-b">${event.location}</td>
                                    <td class="py-2 border-b">
                                        ${event.status === 'canceled' ? '<span class="text-red-500">Canceled</span>' : (event.status === 'closed' ? '<span class="text-gray-500">Closed</span>' : '<span class="text-green-500">Open</span>')}
                                    </td>
                                    <td class="py-2 border-b">
                                        <button class="text-blue-500 view-details-btn" data-event-id="${event.id}">View Details</button>
                                        ${event.status === 'open' ? '<button class="text-blue-500 register-btn" data-event-id="'+ event.id +'">Register</button>' : '<span class="text-gray-500">Registration Closed</span>'}
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        eventRows = '<tr><td colspan="5" class="text-center py-2">No events found</td></tr>';
                    }

                    // Replace the event table body with new rows
                    $('tbody').html(eventRows);
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
                        
                        // Set event banner/picture
                        if (event.banner && event.banner !== '') {
                            $('#eventBanner').attr('src', '../uploads/' + event.banner).show();  // Show the banner if it exists
                        } else {
                            $('#eventBanner').hide();  // Hide the banner section if there is no banner
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
