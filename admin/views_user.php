<?php
include('../config.php');

$search = isset($_POST['search']) ? trim($_POST['search']) : '';

$query = "
    SELECT 
        events.*,
        events.max_participants,
        (SELECT COUNT(*) FROM registrations WHERE registrations.event_id = events.id) AS registered_people
    FROM events
";

if (!empty($search)) {
    if (DateTime::createFromFormat('Y-m-d', $search)) {
        $query .= " WHERE event_date = :search_date";
        $params = [':search_date' => $search];
    } else {
        $query .= " WHERE name LIKE :search_name";
        $params = [':search_name' => '%' . $search . '%'];
    }
} else {
    $params = [];
}

$eventsQuery = $pdo->prepare($query);
$eventsQuery->execute($params);

$events = $eventsQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Users</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Back to Admin Dashboard Button -->
    <div class="container mx-auto mt-5">
        <a href="dashboard.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
            ← Back to Admin Dashboard
        </a>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto mt-10">

        <!-- Page Title -->
        <h2 class="text-4xl font-bold mb-8 text-center text-gray-700">View Registered Users by Event</h2>

        <!-- Search Bar -->
        <form method="POST" class="flex justify-center mb-8">
            <input type="text" name="search" class="p-3 border border-gray-400 rounded-lg w-1/3 focus:outline-none focus:ring focus:ring-blue-200" placeholder="Search by event name or date (YYYY-MM-DD)">
            <button type="submit" class="bg-blue-500 text-white p-3 ml-2 rounded-lg hover:bg-blue-600 transition">Search</button>
        </form>

        <!-- Event Schedule Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php if (count($events) > 0): ?>
                <?php foreach ($events as $event): ?>
                    <div class="bg-white p-6 rounded-lg shadow-lg transition-transform hover:scale-105 duration-200">
                        <h3 class="text-xl font-bold text-gray-700"><?= htmlspecialchars($event['name']) ?></h3> <!-- Corrected column name -->
                        <p class="text-gray-600">Date: <?= htmlspecialchars($event['event_date']) ?></p>
                        <p class="text-gray-600">Time: <?= htmlspecialchars($event['event_time']) ?></p>
                        <p class="text-gray-600">People Registered: <?= htmlspecialchars($event['registered_people']) ?> / <?= htmlspecialchars($event['max_participants']) ?></p>
                        <button onclick="viewDetails(<?= $event['id'] ?>)" class="mt-4 bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition">View Participants</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="col-span-4 text-center text-gray-500">No events found for your search query.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal to Show Event Details -->
    <div id="event-modal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-lg">
            <h2 class="text-2xl font-bold mb-4" id="event-title"></h2> <!-- Updated Title -->
            <p id="event-date" class="text-lg text-gray-600"></p>
            <p id="event-time" class="text-lg text-gray-600 mb-4"></p>

            <h3 class="text-xl font-semibold mt-5 mb-2">Participants</h3>
            <ul id="participant-list" class="space-y-2 list-disc list-inside text-gray-800"></ul> <!-- Updated Participant List -->

            <div class="mt-6 text-right">
                <button onclick="closeModal()" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition">Close</button>
            </div>
        </div>
    </div>

    <script>
        function viewDetails(eventId) {
            fetch('get_event_details.php?event_id=' + eventId)
            .then(response => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                // Update modal content
                document.getElementById('event-title').innerText = data.name || "Event Name Missing";
                document.getElementById('event-date').innerText = "Date: " + data.event_date;
                document.getElementById('event-time').innerText = "Time: " + data.event_time;

                let participants = data.participants;
                let participantList = document.getElementById('participant-list');
                participantList.innerHTML = '';
                participants.forEach(function(participant) {
                    let li = document.createElement('li');
                    li.innerText = `${participant.name} (${participant.email})`;
                    participantList.appendChild(li);
                });

                // Show the modal
                document.getElementById('event-modal').classList.remove('hidden');
            })
            .catch(error => {
                console.error('There was a problem with the fetch operation:', error);
                alert("There was an issue fetching event details.");
            });
        }

        function closeModal() {
            document.getElementById('event-modal').classList.add('hidden');
        }

    </script>

</body>
</html>
