<?php
session_start();
require "../config.php";

if ($_SESSION["role"] != "admin") {
    header("Location: ../index.php?page=login");
    exit();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $event_date = $_POST["event_date"];
    $event_time = $_POST["event_time"];
    $location = $_POST["location"];
    $description = $_POST["description"];
    $max_participants = $_POST["max_participants"];
    $status = $_POST["status"];

    if (!empty($_FILES["banner"]["name"])) {
        $banner = $_FILES["banner"]["name"];
        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($banner);

        if (!move_uploaded_file($_FILES["banner"]["tmp_name"], $target_file)) {
            $error = "Error uploading the banner image.";
        }
    } else {
        $banner = null;
    }

    if (!$error) {
        try {
            $stmt = $pdo->prepare("INSERT INTO events (name, event_date, event_time, location, description, max_participants, banner, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $name,
                $event_date,
                $event_time,
                $location,
                $description,
                $max_participants,
                $banner,
                $status,
            ]);

            header("Location: dashboard.php");
            exit();
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8" >
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Event</title>
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
    <div class="min-h-screen flex items-center justify-center p-4 bg-base-100">
        <div class="w-full max-w-lg mx-auto">
            <div class="card bg-base-200 shadow-xl">
                <div class="card-body p-6">
                    <div class="border-b border-base-300 pb-3 mb-4">
                        <h1 class="card-title text-xl text-white">Create New Event</h1>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-error text-sm">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <!-- Event Name -->
                        <div class="form-control">
                            <label class="label py-1">
                                <span class="label-text text-white">Event Name</span>
                            </label>
                            <input type="text" name="name" required 
                                class="input input-bordered input-sm bg-base-300">
                        </div>

                        <!-- Date and Time Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label py-1">
                                    <span class="label-text text-white">Event Date</span>
                                </label>
                                <input type="date" name="event_date" required 
                                    class="input input-bordered input-sm bg-base-300">
                            </div>
                            <div class="form-control">
                                <label class="label py-1">
                                    <span class="label-text text-white">Event Time</span>
                                </label>
                                <input type="time" name="event_time" required 
                                    class="input input-bordered input-sm bg-base-300">
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="form-control">
                            <label class="label py-1">
                                <span class="label-text text-white">Location</span>
                            </label>
                            <input type="text" name="location" required 
                                class="input input-bordered input-sm bg-base-300">
                        </div>

                        <!-- Description -->
                        <div class="form-control">
                            <label class="label py-1">
                                <span class="label-text text-white">Description</span>
                            </label>
                            <textarea name="description" required rows="3" 
                                class="textarea textarea-bordered bg-base-300 h-20 text-sm"></textarea>
                        </div>

                        <!-- Max Participants -->
                        <div class="form-control">
                            <label class="label py-1">
                                <span class="label-text text-white">Max Participants</span>
                            </label>
                            <input type="number" name="max_participants" required 
                                class="input input-bordered input-sm bg-base-300">
                        </div>

                        <!-- Event Status -->
                        <div class="form-control">
                            <label class="label py-1">
                                <span class="label-text text-white">Event Status</span>
                            </label>
                            <select name="status" required 
                                class="select select-bordered select-sm bg-base-300 ">
                                <option value="open">Open</option>
                                <option value="closed">Closed</option>
                                <option value="canceled">Canceled</option>
                            </select>
                        </div>

                        <!-- Banner Upload -->
                        <div class="form-control">
                            <label class="label py-1">
                                <span class="label-text text-white">Upload Banner (Optional)</span>
                            </label>
                            <input type="file" name="banner" accept="image/*"
                                class="file-input file-input-bordered file-input-sm bg-base-300 w-full">
                        </div>

                        <!-- Submit Button -->
                        <div class="form-control mt-4">
                            <button type="submit" class="btn btn-primary btn-sm">
                                Create Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>