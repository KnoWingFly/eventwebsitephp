<?php
// event_details.php
session_start();
require "../config.php";

$event_id = $_GET["event_id"];

// Fetch event details including banner
$stmt = $pdo->prepare(
	"SELECT name, event_date, event_time, location, description, max_participants, status, banner FROM events WHERE id = ?",
);
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if ($event) {
	echo json_encode($event);
} else {
	echo json_encode(["error" => "Event not found"]);
}
