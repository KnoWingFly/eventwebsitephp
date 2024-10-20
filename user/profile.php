<?php
session_start();
require "../config.php";

if (!isset($_SESSION["user_id"])) {
	header("Location: ../index.php?page=login");
	exit();
}

$user_id = $_SESSION["user_id"];

$stmt_user = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

$stmt_history = $pdo->prepare("
    SELECT events.name, events.event_date, events.location 
    FROM registrations 
    JOIN events ON registrations.event_id = events.id 
    WHERE registrations.user_id = ?
");
$stmt_history->execute([$user_id]);
$registered_events = $stmt_history->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@3.7.3/dist/full.css" rel="stylesheet" type="text/css" />
    <link href="../css/output.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-base-300 p-2 sm:p-4">
    <div class="max-w-full sm:max-w-md md:max-w-lg lg:max-w-xl mx-auto space-y-4">
        <h1 class="text-2xl sm:text-3xl font-bold text-base-content text-white break-words">User Profile</h1>
        
        <div class="bg-base-200 shadow-xl rounded-lg p-4">
            <h2 class="text-lg sm:text-xl font-semibold mb-3 text-base-content text-white break-words">Profile Information</h2>
            <div class="space-y-2 text-base-content text-sm sm:text-base">
                <p class="break-all"><span class="font-semibold">Name:</span> <?php echo htmlspecialchars($user['name']); ?></p>
                <p class="break-all"><span class="font-semibold">Email:</span> <?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <div class="mt-4">
                <a href="edit_profile.php" class="btn btn-primary btn-sm sm:btn-md">EDIT PROFILE</a>
            </div>
        </div>

        <div class="bg-base-200 shadow-xl rounded-lg p-4">
            <h2 class="text-lg sm:text-xl font-semibold mb-3 text-base-content text-white break-words">Event Registration History</h2>
            <div class="overflow-x-auto">
                <table class="table w-full text-xs sm:text-sm">
                    <thead>
                        <tr>
                            <th class="text-left bg-base-300 px-2 py-2">Event Name</th>
                            <th class="text-left bg-base-300 px-2 py-2">Date</th>
                            <th class="text-left bg-base-300 px-2 py-2">Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registered_events as $event): ?>
                        <tr>
                            <td class="break-all px-2 py-2"><?php echo htmlspecialchars($event['name']); ?></td>
                            <td class="break-all px-2 py-2"><?php echo htmlspecialchars($event['event_date']); ?></td>
                            <td class="break-all px-2 py-2"><?php echo htmlspecialchars($event['location']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>