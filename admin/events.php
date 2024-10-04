<?php
require '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $max_participants = $_POST['max_participants'];
    $status = $_POST['status'];

    $banner = $_FILES['banner']['name'];
    move_uploaded_file($_FILES['banner']['tmp_name'], '../uploads/' . $banner);

    $stmt = $pdo->prepare("INSERT INTO events (name, description, location, event_date, event_time, max_participants, banner, status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $description, $location, $event_date, $event_time, $max_participants, $banner, $status]);

    header('Location: index.php?page=admin/dashboard');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Event</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-10">
        <h1 class="text-2xl font-bold mb-6">Create a New Event</h1>
        <form action="" method="post" enctype="multipart/form-data" class="bg-white shadow-lg rounded-lg p-6">
            <div class="mb-4">
                <label class="block text-gray-700">Event Name</label>
                <input type="text" name="name" class="w-full p-3 border border-gray-300 rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Description</label>
                <textarea name="description" class="w-full p-3 border border-gray-300 rounded-lg"></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Location</label>
                <input type="text" name="location" class="w-full p-3 border border-gray-300 rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Event Date</label>
                <input type="date" name="event_date" class="w-full p-3 border border-gray-300 rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Event Time</label>
                <input type="time" name="event_time" class="w-full p-3 border border-gray-300 rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Max Participants</label>
                <input type="number" name="max_participants" class="w-full p-3 border border-gray-300 rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Status</label>
                <select name="status" class="w-full p-3 border border-gray-300 rounded-lg">
                    <option value="open">Open</option>
                    <option value="closed">Closed</option>
                    <option value="canceled">Canceled</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Upload Banner</label>
                <input type="file" name="banner" class="w-full p-3 border border-gray-300 rounded-lg">
            </div>
            <button type="submit" class="bg-teal-500 text-white px-4 py-2 rounded-lg">Create Event</button>
        </form>
    </div>
</body>
</html>
