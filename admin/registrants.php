<?php
session_start();
require '../config.php';

// Check if admin is logged in
if ($_SESSION['role'] != 'admin') {
    header('Location: ../index.php?page=login');
    exit;
}

// Get the event ID from the URL
$event_id = isset($_GET['event_id']) ? $_GET['event_id'] : null;

if (!$event_id) {
    echo "Event ID is required!";
    exit;
}

// Fetch event details
$stmt_event = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt_event->execute([$event_id]);
$event = $stmt_event->fetch();

if (!$event) {
    echo "Event not found!";
    exit;
}

// Fetch the list of registrants
$stmt_registrants = $pdo->prepare("
    SELECT users.name, users.email 
    FROM registrations 
    JOIN users ON registrations.user_id = users.id 
    WHERE registrations.event_id = ?
");
$stmt_registrants->execute([$event_id]);
$registrants = $stmt_registrants->fetchAll(PDO::FETCH_ASSOC);

// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=registrants.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Name', 'Email']); // CSV header
    foreach ($registrants as $registrant) {
        fputcsv($output, $registrant);
    }
    fclose($output);
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrants for <?= htmlspecialchars($event['name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-10">
        <h1 class="text-2xl font-bold mb-6">Registrants for Event: <?= htmlspecialchars($event['name']) ?></h1>
        
        <!-- Export Button -->
        <div class="mb-4">
            <a href="registrants.php?event_id=<?= $event_id ?>&export=csv" class="bg-green-500 hover:bg-green-700 text-white py-2 px-4 rounded-lg">Export to CSV</a>
        </div>

        <!-- Registrants Table -->
        <div class="bg-white shadow-lg rounded-lg p-6">
            <table class="min-w-full bg-white border border-gray-300 rounded-lg">
                <thead>
                    <tr>
                        <th class="py-2 border-b text-left">Name</th>
                        <th class="py-2 border-b text-left">Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrants as $registrant): ?>
                    <tr>
                        <td class="py-2 border-b"><?= htmlspecialchars($registrant['name']) ?></td>
                        <td class="py-2 border-b"><?= htmlspecialchars($registrant['email']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
