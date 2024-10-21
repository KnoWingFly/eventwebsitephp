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
</head>
<body class="min-h-screen bg-base-300 p-2 sm:p-4 md:p-6 lg:p-8">
    <div class="container mx-auto max-w-7xl">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-base-content text-white break-words px-2 sm:px-4 mb-4 sm:mb-6 lg:mb-8 text-center">User Profile</h1>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <div class="bg-base-200 shadow-xl rounded-lg p-4 sm:p-6">
                <h2 class="text-lg sm:text-xl lg:text-2xl font-semibold mb-3 sm:mb-4 text-base-content text-white break-words">Profile Information</h2>
                <div class="space-y-2 sm:space-y-3 text-base-content text-sm sm:text-base">
                    <p class="break-all"><span class="font-semibold">Name:</span> <?php echo htmlspecialchars($user['name']); ?></p>
                    <p class="break-all"><span class="font-semibold">Email:</span> <?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <div class="mt-4 sm:mt-6">
                    <a href="edit_profile.php" class="btn btn-primary btn-sm sm:btn-md">EDIT PROFILE</a>
                    <a href="dashboard.php" class="btn btn-secondary btn-sm sm:btn-md">DASHBOARD</a>
                </div>
            </div>

            <div class="bg-base-200 shadow-xl rounded-lg p-4 sm:p-6">
                <h2 class="text-lg sm:text-xl lg:text-2xl font-semibold mb-3 sm:mb-4 text-base-content text-white break-words">Event Registration History</h2>
                <div class="overflow-x-auto">
                    <table class="table w-full text-xs sm:text-sm">
                        <thead>
                            <tr>
                                <th class="text-left bg-base-300 px-2 sm:px-4 py-2 sm:py-3 text-white">Event Name</th>
                                <th class="text-left bg-base-300 px-2 sm:px-4 py-2 sm:py-3 text-white">Date</th>
                                <th class="text-left bg-base-300 px-2 sm:px-4 py-2 sm:py-3 text-white">Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registered_events as $event): ?>
                            <tr>
                                <td class="break-all px-2 sm:px-4 py-2 sm:py-3"><?php echo htmlspecialchars($event['name']); ?></td>
                                <td class="break-all px-2 sm:px-4 py-2 sm:py-3"><?php echo htmlspecialchars($event['event_date']); ?></td>
                                <td class="break-all px-2 sm:px-4 py-2 sm:py-3"><?php echo htmlspecialchars($event['location']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
