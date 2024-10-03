<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management System</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        h1, h2 { color: #333; }
        form { margin-bottom: 20px; }
        input[type="text"], input[type="date"], textarea { width: 100%; padding: 8px; margin-bottom: 10px; }
        input[type="submit"] { background-color: #4CAF50; color: white; padding: 10px 15px; border: none; cursor: pointer; }
        input[type="submit"]:hover { background-color: #45a049; }
        ul { list-style-type: none; padding: 0; }
        li { background-color: #f2f2f2; margin-bottom: 10px; padding: 10px; border-radius: 5px; }

        /* Modal CSS */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            text-align: center;
        }
        .close-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Event Management System</h1>

    <?php if (isset($_SESSION["user_id"])): ?>
        <a href="/logout" style="color: red;">Logout</a>
    <?php endif; ?>

    <h2>All Events</h2>
    <?php if (empty($allEvents)): ?>
        <p>No events available at the moment.</p>
    <?php else: ?>
        <ul>
        <?php foreach ($allEvents as $evt): ?>
            <li>
                <h3><?= htmlspecialchars($evt["title"]) ?></h3>
                <p><?= htmlspecialchars($evt["description"]) ?></p>
                <p>Location: <?= htmlspecialchars($evt["location"]) ?></p>
                <p>Date: <?= htmlspecialchars($evt["event_date"]) ?></p>
                <form method="POST" action="index.php?action=registerForEvent">
                    <input type="hidden" name="event_id" value="<?= $evt[
                    	"id"
                    ] ?>">
                    <input type="submit" name="register_event" value="Register for Event">
                </form>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h2>Create New Event</h2>
    <form id="event-form" action="/create-event" method="POST">
        <input type="text" name="event_title" placeholder="Event Title" required>
        <input type="text" name="event_description" placeholder="Event Description" required>
        <input type="date" name="event_date" placeholder="Event Date" required>
        <input type="text" name="event_location" placeholder="Event Location" required>
        <button type="submit">Create Event</button>
    </form>

    <a href="index.php?action=listRegisteredEvents">View Your Registered Events</a>

    <!-- Modal -->
    <div id="success-modal" class="modal">
        <div class="modal-content">
            <p>Event has been successfully created!</p>
        </div>
        <div class="modal-footer">
            <button id="modal-ok-btn" class="btn btn-success">OK</button>
        </div>
    </div>

    <script>
        // Check modal
        <?php if (isset($eventCreated) && $eventCreated): ?>
            document.getElementById("eventCreatedModal").style.display = "block";
        <?php endif; ?>

        // Function close modal
        function closeModal() {
            document.getElementById("eventCreatedModal").style.display = "none";
            location.reload(); // Refresh page to show updated event list
        }

        document.getElementById('modal-ok-btn').addEventListener('click', function() {
            // Close moda
            document.getElementById('success-modal').style.display = 'none';

            // Reset the form
            document.getElementById('event-form').reset();
        });
    </script>
</body>
</html>
