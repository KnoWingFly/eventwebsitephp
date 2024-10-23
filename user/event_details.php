<?php
// event_details.php
session_start();
require "../config.php";

$event_id = $_GET["event_id"];

$stmt = $pdo->prepare(
    "SELECT name, event_date, event_time, location, description, max_participants, status, banner FROM events WHERE id = ?"
);
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if ($event) {
    // Check if banner exists in database and file exists in uploads folder
    if (!empty($event['banner'])) {
        $banner_path = "../uploads/" . $event['banner'];
        // Check if file actually exists
        if (!file_exists($banner_path)) {
            $event['banner'] = null;
        }
    } else {
        $event['banner'] = null;
    }
    
    echo json_encode($event);
} else {
    echo json_encode(["error" => "Event not found"]);
}