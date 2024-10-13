<?php
session_start();
require "../config.php";

if ($_SESSION["role"] != "admin") {
    header("Location: ../index.php?page=login");
    exit();
}

$event_id = isset($_GET["event_id"]) ? $_GET["event_id"] : null;

if (!$event_id) {
    echo "Event ID is required!";
    exit();
}

$stmt_event = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt_event->execute([$event_id]);
$event = $stmt_event->fetch();

if (!$event) {
    echo "Event not found!";
    exit();
}

$stmt_registrants = $pdo->prepare("
    SELECT users.name, users.email 
    FROM registrations 
    JOIN users ON registrations.user_id = users.id 
    WHERE registrations.event_id = ?
");
$stmt_registrants->execute([$event_id]);
$registrants = $stmt_registrants->fetchAll(PDO::FETCH_ASSOC);

// CSV Export Logic
if (isset($_GET["export"]) && $_GET["export"] === "csv") {
    $stmt = $pdo->prepare("
        SELECT users.name, users.email, events.name as event_name
        FROM registrations 
        JOIN users ON registrations.user_id = users.id 
        JOIN events ON registrations.event_id = events.id 
        WHERE registrations.event_id = ?
    ");
    $stmt->execute([$event_id]);
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ensure the output buffer is completely cleared
    if (ob_get_length()) {
        ob_end_clean();
    }

    // Setting headers to download the file as a CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=registrations.csv');

    // Add UTF-8 BOM for proper Excel recognition
    echo "\xEF\xBB\xBF";

    // Open output stream for writing CSV
    $output = fopen('php://output', 'w');

    // Using a semicolon (;) as delimiter for CSV to match locale expectations
    fputcsv($output, ['Name', 'Email', 'Event Title'], ';');

    // Writing each row of data to CSV with semicolon delimiter
    foreach ($registrations as $registration) {
        fputcsv($output, [
            $registration['name'],  // Column A: Name
            $registration['email'], // Column B: Email
            $registration['event_name'] // Column C: Event Name
        ], ';');
    }

    // Close the output stream
    fclose($output);

    // Exit to prevent further output
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrants for <?= htmlspecialchars($event["name"]) ?></title>
    <link href="../css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-10">
        <h1 class="text-2xl font-bold mb-6">Registrants for Event: <?= htmlspecialchars($event["name"]) ?></h1>
        
        <div class="mb-4">
            <a href="registrants.php?event_id=<?= $event_id ?>&export=csv" class="bg-green-500 hover:bg-green-700 text-white py-2 px-4 rounded-lg">Export to CSV</a>
        </div>

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
                        <td class="py-2 border-b"><?= htmlspecialchars($registrant["name"]) ?></td>
                        <td class="py-2 border-b"><?= htmlspecialchars($registrant["email"]) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
