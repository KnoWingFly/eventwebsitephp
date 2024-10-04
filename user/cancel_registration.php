<?php
// cancel_registration.php
session_start();
require '../config.php';

$user_id = $_SESSION['user_id'];
$event_id = $_GET['event_id'];

// Check if the user is registered for the event
$stmt_check = $pdo->prepare("SELECT * FROM registrations WHERE user_id = ? AND event_id = ?");
$stmt_check->execute([$user_id, $event_id]);

if ($stmt_check->rowCount() > 0) {
    // Cancel the registration
    $stmt_delete = $pdo->prepare("DELETE FROM registrations WHERE user_id = ? AND event_id = ?");
    $stmt_delete->execute([$user_id, $event_id]);

    // Return success message
    echo json_encode(['status' => 'success', 'message' => 'Registration canceled.']);
} else {
    // If the user is not registered
    echo json_encode(['status' => 'error', 'message' => 'You are not registered for this event.']);
}
exit;
