<?php
include('../config.php');

if (isset($_GET['event_id'])) {
    $eventId = intval($_GET['event_id']);

    // Fetch event details and participants
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = :id");
    $stmt->execute([':id' => $eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($event) {
        // Fetch participants for this event
        $stmt = $pdo->prepare("SELECT users.name, users.email FROM registrations JOIN users ON registrations.user_id = users.id WHERE registrations.event_id = :event_id");
        $stmt->execute([':event_id' => $eventId]);
        $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $event['participants'] = $participants;

        // Return JSON
        header('Content-Type: application/json');
        echo json_encode($event);
    } else {
        // Return an error if event not found
        echo json_encode(['error' => 'Event not found']);
    }
} else {
    echo json_encode(['error' => 'Invalid event ID']);
}