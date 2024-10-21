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

// Initialize an error array
$errors = [];

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
        $oldBannerPath = "../uploads/" . $event['banner'];  // Path to the old banner

        $allowed_types = ['image/png', 'image/jpeg', 'image/jpg'];
        $file_type = mime_content_type($_FILES['banner']['tmp_name']);

        if (in_array($file_type, $allowed_types)) {
            // Move the uploaded file to the target directory
            if (move_uploaded_file($_FILES['banner']['tmp_name'], $target)) {
                // If banner uploaded successfully, delete the old banner
                if (file_exists($oldBannerPath) && $event['banner'] != $banner) {
                    unlink($oldBannerPath); // Delete the old banner
                }
            } else {
                $errors[] = 'Failed to upload the banner image.';
            }
        } else {
            $errors[] = 'Invalid file type. Only PNG, JPG, and JPEG are allowed.';
            $banner = $event['banner']; // Keep the existing banner if new one is invalid
        }
    }

    // Check for errors before updating
    if (empty($errors)) {
        // Update event information in the database
        $stmt = $pdo->prepare("UPDATE events SET name = ?, event_date = ?, event_time = ?, location = ?, description = ?, max_participants = ?, status = ?, banner = ? WHERE id = ?");
        $stmt->execute([$name, $event_date, $event_time, $location, $description, $max_participants, $status, $banner, $event_id]);

        // If the event is canceled, delete all its registrations
        if ($status === 'canceled') {
            $stmt_delete_registrations = $pdo->prepare("DELETE FROM registrations WHERE event_id = ?");
            $stmt_delete_registrations->execute([$event_id]);
        }

        header('Location: dashboard.php');
        exit;
    }
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

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-error mb-4">
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

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
                            <input type="file" name="banner" accept=".png,.jpg,.jpeg"
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
    <div id="modal" class="fixed inset-0 flex items-center justify-center z-10 bg-opacity-75 bg-gray-900 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="bg-base-200 rounded-lg p-5 max-w-md w-full relative">
            <div class="flex items-center justify-between">
                <div class="text-center">
                    <h3 class="text-lg font-medium leading-6 text-white" id="modal-title">Event cannot be re-opened</h3>
                </div>
                <button id="closeModal" class="btn btn-sm btn-circle absolute right-2 top-2">✕</button>
            </div>
            <p class="mt-2 text-sm text-gray-300">The event has already passed. You cannot change its status to "Open".</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const statusSelect = document.querySelector('select[name="status"]');
            const eventDate = new Date("<?= $event['event_date'] ?>T<?= $event['event_time'] ?>");
            const now = new Date();
            const originalStatus = "<?= $event['status'] ?>";
            const modal = document.getElementById('modal');
            const closeModalBtn = document.getElementById('closeModal');

            // Check if event date has passed
            const isEventPassed = eventDate < now;

            // Add event listener to status dropdown
            statusSelect.addEventListener('change', function (e) {
                if (isEventPassed && this.value === 'open') {
                    e.preventDefault();
                    modal.classList.remove('hidden');  // Show the modal
                    this.value = originalStatus;  // Reset status to the original
                }
            });

            // Close modal when clicking outside the modal content area
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.classList.add('hidden');  // Hide the modal when clicked outside
                }
            });

            // Close modal when clicking the close button
            closeModalBtn.addEventListener('click', function() {
                modal.classList.add('hidden');
            });
        });
    </script>
</body>
</html>
