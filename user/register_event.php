<?php
session_start();
require "../config.php";

$user_id = $_SESSION["user_id"];
$event_id = $_GET["event_id"];

$stmt_event = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt_event->execute([$event_id]);
$event = $stmt_event->fetch();

if (!$event) {
	echo json_encode(["status" => "error", "message" => "Event not found."]);
	exit();
}

if ($event["status"] !== "open") {
	echo json_encode([
		"status" => "error",
		"message" =>
			"You cannot register for this event. The event is either closed or canceled.",
	]);
	exit();
}

$stmt_count = $pdo->prepare(
	"SELECT COUNT(*) AS total FROM registrations WHERE event_id = ?",
);
$stmt_count->execute([$event_id]);
$registration_count = $stmt_count->fetchColumn();

if ($registration_count >= $event["max_participants"]) {
	$stmt_update_status = $pdo->prepare(
		"UPDATE events SET status = 'closed' WHERE id = ?",
	);
	$stmt_update_status->execute([$event_id]);

	echo json_encode([
		"status" => "error",
		"message" => "The event is full. Registration is closed.",
	]);
	exit();
}

$stmt_check = $pdo->prepare(
	"SELECT * FROM registrations WHERE user_id = ? AND event_id = ?",
);
$stmt_check->execute([$user_id, $event_id]);

if ($stmt_check->rowCount() > 0) {
	echo json_encode([
		"status" => "error",
		"message" => "You are already registered for this event.",
	]);
	exit();
}

$stmt_register = $pdo->prepare(
	"INSERT INTO registrations (user_id, event_id) VALUES (?, ?)",
);
$stmt_register->execute([$user_id, $event_id]);

echo json_encode([
	"status" => "success",
	"message" => "Registration complete.",
]);
exit();
