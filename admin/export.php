<?php
// admin/export.php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1', 'Name');
$sheet->setCellValue('B1', 'Email');
$sheet->setCellValue('C1', 'Registered At');

$stmt = $pdo->prepare("SELECT users.name, users.email, registrations.registered_at FROM registrations 
                      JOIN users ON registrations.user_id = users.id WHERE event_id = ?");
$stmt->execute([$_GET['event_id']]);
$registrants = $stmt->fetchAll();

$row = 2;
foreach ($registrants as $registrant) {
    $sheet->setCellValue('A' . $row, $registrant['name']);
    $sheet->setCellValue('B' . $row, $registrant['email']);
    $sheet->setCellValue('C' . $row, $registrant['registered_at']);
    $row++;
}

$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="event_registrants.xlsx"');
$writer->save('php://output');
exit;