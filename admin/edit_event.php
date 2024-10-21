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
    if (!empty($_FILES['banner']['name'])) {
        $banner = $_FILES['banner']['name'];
        $target = "../uploads/" . basename($banner);
        move_uploaded_file($_FILES['banner']['tmp_name'], $target);
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
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet" type="text/css" />
    <link href="../css/create_event.css" rel="stylesheet">
    <link href="../css/output.css" rel="stylesheet">
    <style>
        .input, .textarea, .select, .file-input {
            max-width: 100%;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .card-body {
            overflow-x: hidden;
        }

        @media (max-width: 640px) {
            .input, .textarea, .select, .file-input {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center p-4 bg-gray-900">
        <div class="w-full max-w-lg mx-auto">
            <div class="card bg-base-200 shadow-xl">
                <div class="card-body p-6">
                    <div class="border-b border-base-300 pb-3 mb-4">
                        <h1 class="card-title text-xl text-white">Edit Event</h1>
                    </div>

                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <!-- Event Name -->
                        <div class="form-control">
                            <label class="label py-1">
                                <span class="label-text text-white">Event Name</span>
                            </label>
                            <input type="text" name="name" value="<?= htmlspecialchars($event['name']) ?>" required 
                                class="input input-bordered input-sm bg-base-300">
                        </div>

                        <!-- Date and Time Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label py-1">
                                    <span class="label-text text-white">Event Date</span>
                                </label>
                                <input type="date" name="event_date" value="<?= htmlspecialchars($event['event_date']) ?>" required 
                                    class="input input-bordered input-sm bg-base-300">
                            </div>
                            <div class="form-control">
                                <label class="label py-1">
                                    <span class="label-text text-white">Event Time</span>
                                </label>
                                <input type="time" name="event_time" value="<?= htmlspecialchars($event['event_time']) ?>" required 
                                    class="input input-bordered input-sm bg-base-300">
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="form-control">
                            <label class="label py-1">
                                <span class="label-text text-white">Location</span>
                            </label>
                            <input type="text" name="location" value="<?= htmlspecialchars($event['location']) ?>" required 
                                class="input input-bordered input-sm bg-base-300">
                        </div>

                        <!-- Description -->
                        <div class="form-control">
                            <label class="label py-1">
                                <span class="label-text text-white">Description</span>
                            </label>
                            <textarea name="description" required rows="3" 
                                class="textarea textarea-bordered bg-base-300 h-20 text-sm"><?= htmlspecialchars($event['description']) ?></textarea>
                        </div>

                        <!-- Max Participants -->
                        <div class="form-control">
                            <label class="label py-1">
                                <span class="label-text text-white">Max Participants</span>
                            </label>
                            <input type="number" name="max_participants" value="<?= htmlspecialchars($event['max_participants']) ?>" required 
                                class="input input-bordered input-sm bg-base-300">
                        </div>

                        <!-- Event Status -->
                        <div class="form-control">
                            <label class="label py-1">
                                <span class="label-text text-white">Event Status</span>
                            </label>
                            <select name="status" required 
                                class="select select-bordered select-sm bg-base-300">
                                <option value="open" <?= $event['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                                <option value="closed" <?= $event['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                                <option value="canceled" <?= $event['status'] === 'canceled' ? 'selected' : '' ?>>Canceled</option>
                            </select>
                        </div>

                        <!-- Banner Upload -->
                        <div class="form-control">
                            <label class="label py-1">
                                <span class="label-text text-white">Upload Banner (Optional)</span>
                            </label>
                            <input type="file" name="banner" accept="image/*"
                                class="file-input file-input-bordered file-input-sm bg-base-300 w-full">
                            <?php if ($event['banner']): ?>
                                <div class="mt-2">
                                    <img src="../uploads/<?= htmlspecialchars($event['banner']) ?>" alt="Current Event Banner" 
                                        class="max-w-xs rounded-lg shadow-lg">
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-control mt-4">
                            <button type="submit" class="btn btn-primary btn-sm">
                                Update Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-base-300 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-base-200 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-error bg-opacity-20 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-error" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">Cannot Change Status</h3>
                        <div class="mt-2">
                            <p class="text-sm text-base-content">This event has already passed. You cannot change its status to 'open'.</p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button id="confirm" class="btn btn-error btn-sm">
                        Understand
                    </button>
                </div>
            </div>
        </div>
    </div>

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
        });
    </script>
</body>
</html>