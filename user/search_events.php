<?php
// search_events.php
session_start();
require '../config.php';

$search = isset($_GET['query']) ? $_GET['query'] : '';

if ($search) {
    // Search for events based on the query
    $stmt = $pdo->prepare("SELECT * FROM events WHERE name LIKE :search OR event_date LIKE :search");
    $stmt->execute(['search' => "%$search%"]);
} else {
    // If no query, return all events
    $stmt = $pdo->query("SELECT * FROM events");
}
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($events);
