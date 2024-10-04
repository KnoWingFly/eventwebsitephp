<?php
// register_event.php
session_start();
require '../config.php';

$user_id = $_SESSION['user_id'];
$event_id = $_GET['event_id'];

// Fetch event details
$stmt_event = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt_event->execute([$event_id]);
$event = $stmt_event->fetch();

if (!$event) {
    echo json_encode(['status' => 'error', 'message' => 'Event not found.']);
    exit;
}

// Check if the event is open
if ($event['status'] !== 'open') {
    echo json_encode(['status' => 'error', 'message' => 'You cannot register for this event. The event is either closed or canceled.']);
    exit;
}

// Check if the event has reached the maximum number of participants
$stmt_count = $pdo->prepare("SELECT COUNT(*) AS total FROM registrations WHERE event_id = ?");
$stmt_count->execute([$event_id]);
$registration_count = $stmt_count->fetchColumn();

if ($registration_count >= $event['max_participants']) {
    // Update the event status to 'closed' since the max participants have been reached
    $stmt_update_status = $pdo->prepare("UPDATE events SET status = 'closed' WHERE id = ?");
    $stmt_update_status->execute([$event_id]);

    echo json_encode(['status' => 'error', 'message' => 'The event is full. Registration is closed.']);
    exit;
}

// Check if the user is already registered
$stmt_check = $pdo->prepare("SELECT * FROM registrations WHERE user_id = ? AND event_id = ?");
$stmt_check->execute([$user_id, $event_id]);

if ($stmt_check->rowCount() > 0) {
    echo json_encode(['status' => 'error', 'message' => 'You are already registered for this event.']);
    exit;
}

// Register the user for the event
$stmt_register = $pdo->prepare("INSERT INTO registrations (user_id, event_id) VALUES (?, ?)");
$stmt_register->execute([$user_id, $event_id]);

// Return success message
echo json_encode(['status' => 'success', 'message' => 'Registration complete.']);
exit;