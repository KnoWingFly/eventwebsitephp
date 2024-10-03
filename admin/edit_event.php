<?php
// admin/edit_event.php
session_start();
require '../config.php';

if ($_SESSION['role'] != 'admin') {
    header('Location: ../index.php?page=login');
    exit;
}

$event_id = $_GET['id'];

// Fetch event details
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];  // Use correct key
    $location = $_POST['location'];
    $description = $_POST['description'];
    $max_participants = $_POST['max_participants'];
    $status = $_POST['status'];

    // Handle image update if new image is uploaded
    $banner = $event['banner']; // Keep the old image by default
    if (!empty($_FILES['image']['name'])) {
        $banner = $_FILES['image']['name'];
        $target = "../uploads/" . basename($banner);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    }

    // Update event data in the database
    $stmt = $pdo->prepare("UPDATE events SET name = ?, event_date = ?, event_time = ?, location = ?, description = ?, max_participants = ?, status = ?, banner = ? WHERE id = ?");
    $stmt->execute([$name, $event_date, $event_time, $location, $description, $max_participants, $status, $banner, $event_id]);
    
    // Redirect to admin dashboard after successful update
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-10">
        <h1 class="text-2xl font-bold mb-6">Edit Event</h1>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block">Event Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($event['name']) ?>" required class="w-full p-2 border">
            </div>

            <div class="mb-4">
                <label class="block">Event Date</label>
                <input type="date" name="event_date" value="<?= htmlspecialchars($event['event_date']) ?>" required class="w-full p-2 border">
            </div>

            <div class="mb-4">
                <label class="block">Event Time</label>
                <input type="time" name="event_time" value="<?= htmlspecialchars($event['event_time']) ?>" required class="w-full p-2 border">
            </div>

            <div class="mb-4">
                <label class="block">Location</label>
                <input type="text" name="location" value="<?= htmlspecialchars($event['location']) ?>" required class="w-full p-2 border">
            </div>

            <div class="mb-4">
                <label class="block">Description</label>
                <textarea name="description" required class="w-full p-2 border"><?= htmlspecialchars($event['description']) ?></textarea>
            </div>

            <div class="mb-4">
                <label class="block">Max Participants</label>
                <input type="number" name="max_participants" value="<?= htmlspecialchars($event['max_participants']) ?>" required class="w-full p-2 border">
            </div>

            <div class="mb-4">
                <label class="block">Event Status</label>
                <select name="status" required class="w-full p-2 border">
                    <option value="open" <?= $event['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                    <option value="closed" <?= $event['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                    <option value="canceled" <?= $event['status'] === 'canceled' ? 'selected' : '' ?>>Canceled</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block">Event Image</label>
                <input type="file" name="image" accept="image/*" class="w-full p-2 border">
                <!-- Show the existing image -->
                <?php if ($event['banner']): ?>
                    <img src="../uploads/<?= htmlspecialchars($event['banner']) ?>" alt="Event Image" class="mt-4 w-32 h-32">
                <?php endif; ?>
            </div>

            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">Update Event</button>
        </form>
    </div>
</body>
</html>
