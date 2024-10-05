<?php
session_start();
require '../config.php';

$user_id = $_SESSION['user_id'];
$event_id = $_GET['event_id'];

$stmt_check = $pdo->prepare("SELECT * FROM registrations WHERE user_id = ? AND event_id = ?");
$stmt_check->execute([$user_id, $event_id]);

if ($stmt_check->rowCount() > 0) {
    $stmt_delete = $pdo->prepare("DELETE FROM registrations WHERE user_id = ? AND event_id = ?");
    $stmt_delete->execute([$user_id, $event_id]);

    echo json_encode(['status' => 'success', 'message' => 'Registration canceled.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'You are not registered for this event.']);
}
exit;
