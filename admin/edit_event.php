<?php
session_start();
require '../config.php';

if ($_SESSION['role'] != 'admin') {
    header('Location: ../index.php?page=login');
    exit;
}

$event_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $max_participants = $_POST['max_participants'];
    $status = $_POST['status'];

    $banner = $event['banner'];
    if (!empty($_FILES['image']['name'])) {
        $banner = $_FILES['image']['name'];
        $target = "../uploads/" . basename($banner);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    }

    $stmt = $pdo->prepare("UPDATE events SET name = ?, event_date = ?, event_time = ?, location = ?, description = ?, max_participants = ?, status = ?, banner = ? WHERE id = ?");
    $stmt->execute([$name, $event_date, $event_time, $location, $description, $max_participants, $status, $banner, $event_id]);

    if ($status === 'canceled') {
        $stmt_delete_registrations = $pdo->prepare("DELETE FROM registrations WHERE event_id = ?");
        $stmt_delete_registrations->execute([$event_id]);
    }

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
    <link href="../css/output.css" rel="stylesheet">
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const statusSelect = document.querySelector('select[name="status"]');
            const eventDate = new Date("<?= $event['event_date'] ?>T<?= $event['event_time'] ?>");
            const now = new Date();
            const originalStatus = "<?= $event['status'] ?>";

            const isEventPassed = eventDate < now;

            statusSelect.addEventListener('change', function (e) {
                if (isEventPassed && this.value === 'open') {
                    e.preventDefault();
                    document.getElementById('modal').classList.remove('hidden');
                    this.value = originalStatus;
                }
            });

            document.getElementById('confirm').addEventListener('click', function () {
                document.getElementById('modal').classList.add('hidden');
            });

            document.getElementById('cancel').addEventListener('click', function () {
                document.getElementById('modal').classList.add('hidden');
            });
        });
    </script>
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
                <?php if ($event['banner']): ?>
                    <img src="../uploads/<?= htmlspecialchars($event['banner']) ?>" alt="Event Image" class="mt-4 w-32 h-32">
                <?php endif; ?>
            </div>

            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded">Update Event</button>
        </form>
    </div>

    <!-- Modal -->
    <div id="modal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Cannot Change Status</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">This event has already passed. You cannot change its status to 'open'.</p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button id="confirm" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Understand
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
