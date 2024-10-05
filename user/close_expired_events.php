<?php
require '../config.php';

// Ambil event yang masih berstatus 'open' dan waktunya lebih dari 1 menit yang lalu
$stmt = $pdo->prepare("UPDATE events SET status = 'closed' WHERE status = 'open' AND TIMESTAMPDIFF(MINUTE, event_date, NOW()) >= 1");
$stmt->execute();

// Log atau respons sederhana untuk memastikan skrip berjalan
echo "Expired events have been closed.";