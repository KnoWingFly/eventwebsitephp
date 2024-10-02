<?php
// session_start(); // Removed, assuming session has already been started elsewhere

require_once '../model/Event.php'; // Adjust path according to your project structure

// Simulating a logged-in user
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 'U0001'; // Assuming the user ID is a CHAR(5)
}

$event = new Event();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create_event'])) {
        $event->createEvent(
            $_POST['title'],
            $_POST['description'],
            $_POST['schedule'],
            $_POST['location'],
            $_POST['event_date']
        );
    } elseif (isset($_POST['register_event'])) {
        $event->registerUserForEvent($_SESSION['user_id'], $_POST['event_id']);
    }
}

// Fetch all events and registered events
$allEvents = $event->getAllEvents();
$registeredEvents = $event->getRegisteredEvents($_SESSION['user_id']);
?>

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
    </style>
</head>
<body>
    <h1>Event Management System</h1>

    <h2>Create New Event</h2>
    <form method="POST">
        <input type="text" name="title" placeholder="Event Title" required><br>
        <textarea name="description" placeholder="Event Description" required></textarea><br>
        <input type="text" name="schedule" placeholder="Event Schedule" required><br>
        <input type="text" name="location" placeholder="Event Location" required><br>
        <input type="date" name="event_date" required><br>
        <input type="submit" name="create_event" value="Create Event">
    </form>

    <h2>All Events</h2>
    <ul>
    <?php foreach ($allEvents as $evt): ?>
        <li>
            <h3><?= htmlspecialchars($evt['title']) ?></h3>
            <p><?= htmlspecialchars($evt['description']) ?></p>
            <p>Schedule: <?= htmlspecialchars($evt['schedule']) ?></p>
            <p>Location: <?= htmlspecialchars($evt['location']) ?></p>
            <p>Date: <?= htmlspecialchars($evt['event_date']) ?></p>
            <form method="POST">
                <input type="hidden" name="event_id" value="<?= $evt['id'] ?>">
                <input type="submit" name="register_event" value="Register for Event">
            </form>
        </li>
    <?php endforeach; ?>
    </ul>

    <h2>Your Registered Events</h2>
    <ul>
    <?php foreach ($registeredEvents as $evt): ?>
        <li>
            <h3><?= htmlspecialchars($evt['title']) ?></h3> 
        </li>
    <?php endforeach; ?>
    </ul>
</body>
</html>
