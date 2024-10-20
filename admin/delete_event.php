<?php
session_start();
require "../config.php";

// Check if user is admin
if ($_SESSION["role"] != "admin") {
    header("Location: ../index.php?page=login");
    exit();
}

$event_id = $_GET["id"];

// Fetch event details from the database
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $entered_title = trim($_POST["verification_title"]);
    if ($entered_title === $event["name"]) {
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$event_id]);
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "The entered title does not match the event's title.";
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Event</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@3.9.4/dist/full.css" rel="stylesheet" type="text/css" />
    <link href="../css/output.css" rel="stylesheet">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .modal-card {
            animation: fadeIn 0.3s ease-out;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-900 flex items-center justify-center p-4">
    <div class="modal-card card w-full max-w-md bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="flex flex-col gap-4">
                <!-- Header -->
                <div class="flex items-center gap-2 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <h2 class="card-title text-2xl text-white">Delete Event</h2>
                </div>

                <!-- Content -->
                <div class="space-y-4">
                    <div class="prose">
                        <p class="text-base-content/70 text-white">Are you sure you want to delete the event:</p>
                        <p class="text-xl font-semibold text-base-content"><label class="text-white">"<?= htmlspecialchars($event["name"]) ?>"</label></p>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            <span><?= htmlspecialchars($error) ?></span>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" class="space-y-6">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text text-white">Type the event title to confirm:</span>
                            </label>
                            <input 
                                type="text" 
                                id="verification_title" 
                                name="verification_title" 
                                class="input input-bordered w-full" 
                                placeholder="Enter event title"
                                required
                            />
                        </div>

                        <div class="card-actions justify-between">
                            <a href="dashboard.php" class="text-white btn-ghost btn btn-block sm:btn-wide">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-error text-black btn-block sm:btn-wide">
                                Delete Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
