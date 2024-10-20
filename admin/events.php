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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #181A1B;
            color: #E0E0E0;
        }
        .form-container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #1F1F1F;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
        }
        .btn-custom {
            background-color: #6C63FF;
            color: white;
        }
        .btn-custom:hover {
            background-color: #574BDF;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1 class="text-center mb-4">Create a New Event</h1>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Event Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Event Date</label>
                    <input type="date" name="event_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Event Time</label>
                    <input type="time" name="event_time" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Max Participants</label>
                    <input type="number" name="max_participants" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="open">Open</option>
                        <option value="closed">Closed</option>
                        <option value="canceled">Canceled</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Upload Banner</label>
                    <input type="file" name="banner" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-custom w-100">Create Event</button>
            </form>
        </div>
    </div>


</body>
</html>
