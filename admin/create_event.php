<?php
// admin/create_event.php
session_start();
require '../config.php';

// Check if admin is logged in
if ($_SESSION['role'] != 'admin') {
    header('Location: ../index.php?page=login');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $name = $_POST['name'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time']; // Added for event_time
    $location = $_POST['location'];
    $description = $_POST['description'];
    $max_participants = $_POST['max_participants'];
    $status = $_POST['status']; // Collect event status

    // Image file handling (Optional Upload)
    if (!empty($_FILES['banner']['name'])) {
        $banner = $_FILES['banner']['name'];
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($banner);

        // Move uploaded file
        if (!move_uploaded_file($_FILES['banner']['tmp_name'], $target_file)) {
            $error = 'Error uploading the banner image.';
        }
    } else {
        // If no image provided, set banner to NULL
        $banner = null;
    }

    // Insert event data into the database
    if (!$error) {
        try {
            $stmt = $pdo->prepare("INSERT INTO events (name, event_date, event_time, location, description, max_participants, banner, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $event_date, $event_time, $location, $description, $max_participants, $banner, $status]);

            // Redirect to admin dashboard after successful event creation
            header('Location: dashboard.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Event</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-10">
        <h1 class="text-2xl font-bold mb-6">Create New Event</h1>

        <?php if ($error): ?>
            <div class="text-red-500 mb-4"><?= $error ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Event Name</label>
                <input type="text" name="name" required class="mt-1 p-2 w-full border rounded">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Event Date</label>
                <input type="date" name="event_date" required class="mt-1 p-2 w-full border rounded">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Event Time</label>
                <input type="time" name="event_time" required class="mt-1 p-2 w-full border rounded"> <!-- Event Time Input -->
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Location</label>
                <input type="text" name="location" required class="mt-1 p-2 w-full border rounded">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" required class="mt-1 p-2 w-full border rounded"></textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Max Participants</label>
                <input type="number" name="max_participants" required class="mt-1 p-2 w-full border rounded">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Event Status</label>
                <select name="status" required class="mt-1 p-2 w-full border rounded">
                    <option value="open">Open</option>
                    <option value="closed">Closed</option>
                    <option value="canceled">Canceled</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Upload Banner (Optional)</label>
                <input type="file" name="banner" class="mt-1 p-2 w-full border rounded">
            </div>

            <div class="mb-4">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Create Event
                </button>
            </div>
        </form>
    </div>
</body>
</html>
