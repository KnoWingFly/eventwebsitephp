<?php
session_start();
require "../config.php";

// Ensure you have installed PhpSpreadsheet via Composer
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
        'font' => [
            'bold' => true,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4CAF50'],  // Green background
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

    // Apply header styling
    $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

    // SQL Query to fetch event and registration data
    $stmt = $pdo->prepare("
        SELECT users.name, users.email, events.name as event_name, registrations.registered_at 
        FROM registrations 
        JOIN users ON registrations.user_id = users.id 
        JOIN events ON registrations.event_id = events.id 
        WHERE registrations.event_id = ?
    ");
    $stmt->execute([$event_id]);
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Populate data with alternating row color
    $row = 2; // Start on the second row, since the first row is for column headers
    $alternate = false;
    foreach ($registrations as $registration) {
        $sheet->setCellValue("A$row", $registration['name']);
        $sheet->setCellValue("B$row", $registration['email']);
        $sheet->setCellValue("C$row", $registration['event_name']);
        $sheet->setCellValue("D$row", $registration['registered_at']);
        
        // Alternate row color for readability
        if ($alternate) {
            $sheet->getStyle("A$row:D$row")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F2F2F2'],  // Light grey background
                ],
            ]);
        }

        // Add borders to each cell
        $sheet->getStyle("A$row:D$row")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        $row++;
        $alternate = !$alternate; // Toggle row color
    }

    // Autosize columns for better fit
    foreach (range('A', 'D') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    // Send the generated Excel file to the browser for download
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
        /* Warna latar belakang umum */
        body {
            background-color: #1c1c1c; /* Monokrom abu-abu gelap */
            color: #e0e0e0; /* Teks abu-abu terang */
            font-family: 'Helvetica Neue', sans-serif;
        }

        /* Container styling */
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #e0e0e0;
            margin-bottom: 24px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        a {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .bg-green-500 {
            background-color: #4CAF50;
        }

        .hover\\:bg-green-700:hover {
            background-color: #3e8e41;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        th, td {
            padding: 12px 16px;
            text-align: left;
        }

        th {
            background-color: #333333;
            color: #e0e0e0;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 2px solid #444444;
        }

        td {
            background-color: #2b2b2b;
            color: #ffffff;
            border-bottom: 1px solid #444444;
        }

        /* Hover effect for table rows */
        tr:hover td {
            background-color: #3a3a3a;
        }

        /* Button styling */
        .btn {
            padding: 10px 20px;
            background-color: #4CAF50; /* Hijau monokrom */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #3e8e41;
        }

        /* Box styling */
        .box {
            background-color: #2b2b2b;
            padding: 24px;
            border-radius: 10px;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Monochrome hover effect */
        .btn-view:hover {
            background-color: #8bc34a; /* Lebih terang untuk efek hover */
        }

        .btn-edit:hover {
            background-color: #ffeb3b;
        }

        .btn-delete:hover {
            background-color: #f44336;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Registrants for Event: <?= htmlspecialchars($event["name"]) ?></h1>
        
        <div class="mb-4">
            <a href="registrants.php?event_id=<?= $event_id ?>&export=xlsx" class="btn bg-green-500 hover:bg-green-700">Export to Excel</a>
        </div>

        <div class="box">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Registered At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrants as $registrant): ?>
                    <tr>
                        <td><?= htmlspecialchars($registrant["name"]) ?></td>
                        <td><?= htmlspecialchars($registrant["email"]) ?></td>
                        <td><?= htmlspecialchars($registrant["registered_at"]) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
