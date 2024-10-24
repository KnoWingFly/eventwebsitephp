<?php
session_start();
require "../config.php";
require "../vendor/autoload.php";  // PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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
    SELECT users.name, users.email, registrations.registered_at 
    FROM registrations 
    JOIN users ON registrations.user_id = users.id 
    WHERE registrations.event_id = ?
");
$stmt_registrants->execute([$event_id]);
$registrants = $stmt_registrants->fetchAll(PDO::FETCH_ASSOC);

// Excel Export Logic
if (isset($_GET["export"]) && $_GET["export"] === "xlsx") {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Registrations');

    // Set column headers
    $sheet->setCellValue('A1', 'Name');
    $sheet->setCellValue('B1', 'Email');
    $sheet->setCellValue('C1', 'Event Title');
    $sheet->setCellValue('D1', 'Registered At');

    // Header Row Styling (bold, background color, center alignment)
    $headerStyle = [
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4CAF50'],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ];

    $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

    $stmt = $pdo->prepare("
        SELECT users.name, users.email, events.name as event_name, registrations.registered_at 
        FROM registrations 
        JOIN users ON registrations.user_id = users.id 
        JOIN events ON registrations.event_id = events.id 
        WHERE registrations.event_id = ?
    ");
    $stmt->execute([$event_id]);
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $row = 2;
    $alternate = false;
    foreach ($registrations as $registration) {
        $sheet->setCellValue("A$row", $registration['name']);
        $sheet->setCellValue("B$row", $registration['email']);
        $sheet->setCellValue("C$row", $registration['event_name']);
        $sheet->setCellValue("D$row", $registration['registered_at']);
        
        if ($alternate) {
            $sheet->getStyle("A$row:D$row")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F2F2F2'],
                ],
            ]);
        }

        $sheet->getStyle("A$row:D$row")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $row++;
        $alternate = !$alternate;
    }

    foreach (range('A', 'D') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="registrations.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
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
    <style>
        body {
            background-color: #111827; 
            color: #ffffff;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            border: 1px solid #444;
        }
        th {
            background-color: #333;
        }
        td {
            background-color: #222;
        }
        a {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .bg-green-500 {
            background-color: #4CAF50;
        }
        .hover\:bg-green-700:hover {
            background-color: #3E8E41;
        }
        .btn-back {
            background-color: #42a5f5; 
            color: white; 
        }
        .btn-back:hover {
            background-color: #1e88e5; 
        }
        .btn-view {
            background-color: #00bcd4;
        }
        .btn-edit {
            background-color: #ffeb3b;
        }
        .btn-delete {
            background-color: #f44336;
        }
        .button-group {
            display: flex;
            gap: 450px; /* Jarak antar tombol */
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-2xl font-bold mb-6">Registrants for Event: <?= htmlspecialchars($event["name"]) ?></h1>
        
        <div class="button-group">
            <a href="../admin/dashboard.php" 
               class="btn-back hover:bg-blue-700 py-2 px-4 rounded-lg">
               Back to Dashboard
            </a>

            <?php if (!empty($registrants)):?>
            <a href="registrants.php?event_id=<?= $event_id ?>&export=xlsx" class="bg-green-500 hover:bg-green-700 text-white py-2 px-4 rounded-lg">
                Export to Excel
            </a>
            
    <?php endif; ?>
        </div>

        <div class="bg-white shadow-lg rounded-lg p-6" style="background-color: #1e1e1e; color: white;">
            <table class="min-w-full bg-gray-800 border border-gray-700 rounded-lg">
                <thead>
                    <tr>
                        <th class="py-2 border-b text-left">Name</th>
                        <th class="py-2 border-b text-left">Email</th>
                        <th class="py-2 border-b text-left">Registered At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($registrants)): ?>
                        <tr>
                            <td colspan="3" class="py-2 border-b text-center">
                                Belum ada partisipan yang mendaftar saat ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($registrants as $registrant): ?>
                        <tr>
                            <td class="py-2 border-b"><?= htmlspecialchars($registrant["name"]) ?></td>
                            <td class="py-2 border-b"><?= htmlspecialchars($registrant["email"]) ?></td>
                            <td class="py-2 border-b"><?= htmlspecialchars($registrant["registered_at"]) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
