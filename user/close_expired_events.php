<?php
require "../config.php";

$stmt = $pdo->prepare(
	"UPDATE events SET status = 'closed' WHERE status = 'open' AND TIMESTAMPDIFF(MINUTE, event_date, NOW()) >= 1",
);
$stmt->execute();

echo "Expired events have been closed.";
